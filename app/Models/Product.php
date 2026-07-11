<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable=[
        "name",
        "stock_qty",
        "cost_price",
        "description",
        "sku",
        "image",
        "min_stock_level",
        "status",
        "selling_price",
        "brand_id",
        "category_id",
    ];
      //Relationships
      public function category(){
          return $this->belongsTo(Category::class);
    }
    public function stockMovements(){
        return $this->hasMany(StockMovement::class);
    }
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function brand(){
        return $this->belongsTo(Brand::class);
    }
}
