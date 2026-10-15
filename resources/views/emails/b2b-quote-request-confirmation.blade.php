<x-mail::message>
# Otrzymaliśmy Twoje zapytanie {{ $quoteRequest->reference }}

Dziękujemy. Pracownia sprawdzi zakres i przygotuje indywidualną wycenę.

<x-mail::panel>
Odpowiemy na adres {{ $quoteRequest->customer_email }}.

Liczba zgłoszonych pozycji: **{{ $quoteRequest->items->count() }}**
</x-mail::panel>
</x-mail::message>
