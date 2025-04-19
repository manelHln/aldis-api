<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Contracts\ProductTypeInterface;

/**
 * @OA\Tag(
 *     name="Product Types",
 *     description="Product type management endpoints"
 * )
 */
class ProductTypeController extends Controller
{
    public function __construct(protected ProductTypeInterface $productTypeService) {}
    /**
     * @OA\Get(
     *     path="/product-types",
     *     tags={"Product Types"},
     *     summary="Get all product types",
     *     description="Retrieve a list of all product types.",
     *     @OA\Response(
     *         response=200,
     *         description="List of product types",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $data = $this->productTypeService->getAll($request);

        return response()->json([
            "message" => "List of resource",
            "data" => $data
        ]);
    }

    /**
     * @OA\Post(
     *     path="/product-types",
     *     tags={"Product Types"},
     *     summary="Create a new product type",
     *     description="Add a new product type to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Electronics")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product type created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product type created successfully"),
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
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'image' => 'required|image|max:5120'
        ]);
        $product_type = $this->productTypeService->createNew($request->all());
        return response()->json([
            'message' => 'Product type created successfully.',
            'data' => $product_type
        ], 201)->withHeaders([
            'Location' => route('product_types.show', ['id' => $product_type->id]),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/product-types/{id}",
     *     tags={"Product Types"},
     *     summary="Get a product type by ID",
     *     description="Retrieve details of a specific product type by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product type ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product type details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product type not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $product_type = $this->productTypeService->getById($id);

        return response()->json([
            'message' => 'Product type retrieved successfully.',
            'data' => $product_type
        ]);
    }

    /**
     * @OA\Put(
     *     path="/product-types/{id}",
     *     tags={"Product Types"},
     *     summary="Update a product type",
     *     description="Update details of an existing product type.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product type ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Electronics"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="image", type="string", format="binary", example="image.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product type updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product type updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product type not found"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
        ]);

        $product_type = $this->productTypeService->update($id, $request->only('name', 'description'));
        return response()->json([
            'message' => 'Product type updated successfully.',
            'data' => $product_type
        ]);
    }

    /**
     * @OA\Put(
     *     path="/product-types/{id}/image",
     *     tags={"Product Types"},
     *     summary="Update product type image",
     *     description="Update the image of a specific product type.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product type ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"image"},
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product type not found"
     *     )
     * )
     */
    public function updateImage(Request $request, string $id)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $product_type = $this->productTypeService->updateImage($id, $request->file('image'));

        return response()->json([
            'message' => 'Image updated successfully.',
            'data' => $product_type
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/product-types/{id}",
     *     tags={"Product Types"},
     *     summary="Delete a product type",
     *     description="Remove a product type from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product type ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product type deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product type deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product type not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $product_type = $this->productTypeService->delete($id);
        return response()->json([
            'message' => 'Product type deleted successfully.',
            'data' => $product_type
        ]);
    }
}
