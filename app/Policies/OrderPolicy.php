<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->isCustomer() && $user->client_id === $order->client_id;
    }

    public function retryPayment(User $user, Order $order): bool
    {
        return $this->view($user, $order)
            && in_array($order->status->value, ['pending', 'cancelled'], true)
            && $order->payment_status !== 'paid';
    }
}
