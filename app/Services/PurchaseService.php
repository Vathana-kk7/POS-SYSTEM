<?php

namespace App\Services;

use App\DTO\Purchase\CreatePurchaseDTO;
use App\DTO\Purchase\UpdatePurchaseDTO;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;
use App\DTO\Purchase\ReceivePurchaseDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use PhpParser\Node\Expr\FuncCall;

class PurchaseService
{
    public function __construct(
        private PurchaseRepositoryInterface $repo,
        private PurchaseItemRepositoryInterface $purchaseItemRepo,
        private SupplierService $supplierService
    ) {}

    /**
     * Create Purchase
     */
    public Function getstate(){
        return Cache::remember('purchase_state',300 ,function(){
            return $this->repo->getstate();
        });
    }
    public function create(CreatePurchaseDTO $dto, array $items)
    {
        $purchase = DB::transaction(function () use ($dto, $items) {
            $purchaseOrderNo = $this->generatePurchaseOrderNo();
            $subtotal = 0;
            $itemDiscount = 0;
            $itemTax = 0;
            $purchaseItems = [];

            foreach ($items as $item) {
                $quantity = (int) $item['quantity'];
                $costPrice = (float) $item['cost_price'];
                $discount = round((float) ($item['discount'] ?? 0), 2);
                $tax = round((float) ($item['tax'] ?? 0), 2);
                $lineSubtotal = round(($quantity * $costPrice) - $discount + $tax,2);

                $subtotal = round($subtotal + round($quantity * $costPrice, 2),2);

                $itemDiscount = round($itemDiscount + $discount, 2);
                $itemTax = round($itemTax + $tax, 2);
                $purchaseItems[] = [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => $quantity,
                    'cost_price' => $costPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = round($dto->discount + $itemDiscount, 2);
            $tax = round($dto->tax + $itemTax, 2);
            $total = round($subtotal - $discount + $tax + $dto->shipping_cost,2);
            $purchase = $this->repo->create([
                'purchase_order_no' => $purchaseOrderNo,

                'purchase_date' => $dto->purchase_date,
                'expected_delivery_date' => $dto->expected_delivery_date,

                'supplier_id' => $dto->supplier_id,
                'user_id' => $dto->user_id,

                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'tax' => round($tax, 2),
                'shipping_cost' => $dto->shipping_cost,
                'total' => $total,
                'payment_terms' => $dto->payment_terms,
                'status' => $dto->status,
                'notes' => $dto->notes,
            ]);

            foreach ($purchaseItems as $item) {
                $this->purchaseItemRepo->create([
                    'purchase_id' => $purchase->id,
                    ...$item,
                ]);
            }
            return $purchase->fresh([
                'supplier',
                'user',
                'items.product',
            ]);
        });

        $this->supplierService->clearStatsCache();
        $this->clearPurchaseCache();   // ← បន្ថែម
        return $purchase;
    }
    /**
     * Update Purchase
     */
    public function update(string $id, UpdatePurchaseDTO $dto, array $items )
    {
        $purchase = DB::transaction(function () use ($id, $dto, $items) {

            $subtotal = 0;
            $itemDiscount = 0;
            $itemTax = 0;
            $purchaseItems = [];

            foreach ($items as $item) {
                $quantity  = (int) $item['quantity'];
                $costPrice = (float) $item['cost_price'];
                $discount  = round((float) ($item['discount'] ?? 0), 2);
                $tax       = round((float) ($item['tax'] ?? 0), 2);

                $lineSubtotal = round(($quantity * $costPrice) - $discount + $tax, 2);

                $subtotal     = round($subtotal + round($quantity * $costPrice, 2), 2);
                $itemDiscount = round($itemDiscount + $discount, 2);
                $itemTax      = round($itemTax + $tax, 2);

                $purchaseItems[] = [
                    'product_id' => (int) $item['product_id'],
                    'quantity'   => $quantity,
                    'cost_price' => $costPrice,
                    'discount'   => $discount,
                    'tax'        => $tax,
                    'subtotal'   => $lineSubtotal,
                ];
            }

            // ដូច create(): totals = ពី items (+ ផ្នែក header បើមាន)
            $discount = round($dto->discount + $itemDiscount, 2);
            $tax      = round($dto->tax + $itemTax, 2);
            $total    = round($subtotal - $discount + $tax + $dto->shipping_cost, 2);

            // 1. Update header
            $this->repo->update($id, [
                'purchase_date'          => $dto->purchase_date,
                'expected_delivery_date' => $dto->expected_delivery_date,
                'supplier_id'            => $dto->supplier_id,
                'user_id'                => $dto->user_id,
                'subtotal'               => $subtotal,
                'discount'               => $discount,
                'tax'                    => $tax,
                'shipping_cost'          => $dto->shipping_cost,
                'total'                  => $total,
                'payment_terms'          => $dto->payment_terms,
                'status'                 => $dto->status,
                'notes'                  => $dto->notes,
            ]);

            // 2. លុប items ចាស់ រួចបង្កើតថ្មី
            $purchase = $this->repo->findById($id);
            $purchase->items()->delete();

            foreach ($purchaseItems as $row) {
                $this->purchaseItemRepo->create([
                    'purchase_id' => $purchase->id,
                    ...$row,
                ]);
            }

            return $purchase->fresh([
                'supplier',
                'user',
                'items.product',
            ]);
        });

        $this->supplierService->clearStatsCache();
        Cache::forget("purchase_by_id_{$id}");   // ← បន្ថែម
        $this->clearPurchaseCache();             // ← បន្ថែម
        return $purchase;
    }

    public function all()
    {
        $version = Cache::get('purchases_cache_version', 1);

        return Cache::remember(
            'purchases:v' . $version,
            300, // 5 នាទី
            fn () => $this->repo->all()
        );
    }

    public function findById(string $id)
    {
        return Cache::remember(
            "purchase_by_id_{$id}",
            300,
            fn () => $this->repo->findById($id)
        );
    }
    public function delete(string $id)
    {
        $result = DB::transaction(function () use ($id) {
            $purchase = $this->repo->findById($id);

            // មិនអនុញ្ញាតលុប បើបាន receive stock រួច (stock ក្នុង Product នឹងមិនត្រូវគ្នា)
            if ($purchase->items->sum('received_quantity') > 0) {
                throw ValidationException::withMessages([
                    'purchase' => 'Cannot delete a purchase that has already received stock.',
                ]);
            }

            // លុប items មុន ទើបលុប purchase
            $purchase->items()->delete();

            return $this->repo->delete($id);
        });

        $this->supplierService->clearStatsCache();
        Cache::forget("purchase_by_id_{$id}");   // ← បន្ថែម
        $this->clearPurchaseCache();             // ← បន្ថែម
        return $result;
    }
    private function generatePurchaseOrderNo(): string
    {
        $date = now()->format('Ymd');
        $lastPurchase = $this->repo->getLastPurchaseOrderNo($date);
        if (!$lastPurchase) {
            $number = 1;
        } else {
            $lastNumber = (int) substr(
                $lastPurchase->purchase_order_no,
                -4
            );
            $number = $lastNumber + 1;
        }
        return 'PO-' . $date . '-' . str_pad(
            $number,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
    /**
 * Receive Purchase Stock
 */
    public function receiveStock(ReceivePurchaseDTO $dto)
{
    $purchase = DB::transaction(function () use ($dto) {
        $purchase = $this->repo->findById($dto->purchase_id);

        // អនុញ្ញាត receive តែ approved / partial
        if (!in_array($purchase->status, ['approved', 'partial'])) {
            throw ValidationException::withMessages([
                'purchase' => 'Only approved or partially received purchases can receive stock.',
            ]);
        }

        foreach ($dto->items as $item) {
            $purchaseItem = $purchase->items()
                ->where('id', $item['purchase_item_id'])
                ->lockForUpdate()
                ->first();

            if (!$purchaseItem) {
                throw ValidationException::withMessages([
                    'items' => "Purchase item {$item['purchase_item_id']} does not belong to this purchase.",
                ]);
            }

            $qty = (int) $item['received_quantity'];
            $remaining = $purchaseItem->quantity - $purchaseItem->received_quantity;

            if ($qty > $remaining) {
                throw ValidationException::withMessages([
                    'items' => "Received quantity cannot exceed remaining quantity for purchase item {$purchaseItem->id}.",
                ]);
            }

            // 1. Update received quantity
            $purchaseItem->increment('received_quantity', $qty);

            // 2. Update product stock
            $purchaseItem->product->increment('stock_qty', $qty);
        }

        // 3. គណនា status
        $purchase->load('items');
        $totalOrdered  = (int) $purchase->items->sum('quantity');
        $totalReceived = (int) $purchase->items->sum('received_quantity');

        $purchase->update([
            'status' => $totalReceived >= $totalOrdered ? 'received' : 'partial',
        ]);

        return $purchase->fresh(['supplier', 'user', 'items.product']);
    });

    // សម្អាត cache ក្រោយ transaction commit
    Cache::forget("purchase_by_id_{$dto->purchase_id}");
    $this->clearPurchaseCache();
    $this->clearProductCache();
    $this->supplierService->clearStatsCache();

    return $purchase;
}
    private function clearPurchaseCache(): void
    {
        Cache::forget('purchase_state');
        if (Cache::has('purchases_cache_version')) {
            Cache::increment('purchases_cache_version');
        } else {
            Cache::put('purchases_cache_version', 2);
        }
    }

    private function clearProductCache(): void
    {
        // ⚠️ ត្រូវតែដូចអ្វីដែល ProductService ប្រើ
        Cache::forget('product_state');


        if (Cache::has('products_cache_version')) {
            Cache::increment('products_cache_version');
        } else {
            Cache::put('products_cache_version', 2);
        }
    }
}
