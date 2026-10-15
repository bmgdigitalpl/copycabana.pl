<x-mail::message>
# Aktualizacja zapytania {{ $quoteRequest->reference }}

Status zapytania: **{{ \App\Enums\QuoteRequestStatus::labels()[$quoteRequest->status->value] ?? $quoteRequest->status->value }}**

W razie pytań odpowiedz na tę wiadomość lub skontaktuj się z CopyCabana.
</x-mail::message>
