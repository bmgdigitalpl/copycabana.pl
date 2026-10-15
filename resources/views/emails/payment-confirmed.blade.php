<x-mail::message>
# Płatność za zamówienie {{ $order->number }} została potwierdzona

Otrzymaliśmy płatność BLIK przez PayU.

<x-mail::panel>
Kwota: **{{ number_format((float) $order->total, 2, ',', ' ') }} zł**
</x-mail::panel>

Przystąpimy teraz do realizacji Twojego zamówienia.
</x-mail::message>
