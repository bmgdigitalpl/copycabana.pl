@extends('layouts.admin')
@section('title', 'Zapytania B2B | CopyCabana')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm text-slate-500">Obsługa zapytań ofertowych</p>
        <h1 class="mt-1 text-3xl font-bold">Zapytania B2B</h1>
    </div>
</div>

<form class="mt-6 flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm">
    <input name="q" value="{{ request('q') }}" placeholder="Numer, firma lub e-mail" class="min-w-64 flex-1 rounded border p-2">
    <select name="status" class="rounded border p-2">
        <option value="">Wszystkie statusy</option>
        @foreach($statuses as $key => $label)
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="rounded bg-[#063A60] px-4 py-2 text-white">Filtruj</button>
</form>

<div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm">
    <table class="w-full min-w-[760px] text-left text-sm">
        <thead class="border-b bg-slate-50 text-xs uppercase text-slate-500">
            <tr><th class="px-4 py-3">Numer</th><th class="px-4 py-3">Firma</th><th class="px-4 py-3">Kontakt</th><th class="px-4 py-3">Pozycje</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Data</th></tr>
        </thead>
        <tbody>
            @forelse($quoteRequests as $quoteRequest)
                <tr class="border-b last:border-0 hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold"><a class="text-[#D51A70]" href="{{ route('admin.quote-requests.show', $quoteRequest) }}">{{ $quoteRequest->reference }}</a></td>
                    <td class="px-4 py-3">{{ $quoteRequest->company_name }}</td>
                    <td class="px-4 py-3"><div>{{ $quoteRequest->customer_name }}</div><div class="text-xs text-slate-500">{{ $quoteRequest->customer_email }}</div></td>
                    <td class="px-4 py-3">{{ $quoteRequest->items_count ?? $quoteRequest->items->count() }}</td>
                    <td class="px-4 py-3">{{ $statuses[$quoteRequest->status->value] ?? $quoteRequest->status->value }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $quoteRequest->created_at->format('d.m.Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Brak zapytań.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $quoteRequests->links() }}</div>
@endsection
