<?php

namespace App\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

interface ProductCategoryInterface
{
    public function createNew(array $data);
    public function getAll(Request $request);
    public function getById($id);
    public function update($id, array $data);
    public function delete($id);
    public function getDeleted();
    public function restore($id);
    public function forceDelete($id);
    public function deleteMultiple(array $ids, bool $forceDelete = false);
    public function updateImage($id, UploadedFile $file);
}
