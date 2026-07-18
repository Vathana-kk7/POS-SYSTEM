<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    protected $table="bank";
    protected $fillable=[
        "name",
        "account_name",
        "account_number",
        "qr_code",
        "status",
    ];

    public function orderPaymentMethod(){
        return $this->hasMany(OrderPaymentMethod::class);
    }
}
