<?php
namespace App\Repositories\Interfaces;
interface PurchaseItemRepositoryInterface{
    public function create(array $data);
    public function update(string $id,array $data);
    public function all();
    public function findById(string $id);
    public function delete(string $id);
}
