<?php

namespace App\Services;

use App\DTO\GoodsReceipt\ReceiveGoodsDTO;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Repositories\Interfaces\GoodsReceiptRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(
        private GoodsReceiptRepositoryInterface $repo,
        private SupplierService $supplierService,
    ) {}

    public function all(): Collection
    {
        return $this->repo->all();
    }

    public function findById(string $id)
    {
        return $this->repo->findById($id);
    }

    public function receive(ReceiveGoodsDTO $dto)
    {
        $receipt = DB::transaction(function () use ($dto) {
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($dto->purchase_id);

            if ($purchase->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'purchase_id' => 'Goods cannot be received against a cancelled purchase order.',
                ]);
            }

            if ($purchase->status === 'received') {
                throw ValidationException::withMessages([
                    'purchase_id' => 'This purchase order has already been fully received.',
                ]);
            }

            if (!in_array($purchase->status, ['approved', 'partial'], true)) {
                throw ValidationException::withMessages([
                    'purchase_id' => 'Only approved or partially received purchase orders can be received.',
                ]);
            }

            $receiptItems = [];
            $movements = [];
            foreach ($dto->items as $item) {
                $purchaseItem = $purchase->items()
                    ->whereKey($item['purchase_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$purchaseItem) {
                    throw ValidationException::withMessages([
                        'items' => "Purchase item {$item['purchase_item_id']} does not belong to this purchase order.",
                    ]);
                }

                $quantity = (int) $item['received_quantity'];
                $remaining = (int) $purchaseItem->quantity - (int) $purchaseItem->received_quantity;
                if ($quantity < 1 || $quantity > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Received quantity must be between 1 and {$remaining} for purchase item {$purchaseItem->id}.",
                    ]);
                }

                $product = Product::query()->lockForUpdate()->findOrFail($purchaseItem->product_id);
                $stockBefore = (int) $product->stock_qty;
                $product->stock_qty = $stockBefore + $quantity;
                $product->save();
                $purchaseItem->increment('received_quantity', $quantity);

                $receiptItems[] = [
                    'purchase_item_id' => $purchaseItem->id,
                    'quantity' => $quantity,
                ];
                $movements[] = [
                    'product_id' => $product->id,
                    'user_id' => $dto->user_id,
                    'type' => 'grn',
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_qty,
                    'reference_type' => 'goods_receipt',
                ];
            }

            $receipt = $this->repo->create([
                'purchase_id' => $purchase->id,
                'user_id' => $dto->user_id,
                'received_at' => now(),
            ], $receiptItems);

            foreach ($movements as $movement) {
                StockMovement::create([
                    ...$movement,
                    'reference_id' => $receipt->id,
                ]);
            }

            $purchase->load('items');
            $allReceived = $purchase->items->every(
                fn ($purchaseItem) => (int) $purchaseItem->received_quantity >= (int) $purchaseItem->quantity
            );
            $purchase->update(['status' => $allReceived ? 'received' : 'partial']);

            return $this->repo->findById((string) $receipt->id);
        });

        Cache::forget("purchase_by_id_{$dto->purchase_id}");
        Cache::forget('purchase_state');
        if (Cache::has('purchases_cache_version')) {
            Cache::increment('purchases_cache_version');
        } else {
            Cache::put('purchases_cache_version', 2);
        }
        Cache::forget('product_state');
        if (Cache::has('products_cache_version')) {
            Cache::increment('products_cache_version');
        } else {
            Cache::put('products_cache_version', 2);
        }
        $this->supplierService->clearStatsCache();

        return $receipt;
    }
}
