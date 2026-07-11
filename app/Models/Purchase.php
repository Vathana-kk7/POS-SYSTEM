<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        "purchase_date",
        "total",
        "status",
        "supplier_id",
        "user_id",
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
