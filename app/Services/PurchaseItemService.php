<?php

namespace App\Services;

use App\DTO\PurchaseItem\CreatePurchaseItemDTO;
use App\DTO\PurchaseItem\UpdatePurchaseItemDTO;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class PurchaseItemService
{
    public function __construct(
        private PurchaseItemRepositoryInterface $repo
    ) {}

    public function create(CreatePurchaseItemDTO $dto)
    {
        $result = $this->repo->create([
            'purchase_id' => $dto->purchase_id,
            'product_id'  => $dto->product_id,
            'quantity'    => $dto->quantity,
            'cost_price'  => $dto->cost_price,
            'discount'    => $dto->discount,
            'tax'         => $dto->tax,
            'subtotal'    => $dto->subtotal,
        ]);

        $this->clearPurchaseItemCache();

        return $result;
    }

    public function update(string $id, UpdatePurchaseItemDTO $dto)
    {
        $result = $this->repo->update($id, [
            'purchase_id' => $dto->purchase_id,
            'product_id'  => $dto->product_id,
            'quantity'    => $dto->quantity,
            'cost_price'  => $dto->cost_price,
            'discount'    => $dto->discount,
            'tax'         => $dto->tax,
            'subtotal'    => $dto->subtotal,
        ]);

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
        $result = $this->repo->delete($id);

        Cache::forget("purchase_item_by_id_{$id}");
        $this->clearPurchaseItemCache();

        return $result;
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
