<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivacyRequestController extends Controller
{
    public function index(): View
    {
        return view('admin.privacy.index', ['requests' => DataRequest::query()->with('client')->latest()->paginate(25)]);
    }

    public function update(Request $request, DataRequest $dataRequest): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:processing,completed,rejected']]);

        if ($data['status'] === 'completed' && $dataRequest->type === 'erasure') {
            $dataRequest->client->anonymize();
        }

        $dataRequest->update(['status' => $data['status'], 'completed_at' => $data['status'] === 'completed' ? now() : null]);
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => DataRequest::class,
            'auditable_id' => $dataRequest->id,
            'action' => 'privacy_request_'.$data['status'],
            'new_values' => ['status' => $data['status']],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', 'Wniosek RODO został zaktualizowany.');
    }
}
