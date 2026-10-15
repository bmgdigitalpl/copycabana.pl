<x-mail::message>
# Aktualizacja zamówienia {{ $order->number }}

Status zamówienia: **{{ $order->status->label() }}**.

@if($order->trackingUrl())
<x-mail::button :url="$order->trackingUrl()">
Śledź przesyłkę
</x-mail::button>
@endif
</x-mail::message>
