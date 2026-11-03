<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_no',
        'purchase_date',
        'expected_delivery_date',
        'supplier_id',
        'user_id',
        'subtotal',
        'discount',
        'tax',
        'shipping_cost',
        'total',
        'payment_terms',
        'status',
        'notes',
    ];


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
