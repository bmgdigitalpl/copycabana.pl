<h1>Aktualizacja zamówienia {{ $order->number }}</h1>
<p>Status zamówienia: <strong>{{ $order->status->label() }}</strong>.</p>
@if($order->trackingUrl())<p><a href="{{ $order->trackingUrl() }}">Śledź przesyłkę</a></p>@endif
