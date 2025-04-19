<?php

namespace App\Services;

use App\Contracts\FileStorageInterface;
use App\Contracts\ProductImageInterface;
use App\Models\ProductImage;

class ProductImageService implements ProductImageInterface{
    public function __construct(protected FileStorageInterface $fileStorageService){}
    public function getAll(){

    }

    public function createNew(array $data){
        $productImage = new ProductImage();
        $productImage->product_id = $data['product_id'];

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['image'];
            $productImage->image_url = $this->fileStorageService->store($file, 'products', ['image', 'max:5120']);
        }

        $productImage->save();

        return $productImage;
    }

    public function update(string $id, array $data){

    }

    public function delete(string $id){

    }
}
