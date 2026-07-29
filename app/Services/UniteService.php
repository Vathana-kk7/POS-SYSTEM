<?php
namespace App\Services;

use App\DTO\Unite\CreateUniteDTO;
use App\DTO\Unite\UpdateUniteDTO;
use App\Models\Unite;
use App\Repositories\Interfaces\UniteRepositoryInterface;

class UniteService{
    public  function __construct(
        private UniteRepositoryInterface $repo
    ){}
    public function create(CreateUniteDTO $dto){
        $result=$this->repo->create([
            "name"=>$dto->name,
            "symbol"=>$dto->symbol,
        ]);
        return $result;
    }
    public function update(string $id,UpdateUniteDTO $dto){
        return $this->repo->update(
            $id,
            [
                "name"=>$dto->name,
                "symbol"=>$dto->symbol,
            ]
        );
    }
    public function all(){
        return $this->repo->all();
    }
    public function findById(string $id){
        return $this->repo->findById($id);
    }
    public function delete(string $id){
        return $this->delete($id);
    }
}
