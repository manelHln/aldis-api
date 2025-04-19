<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    //
    use SoftDeletes, HasUuids, HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        "name",
        "price",
        "image_url",
        "description",
        "is_available",
        "stock",
        "origin",
        "product_type_id",
        "category_id"
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productType(){
        return $this->belongsTo(ProductType::class);
    }

    public function productImages(){
        return $this->hasMany(ProductImage::class);
    }
}
