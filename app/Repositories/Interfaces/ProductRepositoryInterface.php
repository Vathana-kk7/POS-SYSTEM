<?php
namespace App\Repositories\Interfaces;
interface ProductRepositoryInterface{
    public function create(array $data);
    public function update(string $id,array $data);
    public function all($perPage,array $fillter=[]);
    public function findById(string $id);
    public function getState();
    public function delete(string $id);
    public function existsByBarcode(string $barcode): bool;
    public function insertBulk(array $data):bool;
    public function getForExport(array $filters=[]);
}
