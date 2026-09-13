<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuoteRequestRequest;
use App\Mail\B2bQuoteRequestStatusChanged;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteRequestController extends Controller
{
    public function index(Request $request): View
    {
        $quoteRequests = QuoteRequest::query()
            ->with('client')
            ->withCount('items')
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->string('q')->toString(), function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.quote-requests.index', [
            'quoteRequests' => $quoteRequests,
            'statuses' => QuoteRequestStatus::labels(),
        ]);
    }

    public function show(QuoteRequest $quoteRequest): View
    {
        return view('admin.quote-requests.show', [
            'quoteRequest' => $quoteRequest->load(['items.files', 'files', 'client', 'offers.creator', 'offers.order']),
            'statuses' => QuoteRequestStatus::labels(),
        ]);
    }

    public function update(UpdateQuoteRequestRequest $request, QuoteRequest $quoteRequest): RedirectResponse
    {
        $data = $request->validated();
        $status = QuoteRequestStatus::from($data['status']);
        $wasChanged = $quoteRequest->status !== $status;

        $quoteRequest->forceFill([
            'status' => $status,
            'admin_notes' => $data['note'] ?? $quoteRequest->admin_notes,
        ])->save();

        if ($wasChanged) {
            Mail::to($quoteRequest->customer_email)->queue(
                (new B2bQuoteRequestStatusChanged($quoteRequest->refresh()))->afterCommit(),
            );
        }

        return back()->with('status', 'Zapytanie zostało zaktualizowane.');
    }

    public function downloadFile(QuoteRequest $quoteRequest, QuoteRequestFile $file): StreamedResponse
    {
        abort_unless($file->quote_request_id === $quoteRequest->id && $file->status === 'attached', 404);

        return Storage::disk($file->disk)->download($file->path, $file->original_name, [
            'Content-Type' => $file->mime_type,
        ]);
    }
}
