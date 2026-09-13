<h1>Nowe zapytanie o wycenę {{ $quoteRequest->reference }}</h1>
<p>Firma: {{ $quoteRequest->company_name }}</p>
<p>Kontakt: {{ $quoteRequest->customer_name }} &lt;{{ $quoteRequest->customer_email }}&gt;</p>
<p>Liczba pozycji: {{ $quoteRequest->items->count() }}</p>
<p>Pliki: {{ $quoteRequest->files->count() }}</p>
<p>Otwórz panel administratora, aby sprawdzić szczegóły i przygotować wycenę.</p>
