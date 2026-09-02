<?php
namespace App\Repositories\Interfaces;
interface CategoryRepositoryInterface{
    public function create(array $data);
    public function update(string $id,array $data);
    public function all($perPage,array $fillter=[]);
    public function findById(string $id);
    public function delete(string $id);
    public function getCategorystate();
    public function insertBulk(array $data): bool;
    public function getForExport(array $filters=[]);
}
