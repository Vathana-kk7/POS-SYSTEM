<?php
namespace App\Repositories\Interfaces;
interface SupplierRepositoryInterface{
    public function create(array $data);
    public function all();
    public function findById(string $id);
    public function update(string $id,array $data);
    public function delete(string $id);
}
