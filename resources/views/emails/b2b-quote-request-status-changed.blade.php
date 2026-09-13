<h1>Aktualizacja zapytania {{ $quoteRequest->reference }}</h1>
<p>Status zapytania: <strong>{{ \App\Enums\QuoteRequestStatus::labels()[$quoteRequest->status->value] ?? $quoteRequest->status->value }}</strong></p>
<p>W razie pytań odpowiedz na tę wiadomość lub skontaktuj się z CopyCabana.</p>
