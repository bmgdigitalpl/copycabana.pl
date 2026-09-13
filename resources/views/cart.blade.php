@extends('layouts.main')

@section('title', 'Koszyk — CopyCabana')
@section('description', 'Sprawdź konfigurację, wybierz dostawę i przejdź do bezpiecznej płatności.')

@section('content')
<main class="cc-page" x-data="cartPage()" x-init="init()">
    <section class="cc-container py-12 sm:py-16">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-[#D51A70]">Zamówienie</p>
            <h1 class="mt-2 text-4xl font-bold text-[#063A60]">Twój koszyk</h1>
        </div>

        <template x-if="items.length === 0">
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
                <i class="fas fa-shopping-cart text-5xl text-slate-300" aria-hidden="true"></i>
                <h2 class="mt-4 text-xl font-bold text-[#063A60]">Koszyk jest pusty</h2>
                <p class="mt-2 text-slate-600">Dodaj produkt z katalogu, aby rozpocząć zamówienie.</p>
                <a href="{{ route('products.index') }}" class="btn-magenta mt-6 inline-block">Przeglądaj produkty</a>
            </div>
        </template>

        <template x-if="items.length > 0">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]">
                <section class="grid gap-4">
                    <template x-for="item in items" :key="item.id">
                        <article class="rounded-2xl bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="font-bold text-[#063A60]" x-text="item.productName"></h2>
                                    <p class="mt-1 text-sm text-slate-500" x-text="item.summary || 'Konfiguracja podstawowa'"></p>
                                </div>
                                <button type="button" @click="removeItem(item.id)" class="text-slate-400 hover:text-[#D51A70]" aria-label="Usuń produkt"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-4">
                                <div class="flex items-center rounded-lg border border-slate-200">
                                    <button type="button" @click="updateQty(item.id, item.quantity - 1)" class="px-3 py-2 text-slate-600">−</button>
                                    <span class="min-w-10 px-2 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                    <button type="button" @click="updateQty(item.id, item.quantity + 1)" class="px-3 py-2 text-slate-600">+</button>
                                </div>
                                <strong class="text-[#D51A70]" x-text="formatPrice(Number(item.price) * Number(item.quantity))"></strong>
                            </div>
                        </article>
                    </template>
                    <button type="button" @click="clearCart()" class="justify-self-start text-sm font-semibold text-slate-500 hover:text-[#D51A70]">Wyczyść koszyk</button>
                </section>

                <aside class="h-fit rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-[#063A60]">Dane do zamówienia</h2>
                    <div class="mt-5 grid gap-3">
                        <input x-model="customer.name" type="text" placeholder="Imię i nazwisko *" class="rounded-lg border border-slate-300 px-3 py-2">
                        <input x-model="customer.email" type="email" placeholder="E-mail *" class="rounded-lg border border-slate-300 px-3 py-2">
                        <input x-model="customer.phone" type="tel" placeholder="Telefon" class="rounded-lg border border-slate-300 px-3 py-2">
                        <input x-model="customer.company" type="text" placeholder="Firma" class="rounded-lg border border-slate-300 px-3 py-2">
                        <textarea x-model="customer.notes" rows="3" placeholder="Uwagi do zamówienia" class="rounded-lg border border-slate-300 px-3 py-2"></textarea>
                        <select x-model="customer.shipping_method" class="rounded-lg border border-slate-300 px-3 py-2">
                            <option value="pickup">Odbiór osobisty — bez dopłaty</option>
                            <option value="parcel">Paczkomat — 12,00 zł</option>
                            <option value="courier">Kurier — 18,00 zł</option>
                        </select>
                        <template x-if="customer.shipping_method !== 'pickup'">
                            <div class="grid gap-3 rounded-lg bg-slate-50 p-3">
                                <input x-model="customer.shipping_address.point_code" x-show="customer.shipping_method === 'parcel'" type="text" placeholder="Kod paczkomatu *" class="rounded-lg border border-slate-300 px-3 py-2">
                                <input x-model="customer.shipping_address.name" x-show="customer.shipping_method === 'parcel'" type="text" placeholder="Nazwa paczkomatu *" class="rounded-lg border border-slate-300 px-3 py-2">
                                <input x-model="customer.shipping_address.address" type="text" placeholder="Adres *" class="rounded-lg border border-slate-300 px-3 py-2">
                                <input x-model="customer.shipping_address.city" type="text" placeholder="Miasto *" class="rounded-lg border border-slate-300 px-3 py-2">
                                <input x-model="customer.shipping_address.post_code" type="text" placeholder="Kod pocztowy *" class="rounded-lg border border-slate-300 px-3 py-2">
                            </div>
                        </template>
                        <input x-model="customer.nip" type="text" placeholder="NIP do faktury" class="rounded-lg border border-slate-300 px-3 py-2">
                        <label class="flex gap-2 text-sm text-slate-600"><input x-model="customer.invoice_required" type="checkbox"> Proszę o fakturę VAT</label>
                        <label class="flex gap-2 text-sm text-slate-600"><input x-model="customer.privacy_policy_accepted" type="checkbox"> Akceptuję <a href="{{ route('privacy') }}" class="text-[#D51A70]" target="_blank" rel="noopener">politykę prywatności</a> *</label>
                    </div>

                    <div class="mt-6 grid gap-2 border-t border-slate-100 pt-5 text-sm">
                        <div class="flex justify-between"><span>Produkty</span><strong x-text="formatPrice(subtotal)"></strong></div>
                        <div class="flex justify-between"><span>Dostawa</span><strong x-text="formatPrice(shippingCost())"></strong></div>
                        <div class="mt-2 flex justify-between text-lg font-bold text-[#D51A70]"><span>Razem</span><strong x-text="formatPrice(subtotal + shippingCost())"></strong></div>
                    </div>
                    <p x-show="orderError" x-text="orderError" class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"></p>
                    <button type="button" @click="submitOrder()" :disabled="submitting" class="mt-5 w-full rounded-lg bg-[#7FBF45] px-4 py-3 font-semibold text-white disabled:opacity-60"><span x-text="submitting ? 'Przetwarzamy...' : 'Przejdź do płatności'"></span> <i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></button>
                </aside>
            </div>
        </template>
    </section>
</main>

<script src="{{ asset('js/cart.js') }}"></script>
<script>
    window.copyCabanaShipping = @js(config('business.shipping'));
    function cartPage() {
        return {
            items: [],
            subtotal: 0,
            submitting: false,
            orderError: '',
            idempotencyKey: window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`,
            customer: {
                name: '', email: '', phone: '', company: '', nip: '', notes: '',
                shipping_method: 'pickup', invoice_required: false, privacy_policy_accepted: false,
                shipping_address: { point_code: '', name: '', address: '', city: '', post_code: '' },
            },
            init() { this.loadCart(); window.addEventListener('cart-updated', () => this.loadCart()); },
            loadCart() { this.items = Cart.getItems(); this.subtotal = Cart.getTotal(); },
            updateQty(id, quantity) { if (quantity < 1) return; Cart.updateItem(id, { quantity: Math.min(100, quantity) }); this.loadCart(); },
            removeItem(id) { Cart.removeItem(id); this.loadCart(); },
            clearCart() { Cart.clear(); this.loadCart(); },
            shippingCost() { return Number(window.copyCabanaShipping[this.customer.shipping_method] || 0); },
            formatPrice(value) { return Number(value).toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' zł'; },
            async submitOrder() {
                if (!this.customer.name || !this.customer.email || !this.customer.privacy_policy_accepted) { this.orderError = 'Podaj dane kontaktowe i zaakceptuj politykę prywatności.'; return; }
                this.orderError = ''; this.submitting = true;
                try {
                    const response = await fetch('{{ url('/api/v1/orders') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'Idempotency-Key': this.idempotencyKey },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            customer: this.customer,
                            shipping_method: this.customer.shipping_method,
                            shipping_address: this.customer.shipping_method === 'pickup' ? null : this.customer.shipping_address,
                            invoice_required: this.customer.invoice_required,
                            privacy_policy_accepted: this.customer.privacy_policy_accepted,
                            items: this.items.map((item) => ({ product_slug: item.productId, quantity: item.quantity, option_value_ids: item.option_value_ids || [], configuration: item.options || {} })),
                        }),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Nie udało się utworzyć zamówienia.');
                    Cart.clear(); window.location.assign(payload.payment_url);
                } catch (error) { this.orderError = error.message; } finally { this.submitting = false; }
            },
        };
    }
</script>
@endsection
