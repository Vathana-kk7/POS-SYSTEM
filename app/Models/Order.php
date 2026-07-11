<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        "total",
        "discount",
        "tax",
        "grand_total",
        "payment_status",
        "order_status",
        "customer_id",
    ];
    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function orderitems(){
        return $this->hasMany(OrderItem::class);
    }
    public function orderPaymentMethods(){
        return $this->hasMany(OrderPaymentMethod::class);
    }

}
