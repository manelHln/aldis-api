<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasUuids;
    //
    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
