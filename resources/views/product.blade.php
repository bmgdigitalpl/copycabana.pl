@extends('layouts.main')

@section('title', $product->name.' — CopyCabana')
@section('description', $product->description)

@section('content')
@php
    $productData = [
        'slug' => $product->slug,
        'name' => $product->name,
        'starting_price' => (float) ($product->starting_price ?? 0),
        'options' => $product->options->map(fn ($option): array => [
            'id' => $option->id,
            'name' => $option->name,
            'pricing_model' => $option->pricing_model,
            'is_required' => $option->is_required,
            'values' => $option->values->map(fn ($value): array => [
                'id' => $value->id,
                'label' => $value->label,
                'price_modifier' => (float) $value->price_modifier,
            ])->values()->all(),
        ])->values()->all(),
    ];
@endphp

<main class="cc-page">
    <section class="cc-container py-12 sm:py-16" x-data="catalogProduct({{ Js::from($productData) }})" x-init="init()">
        <div class="mb-6 text-sm text-slate-500"><a href="{{ route('products.index') }}" class="text-[#D51A70]">Produkty</a> <span class="mx-2">/</span> {{ $product->name }}</div>
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
            <div>
                <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-2xl bg-white p-8 shadow-sm">
                    @if($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-contain">
                    @else
                        <i class="fas fa-print text-6xl text-slate-300" aria-hidden="true"></i>
                    @endif
                </div>
                <div class="mt-6 rounded-2xl bg-[#063A60] p-6 text-white">
                    <p class="text-sm uppercase tracking-wide text-white/60">{{ $product->category }}</p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $product->name }}</h1>
                    <p class="mt-4 leading-7 text-white/80">{{ $product->description }}</p>
                </div>
            </div>

            <div class="h-fit rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#063A60]">Skonfiguruj zamówienie</h2>
                @if($product->options->isNotEmpty())
                    <div class="mt-6 grid gap-5">
                        @foreach($product->options as $option)
                            <label class="grid gap-2 text-sm font-semibold text-slate-700">
                                {{ $option->name }}
                                <select x-model="selected[{{ $option->id }}]" @change="calculate()" class="rounded-lg border border-slate-300 px-3 py-2 font-normal">
                                    @foreach($option->values as $value)
                                        <option value="{{ $value->id }}">{{ $value->label }} ({{ $value->price_modifier >= 0 ? '+' : '' }}{{ number_format((float) $value->price_modifier, 2, ',', ' ') }} zł)</option>
                                    @endforeach
                                </select>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm leading-6 text-slate-600">To produkt wyceniany od stałej ceny bazowej. Szczegóły możesz dopisać w uwagach po przejściu do koszyka.</p>
                @endif

                <label class="mt-5 grid gap-2 text-sm font-semibold text-slate-700">
                    Liczba sztuk
                    <input type="number" min="1" max="100" x-model.number="quantity" class="rounded-lg border border-slate-300 px-3 py-2 font-normal">
                </label>

                <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5">
                    <span class="text-slate-600">Cena orientacyjna</span>
                    <strong class="text-2xl text-[#D51A70]" x-text="formatPrice(price())"></strong>
                </div>
                <p class="mt-2 text-xs leading-5 text-slate-500">Cena końcowa jest ponownie obliczana po stronie serwera przed płatnością.</p>
                <button type="button" @click="addToCart()" class="mt-6 w-full rounded-lg bg-[#7FBF45] px-4 py-3 font-semibold text-white transition hover:bg-green-600">Dodaj do koszyka <i class="fas fa-cart-plus ml-2" aria-hidden="true"></i></button>
                <a href="{{ route('cart') }}" class="mt-3 block text-center text-sm font-semibold text-[#063A60] hover:underline">Zobacz koszyk</a>
                <p x-show="message" x-text="message" class="mt-4 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700"></p>
            </div>
        </div>
    </section>
</main>

<script src="{{ asset('js/cart.js') }}"></script>
<script>
    function catalogProduct(product) {
        return {
            product,
            selected: {},
            quantity: 1,
            message: '',
            init() {
                this.product.options.forEach((option) => {
                    if (option.values.length) this.selected[option.id] = String(option.values[0].id);
                });
            },
            price() {
                let price = Number(this.product.starting_price || 0);
                this.product.options.forEach((option) => {
                    const value = option.values.find((item) => String(item.id) === String(this.selected[option.id]));
                    if (!value) return;
                    price = option.pricing_model === 'percentage'
                        ? price + (price * Number(value.price_modifier) / 100)
                        : price + Number(value.price_modifier);
                });
                return Math.max(0, Math.round(price * 100) / 100);
            },
            formatPrice(value) {
                return Number(value).toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' zł';
            },
            addToCart() {
                const unitPrice = this.price();
                if (unitPrice <= 0) {
                    this.message = 'Ten produkt wymaga wyceny indywidualnej.';
                    return;
                }
                Cart.addItem({
                    productId: this.product.slug,
                    productName: this.product.name,
                    summary: this.product.options.map((option) => {
                        const value = option.values.find((item) => String(item.id) === String(this.selected[option.id]));
                        return value ? option.name + ': ' + value.label : null;
                    }).filter(Boolean).join(' · '),
                    options: { ...this.selected },
                    option_value_ids: Object.values(this.selected).map(Number),
                    price: unitPrice,
                    quantity: Math.max(1, Math.min(100, Number(this.quantity) || 1)),
                    image: @js($product->imageUrl()),
                });
                this.message = 'Dodano produkt do koszyka.';
            },
        };
    }
</script>
@endsection
