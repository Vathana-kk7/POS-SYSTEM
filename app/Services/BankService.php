<?php
namespace App\Services;

use App\DTO\Bank\CreateBankDTO;
use App\DTO\Bank\UpdateBankDTO;
use App\Repositories\Interfaces\BankRepositoryInterface;

class BankService{
    public function __construct(
        private BankRepositoryInterface $repo
    ){}
    public function create(CreateBankDTO $dto)
    {
        $result=$this->repo->create([
            "name"=>$dto->name,
            "account_name"=>$dto->account_name,
            "account_number"=>$dto->account_number,
            "qr_code"=>$dto->qr_code,
            "status"=>$dto->status,
        ]);
        return $result;
    }
    public function update(string $id,UpdateBankDTO $dto)
    {
        $result=$this->repo->update(
            $id,
            [
                "name"=>$dto->name,
                "account_name"=>$dto->account_name,
                "account_number"=>$dto->account_number,
                "qr_code"=>$dto->qr_code,
                "status"=>$dto->status,
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
