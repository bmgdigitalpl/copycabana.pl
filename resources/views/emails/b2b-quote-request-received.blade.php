<x-mail::message>
# Nowe zapytanie o wycenę {{ $quoteRequest->reference }}

<x-mail::panel>
Firma: **{{ $quoteRequest->company_name }}**

Kontakt: {{ $quoteRequest->customer_name }} <{{ $quoteRequest->customer_email }}>

Liczba pozycji: {{ $quoteRequest->items->count() }}

Pliki: {{ $quoteRequest->files->count() }}
</x-mail::panel>

Otwórz panel administratora, aby sprawdzić szczegóły i przygotować wycenę.
</x-mail::message>
