<?php
namespace App\Repositories\Interfaces;
interface CustomerRepositoryInterface{
    public function create(array $data);
    public function update(string $id,array $data);
    public function delete(string $id);
    public function findById(string $id);
    public function all(int $perPage = 10, array $filters = []);
    public function stats(): array;
    public function insertBulk(array $data): bool;
    public function getForExport(array $filters = []);
    public function existingEmails(array $emails): array;
}
