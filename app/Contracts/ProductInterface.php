<?php

namespace App\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

interface ProductInterface
{
    //
    public function createNew(array $data);
    public function delete(string $id);
    public function update(string $id, array $data);
    public function getAll(Request $request);
    public function getById(string $id);
    public function addToWishlist(string $id);
    public function getWishlist(Request $request);
    public function removeFromWishlist(string $id);
    public function updateImage(string $id, UploadedFile $file);
    public function updateGallery(string $id, array $data);
}
