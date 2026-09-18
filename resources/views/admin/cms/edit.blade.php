@extends('layouts.admin')

@section('title', $label.' CMS | CopyCabana')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm text-slate-500">Treści marketingowe strony</p>
        <h1 class="text-3xl font-bold">CMS: {{ $label }}</h1>
    </div>
    <a class="rounded bg-[#D51A70] px-4 py-2 font-semibold text-white" href="{{ route('home') }}" target="_blank" rel="noreferrer">Zobacz stronę</a>
</div>

<nav class="mt-6 flex flex-wrap gap-2" aria-label="Sekcje CMS">
    @foreach($sections as $key => $sectionLabel)
        <a href="{{ route('admin.cms.edit', $key) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $section === $key ? 'bg-[#D51A70] text-white' : 'bg-white text-slate-300 shadow-sm' }}">{{ $sectionLabel }}</a>
    @endforeach
</nav>

<form method="post" action="{{ route('admin.cms.update', $section) }}" class="mt-6 grid max-w-5xl gap-6 rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @method('put')

    @if($section === 'hero')
        <div class="grid gap-4 sm:grid-cols-2">
            <label>Nagłówek przed wyróżnieniem<input name="payload[title_before]" value="{{ old('payload.title_before', $payload['title_before'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Wyróżnienie nagłówka<input name="payload[title_emphasis]" value="{{ old('payload.title_emphasis', $payload['title_emphasis'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Przycisk główny<input name="payload[primary_label]" value="{{ old('payload.primary_label', $payload['primary_label'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Link przycisku głównego<input name="payload[primary_link]" value="{{ old('payload.primary_link', $payload['primary_link'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Ikona przycisku głównego<input name="payload[primary_icon]" value="{{ old('payload.primary_icon', $payload['primary_icon'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Przycisk drugi<input name="payload[secondary_label]" value="{{ old('payload.secondary_label', $payload['secondary_label'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Link przycisku drugiego<input name="payload[secondary_link]" value="{{ old('payload.secondary_link', $payload['secondary_link'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Ikona przycisku drugiego<input name="payload[secondary_icon]" value="{{ old('payload.secondary_icon', $payload['secondary_icon'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label class="sm:col-span-2">Opis semantyczny<textarea name="payload[semantic_intro]" rows="4" class="mt-1 w-full rounded border p-2">{{ old('payload.semantic_intro', $payload['semantic_intro'] ?? '') }}</textarea></label>
        </div>
    @elseif($section === 'hero-cards')
        <div class="grid gap-4">
            @foreach(($payload['items'] ?? []) as $index => $item)
                <section class="rounded-lg border p-4">
                    <h2 class="mb-3 font-semibold">Kafelek {{ $index + 1 }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label>Tytuł<input name="payload[items][{{ $index }}][title]" value="{{ old('payload.items.'.$index.'.title', $item['title'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
                        <label>Wariant koloru<input name="payload[items][{{ $index }}][variant]" value="{{ old('payload.items.'.$index.'.variant', $item['variant'] ?? '') }}" class="mt-1 w-full rounded border p-2" placeholder="cyan, magenta, yellow, black"></label>
                        <label class="sm:col-span-2">Opis<textarea name="payload[items][{{ $index }}][body]" rows="3" class="mt-1 w-full rounded border p-2">{{ old('payload.items.'.$index.'.body', $item['body'] ?? '') }}</textarea></label>
                        <label class="sm:col-span-2">Opcjonalny link<input name="payload[items][{{ $index }}][link]" value="{{ old('payload.items.'.$index.'.link', $item['link'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                    </div>
                </section>
            @endforeach
        </div>
    @elseif($section === 'why')
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="sm:col-span-2">Nagłówek<input name="payload[heading]" value="{{ old('payload.heading', $payload['heading'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label class="sm:col-span-2">Lead 1 (obsługuje <code>&lt;strong&gt;</code>/<code>&lt;em&gt;</code> do pogrubień/kursywy)<textarea name="payload[lead_1]" rows="3" class="mt-1 w-full rounded border p-2">{{ old('payload.lead_1', $payload['lead_1'] ?? '') }}</textarea></label>
            <label class="sm:col-span-2">Lead 2 (obsługuje <code>&lt;strong&gt;</code>/<code>&lt;em&gt;</code> do pogrubień/kursywy)<textarea name="payload[lead_2]" rows="4" class="mt-1 w-full rounded border p-2">{{ old('payload.lead_2', $payload['lead_2'] ?? '') }}</textarea></label>
            <label>CTA główne<input name="payload[cta_primary_label]" value="{{ old('payload.cta_primary_label', $payload['cta_primary_label'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Link CTA głównego<input name="payload[cta_primary_link]" value="{{ old('payload.cta_primary_link', $payload['cta_primary_link'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>CTA drugie<input name="payload[cta_secondary_label]" value="{{ old('payload.cta_secondary_label', $payload['cta_secondary_label'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Link CTA drugiego<input name="payload[cta_secondary_link]" value="{{ old('payload.cta_secondary_link', $payload['cta_secondary_link'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
        </div>
        <div class="grid gap-4">
            @foreach(($payload['items'] ?? []) as $index => $item)
                <section class="rounded-lg border p-4">
                    <h2 class="mb-3 font-semibold">Argument {{ $index + 1 }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label>Tytuł<input name="payload[items][{{ $index }}][title]" value="{{ old('payload.items.'.$index.'.title', $item['title'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
                        <label>Ikona<input name="payload[items][{{ $index }}][icon]" value="{{ old('payload.items.'.$index.'.icon', $item['icon'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label>Wariant<input name="payload[items][{{ $index }}][variant]" value="{{ old('payload.items.'.$index.'.variant', $item['variant'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label class="sm:col-span-2">Opis<textarea name="payload[items][{{ $index }}][body]" rows="3" class="mt-1 w-full rounded border p-2">{{ old('payload.items.'.$index.'.body', $item['body'] ?? '') }}</textarea></label>
                    </div>
                </section>
            @endforeach
        </div>
    @elseif($section === 'process')
        <label>Nagłówek sekcji<input name="payload[heading]" value="{{ old('payload.heading', $payload['heading'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
        <div class="grid gap-4">
            @foreach(($payload['items'] ?? []) as $index => $item)
                <section class="rounded-lg border p-4">
                    <h2 class="mb-3 font-semibold">Krok {{ $index + 1 }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label>Numer<input name="payload[items][{{ $index }}][number]" value="{{ old('payload.items.'.$index.'.number', $item['number'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
                        <label>Tytuł<input name="payload[items][{{ $index }}][title]" value="{{ old('payload.items.'.$index.'.title', $item['title'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
                        <label class="sm:col-span-2">Mały tekst<textarea name="payload[items][{{ $index }}][body]" rows="3" class="mt-1 w-full rounded border p-2">{{ old('payload.items.'.$index.'.body', $item['body'] ?? '') }}</textarea></label>
                    </div>
                </section>
            @endforeach
        </div>
    @elseif($section === 'services')
        <label>Nagłówek sekcji<input name="payload[heading]" value="{{ old('payload.heading', $payload['heading'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
        <div class="grid gap-4">
            @foreach(($payload['items'] ?? []) as $index => $item)
                <section class="rounded-lg border p-4">
                    <h2 class="mb-3 font-semibold">Kafelek usługi {{ $index + 1 }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label>Tytuł<input name="payload[items][{{ $index }}][title]" value="{{ old('payload.items.'.$index.'.title', $item['title'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
                        <label>Tytuł w bierniku (np. "o <em>pracę dyplomową</em>"), puste = jak tytuł<input name="payload[items][{{ $index }}][accusative]" value="{{ old('payload.items.'.$index.'.accusative', $item['accusative'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label>Alt obrazka<input name="payload[items][{{ $index }}][alt]" value="{{ old('payload.items.'.$index.'.alt', $item['alt'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label>Obrazek<input name="payload[items][{{ $index }}][image]" value="{{ old('payload.items.'.$index.'.image', $item['image'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label>Ikona<input name="payload[items][{{ $index }}][icon]" value="{{ old('payload.items.'.$index.'.icon', $item['icon'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label class="sm:col-span-2">Opcjonalny link całego kafelka<input name="payload[items][{{ $index }}][href]" value="{{ old('payload.items.'.$index.'.href', $item['href'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label>Sugerowane pytanie 1<input name="payload[items][{{ $index }}][questions][0]" value="{{ old('payload.items.'.$index.'.questions.0', $item['questions'][0] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                        <label>Sugerowane pytanie 2<input name="payload[items][{{ $index }}][questions][1]" value="{{ old('payload.items.'.$index.'.questions.1', $item['questions'][1] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
                    </div>
                </section>
            @endforeach
        </div>
    @elseif($section === 'faq')
        <div class="grid gap-4 sm:grid-cols-2">
            <label>Etykieta<input name="payload[label]" value="{{ old('payload.label', $payload['label'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Nagłówek<input name="payload[heading]" value="{{ old('payload.heading', $payload['heading'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label class="sm:col-span-2">Lead<textarea name="payload[lead]" rows="3" class="mt-1 w-full rounded border p-2">{{ old('payload.lead', $payload['lead'] ?? '') }}</textarea></label>
        </div>
        <div class="grid gap-4">
            @foreach(($payload['items'] ?? []) as $index => $item)
                <section class="rounded-lg border p-4">
                    <h2 class="mb-3 font-semibold">Pytanie {{ $index + 1 }}</h2>
                    <label>Pytanie<input name="payload[items][{{ $index }}][q]" value="{{ old('payload.items.'.$index.'.q', $item['q'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
                    <label class="mt-4 block">Odpowiedź<textarea name="payload[items][{{ $index }}][a]" rows="3" class="mt-1 w-full rounded border p-2">{{ old('payload.items.'.$index.'.a', $item['a'] ?? '') }}</textarea></label>
                </section>
            @endforeach
        </div>
    @elseif($section === 'contact')
        <div class="grid gap-4 sm:grid-cols-2">
            <label>Overline<input name="payload[overline]" value="{{ old('payload.overline', $payload['overline'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Nagłówek przed wyróżnieniem<input name="payload[title_before]" value="{{ old('payload.title_before', $payload['title_before'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Wyróżnienie nagłówka<input name="payload[title_emphasis]" value="{{ old('payload.title_emphasis', $payload['title_emphasis'] ?? '') }}" class="mt-1 w-full rounded border p-2" required></label>
            <label>Telefon - etykieta<input name="payload[phone_label]" value="{{ old('payload.phone_label', $payload['phone_label'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Telefon - link<input name="payload[phone_link]" value="{{ old('payload.phone_link', $payload['phone_link'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Email - etykieta<input name="payload[email_label]" value="{{ old('payload.email_label', $payload['email_label'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label>Email - link<input name="payload[email_link]" value="{{ old('payload.email_link', $payload['email_link'] ?? '') }}" class="mt-1 w-full rounded border p-2"></label>
            <label class="sm:col-span-2">Opis<textarea name="payload[body]" rows="4" class="mt-1 w-full rounded border p-2">{{ old('payload.body', $payload['body'] ?? '') }}</textarea></label>
        </div>
    @endif

    <button class="w-fit rounded bg-[#D51A70] px-5 py-3 font-semibold text-white">Zapisz sekcję</button>
</form>
@endsection
