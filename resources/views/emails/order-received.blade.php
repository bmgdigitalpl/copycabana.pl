<h1>Dziękujemy za zamówienie {{ $order->number }}</h1>
<p>Otrzymaliśmy Twoje zamówienie. Skontaktujemy się z Tobą w sprawie potwierdzenia i finalnej wyceny.</p>
<p>Łączna kwota orientacyjna: {{ number_format((float) $order->total, 2, ',', ' ') }} zł</p>
