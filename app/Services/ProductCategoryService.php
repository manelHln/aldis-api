<?php

namespace App\Services;

use App\Contracts\FileStorageInterface;
use App\Models\ProductCategory;
use App\Contracts\ProductCategoryInterface;
use Illuminate\Support\Facades\Log;
use App\Exceptions\AldisModelNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Helpers\PaginationHelper;

class ProductCategoryService implements ProductCategoryInterface
{
    public function __construct(protected FileStorageInterface $fileStorageService) {}
    public function createNew(array $data)
    {
        $productCategory = new ProductCategory();
        $productCategory->name = $data['name'];
        $productCategory->description = $data['description'];

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['image'];
            $productCategory->image_url = $this->fileStorageService->store($file, 'product_categories', ['image', 'max:5120']);
        }

        $productCategory->save();

        return $productCategory;
    }

    public function getAll(Request $request)
    {
        $query = ProductCategory::query();
        if($request->has('size')){
            $size = $request->input('size');
            $cursor = $request->query("cursor");
            $result = $query->cursorPaginate($size, ['*'], 'cursor', $cursor);
            return PaginationHelper::cursorPaginated($result);
        }
        return $query->get();
    }

    public function getById($id)
    {
        $category = ProductCategory::withTrashed()->find($id);
        if (!$category) {
            throw new AldisModelNotFoundException("Product category with ID {$id} not found.");
        }
        return $category;
    }

    public function update($id, array $data)
    {
        $productCategory = ProductCategory::find($id);
        if (!$productCategory) {
            throw new AldisModelNotFoundException("Product category with ID {$id} not found.");
        }

        $productCategory->name = $data['name'];
        $productCategory->description = $data['description'];
        $productCategory->save();

        return $productCategory;
    }

    public function updateImage($id, \Illuminate\Http\UploadedFile $file)
    {
        $productCategory = ProductCategory::find($id);
        if (!$productCategory) {
            throw new AldisModelNotFoundException("Product category with ID {$id} not found.");
        }

        if ($productCategory->image_url) {
            $this->fileStorageService->delete($productCategory->image_url);
        }
        $productCategory->image_url = $this->fileStorageService->store($file, 'product_categories', ['image', 'max:5120']);
        $productCategory->save();

        return $productCategory;
    }

    public function delete($id)
    {
        $productCategory = ProductCategory::findOrFail($id);
        if (!$productCategory) {
            throw new AldisModelNotFoundException("Product category with ID {$id} not found.");
        }
        $productCategory->delete();
        if ($productCategory->image_url) {
            $this->fileStorageService->delete($productCategory->image_url);
        }
        return $productCategory;
    }
    public function getDeleted()
    {
        return ProductCategory::onlyTrashed()->get();
    }
    public function restore($id)
    {
        $productCategory = ProductCategory::withTrashed()->find($id);
        if (!$productCategory) {
            throw new AldisModelNotFoundException("Product category with ID {$id} not found.");
        }
        $productCategory->restore();

        return $productCategory;
    }
    public function forceDelete($id)
    {
        try {
            $productCategory = ProductCategory::withTrashed()->findOrFail($id);
            if ($productCategory->image_url) {
                $this->fileStorageService->delete($productCategory->image_url);
            }
            $productCategory->forceDelete();

            return $productCategory;
        } catch (ModelNotFoundException $e) {
            Log::warning("Product category not found for permanent deletion: ID {$id}");
            throw new AldisModelNotFoundException("Product category with ID {$id} not found.");
        }
    }
    public function deleteMultiple(array $ids, bool $forceDelete = false)
    {
        $deletedProductCategories = [];
        foreach ($ids as $id) {
            $productCategory = ProductCategory::withTrashed()->findOrFail($id);
            if (!$productCategory) {
                throw new AldisModelNotFoundException("Product category with ID {$id} not found.");
            }
            if ($productCategory->image_url) {
                $this->fileStorageService->delete($productCategory->image_url);
            }
            // $productCategory->delete();
            if ($forceDelete) {
                $productCategory->forceDelete();
            } else {
                $productCategory->delete();
            }
            $deletedProductCategories[] = $productCategory;
        }
        return $deletedProductCategories;
    }
}
