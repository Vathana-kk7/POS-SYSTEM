<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    use HasFactory;
    protected $table = "product_supplier";
    protected $fillable=[
        "product_id",
        "supplier_id",
    ];
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
