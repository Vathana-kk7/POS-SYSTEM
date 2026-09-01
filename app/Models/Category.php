<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "status"
    ];

    /**
     * បំប្លែងតម្លៃ status ទៅជា 'active' ឬ 'inactive' ស្វ័យប្រវត្តិនឹងបោះទៅ API
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => in_array((string)$value, ['1', 'active', 'true'], true) ? 'active' : 'inactive',
        );
    }

    public function products(){
        return $this->hasMany(Product::class);
    }
}
