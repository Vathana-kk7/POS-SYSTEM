<?php

namespace App\Services;

use App\DTO\Purchase\CreatePurchaseDTO;
use App\DTO\Purchase\UpdatePurchaseDTO;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
            $lockedPurchase = Purchase::query()->lockForUpdate()->findOrFail($id);
            if (
                $lockedPurchase->goodsReceipts()->exists()
                || $lockedPurchase->items()->where('received_quantity', '>', 0)->exists()
                || $lockedPurchase->payments()->exists()
            ) {
                throw ValidationException::withMessages([
                    'purchase' => 'A purchase order with receipts or payments cannot be edited.',
                ]);
            }

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
            $purchase = Purchase::query()->lockForUpdate()->findOrFail($id);

            if (
                $purchase->goodsReceipts()->exists()
                || $purchase->items()->where('received_quantity', '>', 0)->exists()
                || $purchase->payments()->exists()
            ) {
                throw ValidationException::withMessages([
                    'purchase' => 'A purchase order with receipts or payments cannot be deleted.',
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
    public function recordPayment(int $purchaseId, int $userId, array $data)
    {
        $proofFile = $data['proof_image'] ?? null;
        $proofPath = $proofFile?->store('purchase-payments/proofs', 'public');
        if ($proofFile && !$proofPath) {
            throw new \RuntimeException('Unable to store payment proof image.');
        }

        try {
            $payment = DB::transaction(function () use ($purchaseId, $userId, $data, $proofPath) {
                $purchase = Purchase::query()->lockForUpdate()->findOrFail($purchaseId);
                if (!in_array($purchase->status, ['approved', 'partial', 'received'], true)) {
                    throw ValidationException::withMessages([
                        'purchase' => 'Payments can only be recorded for approved or received purchases.',
                    ]);
                }

                $totalCents = (int) round((float) $purchase->total * 100);
                $paidCents = (int) round((float) PurchasePayment::query()
                    ->where('purchase_id', $purchase->id)
                    ->sum('amount') * 100);
                $amountCents = (int) round((float) $data['amount'] * 100);
                $balanceCents = max(0, $totalCents - $paidCents);

                if ($amountCents > $balanceCents) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount cannot exceed the purchase balance due.',
                    ]);
                }

                $payment = PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $purchase->supplier_id,
                    'amount' => number_format($amountCents / 100, 2, '.', ''),
                    'method' => $data['method'],
                    'reference_no' => $data['reference_no'],
                    'proof_image' => $proofPath,
                    'notes' => $data['notes'] ?? null,
                ]);

                $purchase->update([
                    'payment_status' => $amountCents === $balanceCents ? 'paid' : 'partial',
                ]);

                return $payment->fresh(['purchase', 'supplier']);
            });
        } catch (\Throwable $exception) {
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }
            throw $exception;
        }

        Cache::forget("purchase_by_id_{$purchaseId}");
        $this->clearPurchaseCache();

        return $payment;
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

}
