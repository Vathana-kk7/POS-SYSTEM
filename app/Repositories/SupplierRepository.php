<?php
namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepositoryInterface;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function create(array $data){
        return Supplier::create($data);
    }
    public function all(){
        return Supplier::all();
    }
    public function findById(string $id){
        return  Supplier::findOrFail($id);
    }
    public function update(string $id,array $data){
        $supplier=Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier->refresh();
    }
    public function delete(string $id){
        $delete=Supplier::findOrFail($id);
        $delete->delete($id);
        return $delete->refresh();
    }

}
