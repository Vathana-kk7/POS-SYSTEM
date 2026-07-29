<?php
namespace App\Repositories\Interfaces;
interface OrderPaymentMethodRepositoryInterface{
    public function create(array $data);
    public function update(string $id,array $data);
    public function delete(string $id);
    public function findById(string $id);
    public function all();
}
