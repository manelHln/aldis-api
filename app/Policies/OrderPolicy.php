<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('orders:view.any') ||
            $user->can('orders:view.own') || $user->can('orders:view.assigned');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if($user->can('orders:view.assigned') && $user->id === $order->delivery_man_id){
            return true;
        }
        return $user->can('orders:view.any') ||
            ($user->can('orders:view.own') && $user->id === $order->user_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('orders:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can('orders:update.any') ||
            ($user->can('orders:update.own') && $user->id === $order->user_id);
    }

    public function updateStatus(User $user, Order $order){
        return $user->can('orders:update.any');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders:delete.any') ||
            ($user->can('orders:delete.own') && $user->id === $order->user_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->can('orders:restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->can('orders:delete.any');
    }
}
