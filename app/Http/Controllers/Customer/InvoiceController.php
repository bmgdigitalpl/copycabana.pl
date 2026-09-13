<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice): View
    {
        $invoice = Invoice::query()
            ->whereKey($invoice->id)
            ->whereHas('order', fn ($query) => $query->whereBelongsTo($request->user()->client))
            ->with('order')
            ->firstOrFail();
        Gate::authorize('view', $invoice);

        return view('customer.invoices.show', compact('invoice'));
    }
}
