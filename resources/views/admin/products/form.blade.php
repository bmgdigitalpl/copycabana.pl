@extends('layouts.admin')
@section('title', 'Edytuj produkt | CopyCabana')
@section('content')
<a class="text-sm text-[#D51A70]" href="{{ route('admin.products.index') }}">← Produkty</a>
<h1 class="mt-2 text-3xl font-bold">Edytuj produkt</h1>
<p class="mt-2 text-slate-500">{{ $product->name }} <span class="text-slate-400">({{ $product->slug }})</span></p>

<form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-6 max-w-3xl rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @method('put')

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <label>Nazwa<input name="name" value="{{ old('name', $product->name) }}" class="mt-1 w-full rounded border p-2" required></label>
        <label>Cena bazowa / od (zł)<input name="starting_price" type="number" min="0" step="0.01" value="{{ old('starting_price', $product->starting_price) }}" class="mt-1 w-full rounded border p-2"></label>
        <label class="sm:col-span-2">Opis<textarea name="description" class="mt-1 w-full rounded border p-2" required>{{ old('description', $product->description) }}</textarea></label>
        <label>Kolejność<input name="sort_order" type="number" min="0" value="{{ old('sort_order', $product->sort_order) }}" class="mt-1 w-full rounded border p-2" required></label>
        <label class="self-end py-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Produkt aktywny</label>
    </div>
    @if(in_array($product->slug, ['praca-dyplomowa', 'druk']))
        <a href="{{ route('admin.printing.edit', $product->slug === 'praca-dyplomowa' ? 'thesis' : 'pdf') }}" class="mb-6 block text-pink-400">Edytuj opcje i cennik {{ $product->slug === 'praca-dyplomowa' ? 'pracy dyplomowej' : 'druku PDF' }} →</a>
    @endif
    <h2 class="mb-4 text-xl font-semibold">Edytuj zdjęcie produktu</h2>

    @if($product->imageUrl())
        <div class="mb-6 flex items-center gap-4 rounded-lg bg-slate-50 p-4">
            <img class="h-24 w-32 rounded object-cover" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
            <div>
                <p class="text-sm font-semibold">Aktualne zdjęcie</p>
                <p class="mt-1 break-all text-xs text-slate-500">{{ $product->image_path }}</p>
            </div>
        </div>
    @endif

    <label class="block text-sm font-semibold" for="catalog_image_path">Wybierz zdjęcie z folderu public/images/produkty</label>
    <select id="catalog_image_path" name="catalog_image_path" class="mt-2 w-full rounded border p-2">
        <option value="">Pozostaw bez zmiany</option>
        @foreach($catalogImages as $imagePath)
            <option value="{{ $imagePath }}" @selected(old('catalog_image_path') === $imagePath || (! old('catalog_image_path') && $product->image_path === $imagePath))>{{ basename($imagePath) }}</option>
        @endforeach
    </select>

    <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wide text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>
        albo wgraj nowe
        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <label class="block text-sm font-semibold" for="image">Nowe zdjęcie</label>
    <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full rounded border p-2 text-sm">
    <p class="mt-2 text-xs text-slate-500">JPG, JPEG, PNG lub WEBP, maksymalnie 5 MB. Nowe zdjęcie zastąpi wybrane powyżej.</p>

    @if($product->is_business_configurator)
        @include('admin.products.fields', ['fields' => $product->configuration['configurator']['fields'] ?? []])
    @endif
    <button class="mt-8 rounded bg-[#D51A70] px-5 py-3 font-semibold text-white">Zapisz produkt</button>
</form>
@endsection
