@extends('layouts.admin')
@section('title', 'Edytuj zdjęcie produktu | CopyCabana')
@section('content')
<a class="text-sm text-[#D51A70]" href="{{ route('admin.products.index') }}">← Produkty i zdjęcia</a>
<h1 class="mt-2 text-3xl font-bold">Edytuj zdjęcie produktu</h1>
<p class="mt-2 text-slate-500">{{ $product->name }} <span class="text-slate-400">({{ $product->slug }})</span></p>

<form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-6 max-w-3xl rounded-xl bg-white p-6 shadow-sm">
    @csrf
    @method('put')

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

    <button class="mt-8 rounded bg-[#D51A70] px-5 py-3 font-semibold text-white">Zapisz zdjęcie</button>
</form>
@endsection
