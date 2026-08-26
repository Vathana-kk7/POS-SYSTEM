<?php
namespace App\Repositories\Interfaces;

interface BrandRepositoryInterface{
    public function create(array $data);
    public function all($perPage, array $filters = []);
    public function findById(string $id);
    public function update(string $id,array $data);
    public function getBrandStats();
    // public function delete(string $id);
    public function delete(string $id): bool;
    public function paginate(int $perPage = 10);
    public function insertBulk(array $data): bool;
    public function getForExport(array $filters = []);
}
