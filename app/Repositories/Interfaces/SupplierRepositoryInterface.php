<?php
namespace App\Repositories\Interfaces;
interface SupplierRepositoryInterface{
    public function create(array $data);
    public function all(int $perPage = 10, array $filters = []);
    public function findById(string $id);
    public function update(string $id,array $data);
    public function delete(string $id);
    public function stats(): array;
    public function insertBulk(array $data): bool;
    public function getForExport(array $filters = []);
    public function getForPdfExport(array $filters = []);
    public function existingEmails(array $emails): array;
}
