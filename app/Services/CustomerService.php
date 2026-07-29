<?php
namespace App\Services;

use App\DTO\Customer\CreateCustomerDTO;
use App\DTO\Customer\UpdateCustomerDTO;
use App\Repositories\Interfaces\CustomerRepositoryInterface;

class CustomerService{
    public function __construct(
        private CustomerRepositoryInterface $repo
    ){}
    public function create(CreateCustomerDTO $dto)
    {
        $result=$this->repo->create([
            "name"=>$dto->name,
            "phone"=>$dto->phone,
            "address"=>$dto->address,
        ]);
        return $result;
    }
    public function update(string $id,UpdateCustomerDTO $dto)
    {
        $result=$this->repo->update(
            $id,
            [
                "name"=>$dto->name,
                "phone"=>$dto->phone,
                "address"=>$dto->address,
            ]
        );
        return $result;
    }
    public function all()
    {
        return $this->repo->all();
    }
    public function findById(string $id)
    {
        return $this->repo->findById($id);
    }
    public function delete(string $id)
    {
        return $this->repo->delete($id);
    }
}
