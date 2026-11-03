<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    /**
     * Create Purchase
     */
    public function create(array $data)
    {
        return Purchase::create($data);
    }


    /**
     * Update Purchase
     */
    public function update(string $id, array $data)
    {
        $purchase = Purchase::findOrFail($id);

        $purchase->update($data);

        return $purchase->fresh();
    }


    /**
     * Get all Purchases
     */
    public function all()
{
    return Purchase::with([
        'supplier',
        'user',
        'items.product',   // ← បន្ថែម
    ])
    ->latest()
    ->get();
}

public function findById(string $id)
{
    return Purchase::with([
        'supplier',
        'user',
        'items.product',   // ← ប្តូរពី 'items' ទៅ 'items.product'
    ])->findOrFail($id);
}


    /**
     * Delete Purchase
     */
    public function delete(string $id)
    {
        $purchase = Purchase::findOrFail($id);

        $purchase->delete();

        return $purchase;
    }

    public function getstate(){
            return [
                'total_orders'    => Purchase::count(),
                'total_value'     => (float) Purchase::where('status', '!=', 'cancelled')->sum('total'),
                'received_count'  => Purchase::whereIn('status', ['received', 'partial'])->count(),
                'pending_count'   => Purchase::whereIn('status', ['pending', 'approved'])->count(),
                'cancelled_count' => Purchase::where('status', 'cancelled')->count(),
            ];
    }

    /**
     * Get latest Purchase Order Number
     */
    public function getLastPurchaseOrderNo(string $date)
    {
        return Purchase::where(
            'purchase_order_no',
            'like',
            'PO-' . $date . '-%'
        )
        ->orderByDesc('purchase_order_no')
        ->first();
    }
}
