<?php

namespace App\Services;

use App\DTO\PurchaseItem\CreatePurchaseItemDTO;
use App\DTO\PurchaseItem\UpdatePurchaseItemDTO;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseItemService
{
    public function __construct(
        private PurchaseItemRepositoryInterface $repo
    ) {}

    public function create(CreatePurchaseItemDTO $dto)
    {
        $result = DB::transaction(function () use ($dto) {
            $this->lockEditablePurchase($dto->purchase_id);

            return $this->repo->create([
                'purchase_id' => $dto->purchase_id,
                'product_id'  => $dto->product_id,
                'quantity'    => $dto->quantity,
                'cost_price'  => $dto->cost_price,
                'discount'    => $dto->discount,
                'tax'         => $dto->tax,
                'subtotal'    => $dto->subtotal,
            ]);
        });

        $this->clearPurchaseItemCache();
        return $result;
    }

    public function update(string $id, UpdatePurchaseItemDTO $dto)
    {
        $result = DB::transaction(function () use ($id, $dto) {
            $existingItem = PurchaseItem::query()->findOrFail($id);
            $purchaseIds = array_unique([(int) $existingItem->purchase_id, $dto->purchase_id]);
            sort($purchaseIds);
            foreach ($purchaseIds as $purchaseId) {
                $this->lockEditablePurchase($purchaseId);
            }

            $item = PurchaseItem::query()->lockForUpdate()->findOrFail($id);
            if ((int) $item->purchase_id !== (int) $existingItem->purchase_id) {
                throw ValidationException::withMessages([
                    'purchase_item' => 'The purchase item changed while it was being updated. Reload and retry.',
                ]);
            }

            if ($item->received_quantity > 0) {
                throw ValidationException::withMessages([
                    'purchase_item' => 'A purchase item with received stock cannot be edited.',
                ]);
            }

            return $this->repo->update($id, [
                'purchase_id' => $dto->purchase_id,
                'product_id'  => $dto->product_id,
                'quantity'    => $dto->quantity,
                'cost_price'  => $dto->cost_price,
                'discount'    => $dto->discount,
                'tax'         => $dto->tax,
                'subtotal'    => $dto->subtotal,
            ]);
        });

        Cache::forget("purchase_item_by_id_{$id}");
        $this->clearPurchaseItemCache();

        return $result;
    }

    public function all()
    {
        $version = Cache::get('purchase_items_cache_version', 1);

        return Cache::remember(
            'purchase_items:v' . $version,
            300,
            fn () => $this->repo->all()
        );
    }

    public function findById(string $id)
    {
        return Cache::remember(
            "purchase_item_by_id_{$id}",
            300,
            fn () => $this->repo->findById($id)
        );
    }

    public function delete(string $id)
    {
        $result = DB::transaction(function () use ($id) {
            $itemBeforeLock = PurchaseItem::query()->findOrFail($id);
            $this->lockEditablePurchase((int) $itemBeforeLock->purchase_id);

            $item = PurchaseItem::query()->lockForUpdate()->findOrFail($id);
            if ((int) $item->purchase_id !== (int) $itemBeforeLock->purchase_id || $item->received_quantity > 0) {
                throw ValidationException::withMessages([
                    'purchase_item' => 'A purchase item with received stock cannot be deleted.',
                ]);
            }

            return $this->repo->delete($id);
        });
        Cache::forget("purchase_item_by_id_{$id}");
        $this->clearPurchaseItemCache();

        return $result;
    }

    private function lockEditablePurchase(int $purchaseId): Purchase
    {
        $purchase = Purchase::query()->lockForUpdate()->findOrFail($purchaseId);
        if (
            $purchase->goodsReceipts()->exists()
            || $purchase->items()->where('received_quantity', '>', 0)->exists()
            || $purchase->payments()->exists()
        ) {
            throw ValidationException::withMessages([
                'purchase' => 'Purchase items cannot be changed after a receipt or payment has been recorded.',
            ]);
        }

        return $purchase;
    }

    private function clearPurchaseItemCache(): void
    {
        if (Cache::has('purchase_items_cache_version')) {
            Cache::increment('purchase_items_cache_version');
        } else {
            Cache::put('purchase_items_cache_version', 2);
        }
    }
}
