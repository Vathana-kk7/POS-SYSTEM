<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table="products";
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
    public function suppliers()
    {
        return $this->belongsToMany(
            Supplier::class,
            'product_supplier',
            'product_id',
            'supplier_id'
        );
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function brand(){
        return $this->belongsTo(Brand::class);
    }
}
