@extends('layouts.admin')
@section('title', 'Produkty | CopyCabana')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm text-slate-500">Katalog i konfigurator</p>
        <h1 class="text-3xl font-bold">Produkty</h1>
    </div>
    <a class="rounded bg-[#D51A70] px-4 py-2 font-semibold text-white" href="{{ route('services.business') }}" target="_blank" rel="noreferrer">Zobacz stronę dla firm</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($products as $product)
        <article class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="flex h-48 items-center justify-center bg-slate-100">
                @if($product->imageUrl())
                    <img class="h-full w-full object-cover" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy">
                @else
                    <span class="text-sm text-slate-400">Brak zdjęcia</span>
                @endif
            </div>
            <div class="p-5">
                <div class="flex justify-between gap-3">
                    <h2 class="font-semibold">{{ $product->name }}</h2>
                    <span class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $product->is_active ? 'Aktywny' : 'Nieaktywny' }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-500">{{ $product->slug }} · {{ $product->category }}</p>
                <p class="mt-3 truncate text-xs text-slate-400">{{ $product->image_path ?: 'Nie ustawiono' }}</p>
                <a class="mt-4 inline-block text-sm font-semibold text-[#D51A70]" href="{{ route('admin.products.edit', $product) }}">Edytuj zdjęcie, opcje i ceny</a>
            </div>
        </article>
    @empty
        <div class="rounded-xl bg-white p-8 text-slate-500 shadow-sm">Nie dodano jeszcze produktów.</div>
    @endforelse
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
