<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPaymentMethod extends Model
{
    use HasFactory;
    protected $fillable=[
        "order_id",
        "bank_id",
    ];

    public function bank(){
        return $this->belongsTo(Bank::class);
    }
    public function order(){
        return $this->belongsTo(Order::class);
    }

}
