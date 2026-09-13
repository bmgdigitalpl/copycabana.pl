<!DOCTYPE html>
<html lang="pl" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Logowanie | CopyCabana')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-zinc-100 antialiased dark:bg-zinc-950">
        <main class="flex min-h-screen items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 flex justify-center text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                    CopyCabana
                </a>
                @yield('content')
            </div>
        </main>

        @fluxScripts
    </body>
</html>
