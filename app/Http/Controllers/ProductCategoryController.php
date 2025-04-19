<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\ProductCategoryInterface;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Product Categories",
 *     description="Product category management endpoints"
 * )
 */
class ProductCategoryController extends Controller
{
    //
    public function __construct(protected ProductCategoryInterface $productCategoryService) {}
    /**
     * Display a listing of the resource.
     * @OA\Get(
     *     path="/product-categories",
     *     tags={"Product Categories"},
     *     summary="Get all product categories",
     *     description="Retrieve a list of all product categories.",
     *     @OA\Response(
     *         response=200,
     *         description="List of product categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $productCategories = $this->productCategoryService->getAll($request);

        return response()->json([
            "message" => "List of resource",
            "data" => $productCategories
        ]);
    }

    /**
     * @OA\Post(
     *     path="/product-categories",
     *     tags={"Product Categories"},
     *     summary="Create a new product category",
     *     description="Add a new product category to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Electronics")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product category created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request){
        $this->authorize("create", ProductCategory::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'description');
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $productCategory = $this->productCategoryService->createNew($data);

        return response()->json([
            'message' => 'Product category created successfully.',
            'data' => $productCategory
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/product-categories/{id}",
     *     tags={"Product Categories"},
     *     summary="Get a product category by ID",
     *     description="Retrieve details of a specific product category by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product category ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product category not found"
     *     )
     * )
     */
    public function show(string $id){
        $productCategory = $this->productCategoryService->getById($id);

        return response()->json([
            'message' => 'Product category retrieved successfully.',
            'data' => $productCategory
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/product-categories/{id}",
     *     tags={"Product Categories"},
     *     summary="Update a product category",
     *     description="Update details of an existing product category.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product category ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Electronics")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product category updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product category not found"
     *     )
     * )
     */
    public function update(Request $request, string $id){
        $request->validate([
            'name' => 'string',
            'description' => 'string',
        ]);

        $data = $request->only('name', 'description');

        $productCategory = $this->productCategoryService->update($id, $data);

        return response()->json([
            'message' => 'Product category updated successfully.',
            'data' => $productCategory
        ], 200);
    }

    public function updateImage(Request $request, string $id){
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $request->file('image');

        $productCategory = $this->productCategoryService->updateImage($id, $file);

        return response()->json([
            'message' => 'Product category image updated successfully.',
            'data' => $productCategory
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/product-categories/{id}",
     *     tags={"Product Categories"},
     *     summary="Delete a product category",
     *     description="Remove a product category from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product category ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product category not found"
     *     )
     * )
     */
    public function destroy(string $id){
        $this->productCategoryService->delete($id);

        return response()->json([
            'message' => 'Product category deleted successfully.',
            'data' => null
        ], 200);
    }

    public function restore(string $id){
        $productCategory = $this->productCategoryService->restore($id);

        return response()->json([
            'message' => 'Product category restored successfully.',
            'data' => $productCategory
        ], 200);
    }
    public function forceDelete(string $id){
        $this->productCategoryService->forceDelete($id);

        return response()->json([
            'message' => 'Product category permanently deleted successfully.',
            'data' => null
        ], 200);
    }
    public function getDeleted(){
        $deletedProductCategories = $this->productCategoryService->getDeleted();

        return response()->json([
            'message' => 'Deleted product categories retrieved successfully.',
            'data' => $deletedProductCategories
        ], 200);
    }

    public function deleteMultiple(Request $request)
    {
        // Validate that we receive an array of IDs
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:product_categories,id'
        ]);

        if ($validator->fails()) {
            $inputIds = $request->input('ids', []);
            $customErrors = [];

            foreach ($inputIds as $index => $id) {
                if ($validator->errors()->has("ids.$index")) {
                    $customErrors[] = "The ID '{$id}' is invalid or does not exists.";
                }
            }

            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'ids' => $customErrors
                ]
            ], 422);
        }


        $ids = $request->input('ids');
        $deleted_ids = $this->productCategoryService->deleteMultiple($ids);
        return response()->json([
            'message' => 'Product categories deleted successfully.',
            'deleted_ids' => $deleted_ids
        ], 200);
    }

}
