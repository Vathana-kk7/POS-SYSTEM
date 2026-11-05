<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable=[
        "name",
        "contact_person",
        "email",
        "address",
        "phone",
        "status",
        "bank_name",
        "account_name",
        "account_number",
        "bakong_account_id",
        "khqr_image",
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_supplier',
            'supplier_id',
            'product_id'
        );
    }
}
