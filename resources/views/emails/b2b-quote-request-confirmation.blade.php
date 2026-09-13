<h1>Otrzymaliśmy Twoje zapytanie {{ $quoteRequest->reference }}</h1>
<p>Dziękujemy. Pracownia sprawdzi zakres i przygotuje indywidualną wycenę.</p>
<p>Odpowiemy na adres {{ $quoteRequest->customer_email }}.</p>
<p>Liczba zgłoszonych pozycji: {{ $quoteRequest->items->count() }}.</p>
