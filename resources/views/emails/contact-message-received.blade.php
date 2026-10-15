<x-mail::message>
# Nowa wiadomość z formularza kontaktowego

Kontakt: {{ $name }} <{{ $email }}>
@if ($phone)

Telefon: {{ $phone }}
@endif

## Wiadomość

<x-mail::panel>
{!! str_replace("\n", "  \n", e(str_replace("\r\n", "\n", $body))) !!}
</x-mail::panel>
</x-mail::message>
