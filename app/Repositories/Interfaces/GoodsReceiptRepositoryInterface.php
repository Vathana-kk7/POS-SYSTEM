<?php

namespace App\Repositories\Interfaces;

use App\Models\GoodsReceipt;
use Illuminate\Database\Eloquent\Collection;

interface GoodsReceiptRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): GoodsReceipt;

    public function create(array $data, array $items): GoodsReceipt;
}
