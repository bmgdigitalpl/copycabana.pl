<h1>Nowa wiadomość z formularza kontaktowego</h1>
<p>Kontakt: {{ $name }} &lt;{{ $email }}&gt;</p>
@if ($phone)
<p>Telefon: {{ $phone }}</p>
@endif
<h2>Wiadomość</h2>
<p>{!! nl2br(e($body)) !!}</p>
