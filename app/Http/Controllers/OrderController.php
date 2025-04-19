<?php

namespace App\Http\Controllers;

use App\Contracts\OrderInterface;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management endpoints"
 * )
 */
class OrderController extends Controller
{
    public function __construct(protected OrderInterface $orderService) {}

    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Get all orders",
     *     description="Retrieve a paginated list of all orders.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $orders = $this->orderService->getAll($request);

        return response()->json([
            "message" => "Orders retrieved successfully",
            "data" => $orders
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/orders/user/{user_id}",
     *     tags={"Orders"},
     *     summary="Get orders by user ID",
     *     description="Retrieve a list of orders for a specific user.",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string", example="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders for the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getUserOrders(Request $request, string $user_id)
    {
        $orders = $this->orderService->getUserOrders($user_id, $request);

        return response()->json([
            "message" => "Orders retrieved successfully",
            "data" => $orders
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/orders/me",
     *     tags={"Orders"},
     *     summary="Get current user's orders",
     *     description="Retrieve a list of orders for the currently authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="List of orders for the current user",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getCurrentUserOrders(Request $request)
    {
        $orders = $this->orderService->getUserOrders($request->user()->id, $request);

        return response()->json([
            "message" => "Orders retrieved successfully",
            "data" => $orders
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Add a new order with associated products.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status", "user_id", "location_id", "products"},
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="user_id", type="string", example="uuid"),
     *             @OA\Property(property="location_id", type="string", example="uuid"),
     *             @OA\Property(property="products", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="string", example="uuid"),
     *                 @OA\Property(property="quantity", type="integer", example=2)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|uuid|exists:user_locations,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|uuid|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);
        $data['user_id'] = $request->user()->id;

        $order = $this->orderService->createNew($data);

        return response()->json([
            "message" => "Order created successfully",
            "data" => $order
        ], 201)->withHeaders([
            'Location' => route('orders.show', ['id' => $order->id])
        ]);
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get an order by ID",
     *     description="Retrieve details of a specific order by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="string", example="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show($id)
    {
        $order = $this->orderService->getById($id);

        return response()->json([
            "message" => "Order retrieved successfully",
            "data" => $order
        ], 200);
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|string|max:50'
        ]);

        $order = $this->orderService->updateStatus($id, $data['status']);

        return response()->json([
            "message" => "Order status updated successfully",
            "data" => $order
        ], 200);
    }

    public function assignDeliveryman(Request $request, string $order_id, string $delivery_man_id)
    {
        // $data = $request->validate([
        //     "delivery_man_id" => "required|uuid|exists:orders,id"
        // ]);

        $order = $this->orderService->assignDeliveryMan($order_id, $delivery_man_id);

        return response()->json([
            "message" => "Delivery man assigned to order successfully",
            "data" => $order
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Delete an order",
     *     description="Remove an order from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="string", example="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function delete($id)
    {
        $this->orderService->delete($id);

        return response()->json([
            "message" => "Order deleted successfully",
            "data" => null
        ], 204);
    }
}
