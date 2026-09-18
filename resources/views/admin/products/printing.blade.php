@extends('layouts.admin')
@section('title', $type === 'thesis' ? 'Prace Dyplomowe' : 'Druk PDF')
@section('content')
<h1 class="text-3xl font-bold">{{ $type === 'thesis' ? 'Prace Dyplomowe' : 'Druk PDF' }}</h1>
<p class="mt-2 text-slate-400">Opcje i ceny konfiguratora. Zmiany obowiązują dla nowych wycen i zamówień.</p>
<a href="{{ route('admin.products.edit', $product) }}" class="mt-3 inline-block text-pink-400">Edytuj zdjęcie i dane produktu →</a>
<form method="post" action="{{ route('admin.printing.update', $type) }}" enctype="multipart/form-data" class="mt-6 grid max-w-5xl gap-6">
    @csrf @method('put')
    <section class="grid gap-4 rounded-xl bg-white p-6 sm:grid-cols-3">
        @foreach(['bw' => 'Strona czarno-biała (zł)', 'color' => 'Strona kolorowa (zł)'] as $key => $label)
            <label>{{ $label }}<input class="mt-2 w-full rounded border p-2" type="number" min="0" max="100000" step="0.01" name="page_prices[{{ $key }}]" value="{{ old('page_prices.'.$key, $printing['page_prices'][$key] ?? '') }}" required></label>
        @endforeach
        <label>Maks. egzemplarzy<input class="mt-2 w-full rounded border p-2" type="number" min="1" max="1000" name="max_copies" value="{{ old('max_copies', $printing['max_copies'] ?? '') }}" required></label>
    </section>
    @foreach($groups as $group => $label)
        <section class="rounded-xl bg-white p-6" x-data="{ rows: {{ Illuminate\Support\Js::from(old($group, $printing[$group])) }} }">
            <h2 class="text-xl font-bold">{{ $label }}</h2>
            @if($group === 'covers')<p class="mt-2 text-sm text-slate-400">Kody zachowań: none — bez napisu, standard — tytuł i uczelnia, custom — własny tekst.</p>@endif
            <div class="mt-4 grid gap-4">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid gap-3 rounded border p-4 sm:grid-cols-2">
                        <label class="text-sm">Kod<input class="mt-1 w-full rounded border p-2" :name="'{{ $group }}[' + index + '][key]'" x-model="row.key" pattern="[a-zA-Z0-9_-]+" required></label>
                        <label class="text-sm">Nazwa<input class="mt-1 w-full rounded border p-2" :name="'{{ $group }}[' + index + '][label]'" x-model="row.label" maxlength="255" required></label>
                        @if(in_array($group, ['bindings', 'covers', 'finishes']))
                            <label class="text-sm">Cena za egzemplarz (zł)<input class="mt-1 w-full rounded border p-2" type="number" min="0" step="0.01" :name="'{{ $group }}[' + index + '][price]'" x-model="row.price" required></label>
                            <label class="text-sm">Opis<input class="mt-1 w-full rounded border p-2" :name="'{{ $group }}[' + index + '][hint]'" x-model="row.hint" maxlength="255"></label>
                        @endif
                        @if(in_array($group, ['imprint_colors', 'cover_colors']))
                            <label class="text-sm">Kolor<input class="mt-1 block" type="color" :name="'{{ $group }}[' + index + '][hex]'" x-model="row.hex" required></label>
                        @endif
                        @if($group === 'cover_colors')
                            <div class="text-sm sm:col-span-2">
                                <template x-if="row.photo_url">
                                    <img :src="row.photo_url" class="mb-2 h-20 w-20 rounded object-cover" alt="">
                                </template>
                                <label>Zdjęcie czystej okładki<input class="mt-1 block w-full rounded border p-2 text-sm" type="file" accept=".jpg,.jpeg,.png,.webp" :name="'{{ $group }}[' + index + '][photo]'"></label>
                                <input type="hidden" :name="'{{ $group }}[' + index + '][existing_photo]'" x-model="row.photo">
                                <p class="mt-1 text-xs text-slate-400">JPG, PNG lub WEBP, maks. 5 MB. Po wgraniu, po wybraniu tego koloru w konfiguratorze pokaże się całe to zdjęcie zamiast generowanego podglądu.</p>
                            </div>
                        @endif
                        <button type="button" class="text-left text-sm text-red-400" @click="rows.splice(index, 1)" :disabled="rows.length <= 1">Usuń wariant</button>
                    </div>
                </template>
            </div>
            <button type="button" class="mt-4 rounded border px-4 py-2" @click="rows.push({ key: '', label: '', price: 0, hint: '', hex: '#000000' })">+ Dodaj wariant</button>
        </section>
    @endforeach
    @if($type === 'thesis')
        @foreach(['cd' => 'Nagranie na CD — jednorazowo', 'spine_engraving' => 'Grawerowanie — za egzemplarz'] as $key => $label)
            <section class="rounded-xl bg-white p-6">
                <h2 class="text-xl font-bold">{{ $label }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label>Nazwa<input class="mt-1 w-full rounded border p-2" name="{{ $key }}[label]" value="{{ old($key.'.label', $printing[$key]['label'] ?? '') }}" required></label>
                    <label>Cena (zł)<input class="mt-1 w-full rounded border p-2" type="number" min="0" step="0.01" name="{{ $key }}[price]" value="{{ old($key.'.price', $printing[$key]['price'] ?? '') }}" required></label>
                </div>
            </section>
        @endforeach
    @endif
    <button class="rounded bg-[#D51A70] px-5 py-3 font-semibold text-white">Zapisz konfigurator</button>
</form>
@endsection
