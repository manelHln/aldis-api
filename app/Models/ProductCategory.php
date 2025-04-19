<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    //
    use HasUuids, SoftDeletes, HasFactory;
    protected $fillable = [
        'name',
        'description',
        'image_url'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
