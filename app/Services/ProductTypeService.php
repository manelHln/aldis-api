<?php

namespace App\Services;

use App\Contracts\FileStorageInterface;
use App\Models\ProductType;
use App\Contracts\ProductTypeInterface;
use App\Exceptions\AldisModelNotFoundException;
use App\Helpers\PaginationHelper;
use Illuminate\Http\Request;
use \Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductTypeService implements ProductTypeInterface
{
    public function __construct(protected FileStorageInterface $fileStorageService) {}
    public function getById(string $id)
    {
        $product_type = ProductType::find($id);
        if (!$product_type) {
            throw new AldisModelNotFoundException("ProductType not found");
        }
        return $product_type;
    }
    public function getAll(Request $request)
    {
        $query = ProductType::query();
        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('search') . '%');
        }

        if ($request->query('size')) {
            $size = $request->query('size', 20);
            $cursor = $request->query("cursor");
            $result = $query->cursorPaginate($size, ['*'], 'cursor', $cursor);
            return PaginationHelper::cursorPaginated($result);
        }

        return $query->get();
    }
    public function createNew(array $data)
    {
        $product_type = new ProductType();
        $product_type->name = $data['name'];
        $product_type->description = $data['description'];
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $file = $data['image'];
            $product_type->image_url = $this->fileStorageService->store($file, 'product_types', ['image', 'max:5120']);
        }
        $product_type->save();
        return $product_type;
    }
    public function update(string $id, array $data)
    {
        $product_type = ProductType::findOrFail($id);
        $product_type->name = $data['name'] ?? $product_type->name;
        $product_type->description = $data['description'] ?? $product_type->description;

        $product_type->save();
        return $product_type;
    }

    public function updateImage(string $id, UploadedFile $image)
    {
        $product_type = ProductType::find($id);
        if (!$product_type) {
            throw new AldisModelNotFoundException("ProductType not found");
        }

        if ($product_type->image_url) {
            $this->fileStorageService->delete($product_type->image_url);
        }

        $product_type->image_url = $this->fileStorageService->store($image, 'product_types');
        $product_type->save();

        return $product_type;
    }

    public function delete(string $id)
    {
        $product_type = ProductType::findOrFail($id);
        $product_type->delete();
        return true;
    }
}
