<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $client = $request->user()->loadMissing('client')->client;

        return view('customer.dashboard', [
            'client' => $client,
            'latestOrder' => $client->orders()->latest()->first(),
            'openOffers' => $client->quoteRequests()
                ->whereHas('offers', fn ($query) => $query->where('status', 'sent'))
                ->with('latestOffer')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
