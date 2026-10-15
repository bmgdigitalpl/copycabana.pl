<x-mail::message>
# Oferta {{ $quoteRequest->reference }}

Przygotowaliśmy indywidualną ofertę dla {{ $quoteRequest->company_name }}.

<x-mail::panel>
Kwota brutto: **{{ number_format((float) $offer->total, 2, ',', ' ') }} {{ $offer->currency }}**

Oferta jest ważna do {{ $offer->valid_until->format('d.m.Y') }}.
</x-mail::panel>

@if($offer->notes)
{{ $offer->notes }}

@endif
<x-mail::button :url="route('quote-offers.show', ['token' => $token])">
Sprawdź ofertę i zaakceptuj
</x-mail::button>
</x-mail::message>
