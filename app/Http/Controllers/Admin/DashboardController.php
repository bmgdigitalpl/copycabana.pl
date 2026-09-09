<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $ordersByStatus = Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $stats = [
            'orders' => Order::query()->count(),
            'revenue' => Order::query()->whereNot('status', OrderStatus::Cancelled->value)->sum('total'),
            'clients' => Client::query()->count(),
            'awaiting' => Order::query()->whereIn('status', [OrderStatus::Pending->value, OrderStatus::PaymentAwaited->value])->count(),
        ];
        $recentOrders = Order::query()->with('client')->latest()->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'ordersByStatus', 'recentOrders'));
    }
}
