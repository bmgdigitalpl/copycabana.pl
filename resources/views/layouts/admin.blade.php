<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel CopyCabana')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<div class="min-h-screen lg:flex">
    <aside class="w-full bg-[#063A60] p-5 text-white lg:min-h-screen lg:w-64">
        <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-[#FFED00]">CopyCabana</a>
        <p class="mt-1 text-xs text-white/60">Panel administracyjny</p>
        <nav class="mt-8 grid gap-2 text-sm">
            <a class="rounded px-3 py-2 hover:bg-white/10" href="{{ route('admin.dashboard') }}">Pulpit</a>
            <a class="rounded px-3 py-2 hover:bg-white/10" href="{{ route('admin.orders.index') }}">Zamówienia</a>
            <a class="rounded px-3 py-2 hover:bg-white/10" href="{{ route('admin.quote-requests.index') }}">Zapytania B2B</a>
            <a class="rounded px-3 py-2 hover:bg-white/10" href="{{ route('admin.clients.index') }}">Klienci i RODO</a>
            <a class="rounded px-3 py-2 hover:bg-white/10" href="{{ route('admin.privacy.index') }}">Wnioski RODO</a>
            <a class="rounded px-3 py-2 hover:bg-white/10" href="{{ route('admin.products.index') }}">Produkty i zdjęcia</a>
            <a class="rounded px-3 py-2 hover:bg-white/10" href="{{ route('admin.options.index') }}">Opcje i ceny</a>
        </nav>
        <form class="mt-8" method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button class="rounded px-3 py-2 text-sm text-white/70 hover:bg-white/10">Wyloguj</button>
        </form>
    </aside>
    <main class="flex-1 p-5 sm:p-8">
        @if(session('status'))
            <div class="mb-5 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
