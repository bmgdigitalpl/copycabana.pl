<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\DataRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()->withCount('orders')
            ->when($request->string('q')->toString(), function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            })->latest()->paginate(25)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function show(Client $client): View
    {
        return view('admin.clients.show', ['client' => $client->load(['orders', 'dataRequests'])]);
    }

    public function export(Client $client): JsonResponse
    {
        AuditLog::create(['user_id' => auth()->id(), 'auditable_type' => Client::class, 'auditable_id' => $client->id, 'action' => 'export', 'ip_address' => request()->ip()]);

        return response()->json([
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'company' => $client->company,
            'orders' => $client->orders()->with('items')->get(),
        ])->header('Content-Disposition', 'attachment; filename="client-'.$client->id.'-data.json"');
    }

    public function requestDeletion(Client $client): RedirectResponse
    {
        $client->update(['deletion_requested_at' => now()]);
        $request = $client->dataRequests()->create(['type' => 'erasure', 'status' => 'pending', 'requested_at' => now()]);
        AuditLog::create(['user_id' => auth()->id(), 'auditable_type' => DataRequest::class, 'auditable_id' => $request->id, 'action' => 'deletion_requested', 'ip_address' => request()->ip()]);

        return back()->with('status', 'Wniosek o usunięcie danych został zarejestrowany. Zweryfikuj obowiązki księgowe przed anonimizacją.');
    }

    public function anonymize(Client $client): RedirectResponse
    {
        $client->anonymize();
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'action' => 'anonymized',
            'new_values' => ['anonymized' => true],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('status', 'Dane klienta zostały zanonimizowane.');
    }
}
