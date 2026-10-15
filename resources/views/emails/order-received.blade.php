<x-mail::message>
# Dziękujemy za zamówienie {{ $order->number }}

Otrzymaliśmy Twoje zamówienie. Skontaktujemy się z Tobą w sprawie potwierdzenia i finalnej wyceny.

<x-mail::panel>
Łączna kwota orientacyjna: **{{ number_format((float) $order->total, 2, ',', ' ') }} zł**
</x-mail::panel>
</x-mail::message>
