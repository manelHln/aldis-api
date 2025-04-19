<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductType extends Model
{
    //
    use HasUuids, SoftDeletes, HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        "name",
        "description",
        "image_url"
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function generateSlug(string $name){
        return strtolower(str_replace(' ', '-', $name));
    }
}
