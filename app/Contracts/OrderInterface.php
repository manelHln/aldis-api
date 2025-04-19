<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface OrderInterface
{
    //
    public function createNew(array $data);
    public function delete(string $id);
    public function updateStatus(string $id, string $status);
    public function assignDeliveryMan(string $order_id, string $delivery_man_id);
    public function getAll(Request $request);
    public function getById($id);
    public function getUserOrders(string $user_id, Request $request);
    public function getCurrentUserOrders(Request $request);
}
