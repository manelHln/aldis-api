<?php

namespace App\Http\Controllers;

use App\Contracts\ProductInterface;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Product management endpoints"
 * )
 */
class ProductController extends Controller
{
    public function __construct(protected ProductInterface $productService) {}
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/products",
     *     tags={"Products"},
     *     summary="Get all products",
     *     description="Retrieve a paginated list of all products.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $result = $this->productService->getAll($request);

        return response()->json([
            "message" => "List of resource",
            "data" => $result,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/products",
     *     tags={"Products"},
     *     summary="Create a new product",
     *     description="Add a new product to the catalog.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "description", "category", "stock", "image_url", "origin", "type"},
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             @OA\Property(property="description", type="string", example="Product description"),
     *             @OA\Property(property="category", type="string", example="Electronics"),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="image_url", type="string", example="http://example.com/image.jpg"),
     *             @OA\Property(property="origin", type="string", example="USA"),
     *             @OA\Property(property="type", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Log::info('ProductController@store', [
        //     'request' => $request->all(),
        // ]);
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'price' => 'required|decimal:0,2',
            'description' => 'required|string',
            'category_id' => 'required|uuid',
            'stock' => 'required|integer',
            'origin' => 'required',
            'product_type_id' => 'uuid|required',
            'image' => 'required|image|max:5120'
        ]);

        $product = $this->productService->createNew($request->all());

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product
        ], 201);
    }

    public function addTowishlist(Request $request, string $id){
        $this->productService->addToWishlist($id);

        return response()->json([
            'message' => 'Product added to wishlist',
            'data' => []
        ], 200);
    }

    public function removeFromWishlist(Request $request, string $id){
        $this->productService->removeFromWishlist($id);

        return response()->json([
            'message' => 'Product removed from wishlist',
            'data' => []
        ], 200);
    }

    public function getWishlist(Request $request){
        $result = $this->productService->getWishlist($request);

        return response()->json([
            'message' => 'List of wishlist',
            'data' => $result
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/products/{id}",
     *     tags={"Products"},
     *     summary="Get a product by ID",
     *     description="Retrieve details of a specific product by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        //
        $product = $this->productService->getById($id);

        return response()->json([
            "message" => "Product retrieved successfully.",
            "data" => $product
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/products/{id}",
     *     tags={"Products"},
     *     summary="Update a product",
     *     description="Update details of an existing product.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=79.99),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="category", type="string", example="Updated category"),
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(property="image_url", type="string", example="http://example.com/updated-image.jpg"),
     *             @OA\Property(property="origin", type="string", example="Canada"),
     *             @OA\Property(property="type", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        // Gate::authorize("update", Product::class);
        $request->validate([
            'name' => 'string|max:255|unique:products,name,' . $id,
            'price' => 'decimal:0,2',
            'description' => 'string',
            'category_id' => 'uuid',
            'stock' => 'integer',
            'origin' => 'string',
            'product_type_id' => 'uuid'
        ]);

        $product = $this->productService->update($id, $request->all());
            return response()->json([
            "message" => "Product updated successfully.",
            "data" => $product
        ], 200);
    }

    /**
     * Update the image of the specified resource in storage.
     *
     * @OA\Put(
     *     path="/products/{id}/image",
     *     tags={"Products"},
     *     summary="Update product image",
     *     description="Update the image of an existing product.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product image updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product image updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function updateImage(Request $request, string $id)
    {
        $request->validate([
            'image' => 'required|image|max:5120'
        ]);

        $product = $this->productService->updateImage($id, $request->file('image'));

        return response()->json([
            "message" => "Product image updated successfully.",
            "data" => $product
        ], 200);
    }

    public function updateGallery(Request $request, string $id)
    {
        if(!$request->hasFile('gallery')) {
            return response()->json([
                "message" => "Gallery images are required.",
                "data" => null
            ], 422);
        }

        $product = $this->productService->updateGallery($id, $request->all());

        return response()->json([
            "message" => "Product gallery updated successfully.",
            "data" => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/products/{id}",
     *     tags={"Products"},
     *     summary="Delete a product",
     *     description="Remove a product from the catalog.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $product = $this->productService->delete($id);

        return response()->json([
            "message" => "Product successfully moved to trash.",
            "data" => null
        ], 200);
    }
}
