<?php

namespace App\Services;

use App\Exceptions\AldisModelNotFoundException;
use Illuminate\Http\Request;
use App\Contracts\OrderInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserLocationInterface;
use App\Helpers\PaginationHelper;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ValueError;
use Illuminate\Support\Str;

class OrderService implements OrderInterface
{
    public function __construct(protected UserInterface $userService, protected UserLocationInterface $userLocationService){}

    public function createNew(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = new Order();
            $location = $this->userLocationService->getUserLocationById($data["location_id"]);
            $order->user_id = $data['user_id'];
            $order->status = OrderStatus::PENDING;
            $order->location_id = $location->id;
            $order->total_price = 0;
            $order->save();

            $total = 0;

            foreach ($data['products'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];
                $unitPrice = $product->price;

                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $total += $quantity * $unitPrice;
            }

            $order->update(['total_price' => $total]);

            return Order::with(['products'])->find($order->id);
        });
    }
    public function delete(string $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return true;
    }

    public function getAll(Request $request)
    {
        $query = Order::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->has('size')) {
            $size = $request->input('size');
            $result = $query->with(['products.product'])->cursorPaginate($size);
            return PaginationHelper::cursorPaginated($result);
        }

        return $query->get();
    }
    public function getById($id)
    {
        $order = Order::with(['products'])->findOrFail($id);
        return $order;
    }

    public function getUserOrders(string $user_id, Request $request)
    {
        $user = $this->userService->getById($user_id);

        $query = Order::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }
        if ($request->has('size')) {
            $size = $request->input('size');
            $result = $query->where('user_id', $user->id)->with(['products.product'])->cursorPaginate($size);
            return PaginationHelper::cursorPaginated($result);
        }

        $orders = $query->with(['products'])->get();

        return $orders;
    }

    public function getCurrentUserOrders(Request $request){
        $user = $request->user();
        if(!$user){
            throw new UnauthorizedException("User not authenticated");
        }
        $query = Order::query();
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }
        if ($request->has('size')) {
            $size = $request->input('size');
            $result = $query->where('user_id', $user->id)->with(['products.product'])->cursorPaginate($size);
            return PaginationHelper::cursorPaginated($result);
        }

        $orders = $query->with(['products.product'])->get();

        return $orders;
    }

    public function updateSTatus($id, $status){
        $order = Order::find($id);

        if(!$order){
            throw new AldisModelNotFoundException("Order with id $id not found");
        }

        try{
            $order_status = OrderStatus::from(value: Str::lower($status));

            $order->update(["status" => $order_status->value]);
            return $order;
        }catch(ValueError $e){
            throw new BadRequestHttpException("Cannot update order status to: $status. Valid statuses are: pending, completed or cancelled");
        }
    }

    public function assignDeliveryMan(string $order_id, string $delivery_man_id){
        $order = Order::find($order_id);
        if(!$order){
            throw new AldisModelNotFoundException("Order with ID $order_id not found");
        }
        $delivery_man = $this->userService->getById($delivery_man_id);
        if(!$delivery_man){
            throw new AldisModelNotFoundException("User with ID $delivery_man_id not found");
        }

        if(!$delivery_man->hasRole('delivery')){
            throw new BadRequestHttpException("User with id $delivery_man_id is not a delivery man");
        }

        $order->update(["delivery_man_id" => $delivery_man_id]);

        return $order->load("deliveryMan");
    }
}
