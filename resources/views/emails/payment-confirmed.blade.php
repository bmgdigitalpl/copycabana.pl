<h1>Płatność za zamówienie {{ $order->number }} została potwierdzona</h1>
<p>Otrzymaliśmy płatność BLIK przez PayU.</p>
<p>Kwota: {{ number_format((float) $order->total, 2, ',', ' ') }} zł</p>
<p>Przystąpimy teraz do realizacji Twojego zamówienia.</p>
