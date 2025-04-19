<?php

namespace App\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

interface ProductTypeInterface{
    public function getById(string $id);
    public function getAll(Request $request);
    public function createNew(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
    public function updateImage(string $id, UploadedFile $file);
}
