@extends('layouts.admin')
@section('title', $quoteRequest->reference.' | CopyCabana')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div><a class="text-sm text-[#D51A70]" href="{{ route('admin.quote-requests.index') }}">← Zapytania B2B</a><h1 class="mt-2 text-3xl font-bold">{{ $quoteRequest->reference }}</h1><p class="text-sm text-slate-500">{{ $quoteRequest->created_at->format('d.m.Y H:i') }}</p></div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[2fr_1fr]">
    <section class="rounded-xl bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Pozycje do wyceny</h2>
        <div class="mt-4 grid gap-4">
            @foreach($quoteRequest->items as $item)
                <article class="border-b pb-4 last:border-0">
                    <div class="flex justify-between gap-4"><div><p class="font-medium">{{ $item->product_name }}</p><p class="text-sm text-slate-500">Ilość: {{ $item->quantity }}{{ $item->help_wanted ? ' · projekt po stronie pracowni' : '' }}</p></div><strong>{{ $item->files->count() }} plik(i)</strong></div>
                    @if($item->configuration)
                        <dl class="mt-2 grid gap-1 text-sm text-slate-400">
                            @foreach($item->configuration as $key => $value)
                                <div><dt class="inline font-medium">{{ is_array($value) ? ($value['label'] ?? $key) : $key }}:</dt>
                                    <dd class="inline">{{ is_array($value) ? ($value['display'] ?? json_encode($value, JSON_UNESCAPED_UNICODE)) : $value }}
                                        @if(is_array($value) && isset($value['price']))
                                            <span> · {{ number_format((float) $value['price'], 2, ',', ' ') }} zł{{ ($value['pricing_unit'] ?? '') === 'unit' ? ' / jednostkę' : ' dopłaty' }}</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </article>
            @endforeach
        </div>
        @if($quoteRequest->files->isNotEmpty())
            <div class="mt-6 border-t pt-4"><h3 class="font-semibold">Załączniki</h3><div class="mt-3 grid gap-2 text-sm">@foreach($quoteRequest->files as $file)<a class="flex justify-between rounded border px-3 py-2 text-[#063A60] hover:bg-slate-50" href="{{ route('admin.quote-requests.files.download', [$quoteRequest, $file]) }}"><span>{{ $file->original_name }} ({{ number_format($file->size / 1024 / 1024, 2, ',', ' ') }} MB)</span><span class="font-semibold">Pobierz</span></a>@endforeach</div></div>
        @endif
        @if($quoteRequest->notes)<div class="mt-6 border-t pt-4"><h3 class="font-semibold">Opis i uwagi klienta</h3><p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $quoteRequest->notes }}</p></div>@endif
    </section>

    <aside class="grid content-start gap-6">
        <section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-semibold">Aktualizacja</h2><form method="post" action="{{ route('admin.quote-requests.update', $quoteRequest) }}" class="mt-4 grid gap-3">@csrf @method('put')<label class="text-sm">Status<select name="status" class="mt-1 w-full rounded border p-2">@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected($quoteRequest->status->value === $key)>{{ $label }}</option>@endforeach</select></label><label class="text-sm">Notatka wewnętrzna<textarea name="note" rows="5" class="mt-1 w-full rounded border p-2">{{ old('note', $quoteRequest->admin_notes) }}</textarea></label><button class="rounded bg-[#D51A70] px-4 py-2 font-semibold text-white">Zapisz</button></form></section>
        <section class="rounded-xl bg-white p-5 shadow-sm"><h2 class="font-semibold">Przygotuj ofertę</h2><form method="post" action="{{ route('admin.quote-offers.store', $quoteRequest) }}" class="mt-4 grid gap-3">@csrf<label class="text-sm">Wartość usług<input name="subtotal" type="number" step="0.01" min="0" value="{{ old('subtotal') }}" class="mt-1 w-full rounded border p-2" required></label><label class="text-sm">Dostawa<input name="shipping_total" type="number" step="0.01" min="0" value="{{ old('shipping_total', 0) }}" class="mt-1 w-full rounded border p-2" required></label><label class="text-sm">Razem do zapłaty<input name="total" type="number" step="0.01" min="0" value="{{ old('total') }}" class="mt-1 w-full rounded border p-2" required></label><label class="text-sm">Waluta<input name="currency" value="{{ old('currency', config('business.currency')) }}" maxlength="3" class="mt-1 w-full rounded border p-2" required></label><label class="text-sm">Ważna do<input name="valid_until" type="date" value="{{ old('valid_until', now()->addDays(14)->toDateString()) }}" min="{{ today()->toDateString() }}" class="mt-1 w-full rounded border p-2" required></label><label class="text-sm">Komentarz dla klienta<textarea name="notes" rows="4" class="mt-1 w-full rounded border p-2">{{ old('notes') }}</textarea></label><button class="rounded bg-[#D51A70] px-4 py-2 font-semibold text-white">Wyślij ofertę</button></form></section>
        <section class="rounded-xl bg-white p-5 text-sm shadow-sm"><h2 class="font-semibold">Dane firmy</h2><dl class="mt-3 grid gap-2"><div><dt class="text-slate-500">Firma</dt><dd>{{ $quoteRequest->company_name }}</dd></div><div><dt class="text-slate-500">Kontakt</dt><dd>{{ $quoteRequest->customer_name }}</dd></div><div><dt class="text-slate-500">E-mail</dt><dd>{{ $quoteRequest->customer_email }}</dd></div><div><dt class="text-slate-500">Telefon</dt><dd>{{ $quoteRequest->customer_phone ?: '—' }}</dd></div><div><dt class="text-slate-500">NIP</dt><dd>{{ $quoteRequest->nip ?: '—' }}</dd></div><div><dt class="text-slate-500">Dostawa</dt><dd>{{ $quoteRequest->shipping_method ?: '—' }}</dd></div></dl></section>
        @if($quoteRequest->offers->isNotEmpty())<section class="rounded-xl bg-white p-5 text-sm shadow-sm"><h2 class="font-semibold">Historia ofert</h2><div class="mt-3 grid gap-3">@foreach($quoteRequest->offers->sortByDesc('version') as $offer)<div class="rounded border p-3"><div class="flex justify-between"><strong>Wersja {{ $offer->version }}</strong><span>{{ $offer->status->value }}</span></div><p class="mt-1">{{ number_format((float) $offer->total, 2, ',', ' ') }} {{ $offer->currency }} · ważna do {{ $offer->valid_until->format('d.m.Y') }}</p></div>@endforeach</div></section>@endif
    </aside>
</div>
@endsection
