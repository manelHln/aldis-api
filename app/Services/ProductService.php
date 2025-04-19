<?php

namespace App\Services;

use App\Contracts\FileStorageInterface;
use App\Contracts\ProductCategoryInterface;
use App\Contracts\ProductImageInterface;
use App\Models\Product;
use App\Contracts\ProductInterface;
use App\Models\FavoriteProduct;
use App\Contracts\ProductTypeInterface;
use Illuminate\Http\UploadedFile;
use Exception;
use App\Exceptions\AldisModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PaginationHelper;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ProductService implements ProductInterface
{
    public function __construct(protected ProductTypeInterface $productTypeService, protected FileStorageInterface $fileStorageService, protected ProductCategoryInterface $productCategoryService, protected ProductImageInterface $productImageService) {}
    public function createNew(array $data)
    {
        $product_type = $this->productTypeService->getById($data['product_type_id']);

        $product_category = $this->productCategoryService->getById($data['category_id']);

        $product = new Product();
        $product->name = $data['name'];
        $product->price = $data['price'];
        $product->description = $data['description'];
        $product->stock = $data['stock'];
        $product->origin = $data['origin'];
        $product->product_type_id = $product_type->id;

        $product->category_id = $product_category->id;
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $file = $data['image'];
            $product->image_url = $this->fileStorageService->store($file, 'products', ['image', 'max:5120']);
        }

        $product->save();

        if (isset($data['gallery'])) {
            foreach ($data['gallery'] as $file) {
                if ($file instanceof UploadedFile) {
                    $productImage = $this->productImageService->createNew([
                        'image' => $file,
                        'product_id' => $product->id,
                    ]);
                }
            }
        }

        return $product->load('productType', 'category', 'productImages');
    }

    public function addToWishlist($id)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new AldisModelNotFoundException("Product not found. Cannot add to wishlist");
        }
        $favoriteProduct = new FavoriteProduct();
        $favoriteProduct->product_id = $id;
        $favoriteProduct->user_id = Auth::id();
        $favoriteProduct->save();

        return $product;
    }
    public function getWishlist(Request $request)
    {
        $query = FavoriteProduct::with('product')->where('user_id', Auth::id());

        if ($request->query('category')) {
            $category = $request->query('category');
            $query->whereHas('product.category', function ($q) use ($category) {
                $q->where('name', 'ilike', "%$category%");
            });
        }

        if ($request->query('product_type')) {
            $product_type = $request->query('product_type');
            $query->whereHas('product.productType', function ($q) use ($product_type) {
                $q->where('name', 'ilike', "%$product_type%");
            });
        }

        if ($request->query('price_min')) {
            $price_min = $request->query('price_min');
            $query->whereHas('product', function ($q) use ($price_min) {
                $q->where('price', '>=', $price_min);
            });
        }

        if ($request->query('price_max')) {
            $price_max = $request->query('price_max');
            $query->whereHas('product', function ($q) use ($price_max) {
                $q->where('price', '<=', $price_max);
            });
        }

        if ($request->query('is_available')) {
            $is_available = $request->query('is_available');
            $query->whereHas('product', function ($q) use ($is_available) {
                $q->where('is_available', '=', (bool)$is_available);
            });
        }

        if ($request->query('stock_min')) {
            $stock_min = $request->query('stock_min');
            $query->whereHas('product', function ($q) use ($stock_min) {
                $q->where('stock', '>=', (int)$stock_min);
            });
        }

        if ($request->query('stock_max')) {
            $stock_max = $request->query('stock_max');
            $query->whereHas('product', function ($q) use ($stock_max) {
                $q->where('stock', '<=', (int)$stock_max);
            });
        }

        return [
            'items' => $query->get()->pluck("product"),
        ];
    }

    public function getAll(Request $request)
    {
        $query = Product::query();
        if ($request->query('category')) {
            $category = $request->query('category');
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', 'ilike', "%$category%");
            });
        }

        if ($request->query('product_type')) {
            $product_type = $request->query('product_type');
            $query->whereHas('productType', function ($q) use ($product_type) {
                $q->where('name', 'ilike', "%$product_type%");
            });
        }

        if ($request->query('price_min')) {
            $price_min = $request->query('price_min');
            $query->where('price', '>=', $price_min);
        }

        if ($request->query('price_max')) {
            $price_max = $request->query('price_max');
            $query->where('price', '<=', $price_max);
        }

        if ($request->query('is_available')) {
            $is_available = $request->query('is_available');
            $query->where('is_available', $is_available);
        }

        if ($request->query('stock_min')) {
            $stock_min = $request->query('stock_min');
            $query->where('stock', '>=', $stock_min);
        }

        if ($request->query('stock_max')) {
            $stock_max = $request->query('stock_max');
            $query->where('stock', '<=', $stock_max);
        }

        if ($request->query('size')) {
            $size = $request->query('size', 20);
            $result = $query->cursorPaginate($size);
            return PaginationHelper::cursorPaginated($result);
        }

        return $query->get();
    }

    public function getById($id)
    {
        $product = Product::with('productImages')->find($id);
        if (!$product) {
            throw new AldisModelNotFoundException("Product not found");
        }
        return $product;
    }

    public function delete($id)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new AldisModelNotFoundException("Product not found");
        }
        $product->delete();

        return true;
    }

    public function updateImage(string $id, UploadedFile $file)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new AldisModelNotFoundException("Product with ID $id not found");
        }

        if ($product->image_url) {
            $this->fileStorageService->delete($product->image_url);
        }
        $this->fileStorageService->delete($product->image_url);
        $product->image_url = $this->fileStorageService->store($file, 'products', ['image', 'max:5120']);
        $product->save();
        return $product;
    }

    public function updateGallery(string $id, array $data)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new AldisModelNotFoundException("Product with ID $id not found");
        }

        foreach ($data['gallery'] as $file) {
            if ($file instanceof UploadedFile) {
                $productImage = $this->productImageService->createNew([
                    'image' => $file,
                    'product_id' => $product->id
                ]);
            }
        }

        return $product->load('productImages');
    }

    public function update($id, array $data)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new AldisModelNotFoundException("Product not found");
        }
        // $product->update($data);
        if (isset($data['product_type_id'])) {
            $product_type = $this->productTypeService->getById($data['product_type_id']);
            $product->product_type_id = $product_type->id;
        }

        if (isset($data['category_id'])) {
            $product_category = $this->productCategoryService->getById($data['category_id']);
            $product->category_id = $product_category->id;
        }

        $product->name = $data['name'] ?? $product->name;
        $product->price = $data['price'] ?? $product->price;
        $product->description = $data['description'] ?? $product->description;
        $product->stock = $data['stock'] ?? $product->stock;
        $product->origin = $data['origin'] ?? $product->origin;
        $product->is_available = $data['is_available'] ?? $product->is_available;

        $product->save();

        return $product;
    }

    public function removeFromWishlist($id)
    {
        $product = Product::find($id);
        if (!$product) {
            throw new AldisModelNotFoundException("Product not found. Cannot remove from wishlist");
        }
        $favoriteProduct = FavoriteProduct::where('product_id', $id)->where('user_id', Auth::id())->first();
        if (!$favoriteProduct) {
            throw new BadRequestException("Product not found in wishlist");
        }
        if ($favoriteProduct) {
            $favoriteProduct->delete();
        }

        return $product;
    }
}
