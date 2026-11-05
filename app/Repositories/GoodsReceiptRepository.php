<?php

namespace App\Repositories;

use App\Models\GoodsReceipt;
use App\Repositories\Interfaces\GoodsReceiptRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GoodsReceiptRepository implements GoodsReceiptRepositoryInterface
{
    private const RELATIONS = [
        'user',
        'purchase.supplier',
        'purchase.user',
        'items.purchaseItem.product',
    ];

    public function all(): Collection
    {
        return GoodsReceipt::with(self::RELATIONS)
            ->latest('received_at')
            ->get();
    }

    public function findById(string $id): GoodsReceipt
    {
        return GoodsReceipt::with(self::RELATIONS)->findOrFail($id);
    }

    public function create(array $data, array $items): GoodsReceipt
    {
        $receipt = GoodsReceipt::create($data);
        $receipt->items()->createMany($items);

        return $receipt->load(self::RELATIONS);
    }
}
