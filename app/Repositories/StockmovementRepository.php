<?php
namespace App\Repositories;

use App\Models\StockMovement;
use App\Repositories\Interfaces\StockmovementRepositoryInterface;

class StockmovementRepository implements StockmovementRepositoryInterface{

    public function create(array $data){
        return StockMovement::create($data);
    }
    public function findById(string $id){
        return StockMovement::findOrFail($id);
    }
    public function delete(string $id){
        $result=StockMovement::findOrFail($id);
        $result->delete($id);
        return $result;
    }
    public function all(){
        return StockMovement::all();
    }
    public function update(string $id,array $data){
        $result=StockMovement::findOrFail($id);
        $result->update($data);
        return $result;
    }
}
