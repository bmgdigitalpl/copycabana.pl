<!DOCTYPE html>
<html lang="pl" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Panel CopyCabana')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .admin-shell .bg-white,
            .admin-shell .bg-gray-50,
            .admin-shell .bg-slate-50 {
                background-color: rgb(24 24 27) !important;
            }

            .admin-shell input,
            .admin-shell select,
            .admin-shell textarea {
                background-color: rgb(39 39 42) !important;
                border-color: rgb(82 82 91) !important;
                color: rgb(244 244 245) !important;
            }

            .admin-shell input::placeholder,
            .admin-shell textarea::placeholder {
                color: rgb(161 161 170) !important;
            }

            .admin-shell .border,
            .admin-shell .border-b,
            .admin-shell .border-t,
            .admin-shell .border-l,
            .admin-shell .border-r,
            .admin-shell .border-l-2 {
                border-color: rgb(63 63 70) !important;
            }

            .admin-shell .shadow,
            .admin-shell .shadow-sm {
                box-shadow: 0 1px 2px rgb(0 0 0 / 0.35), 0 0 0 1px rgb(63 63 70 / 0.8) !important;
            }
        </style>
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-900 antialiased dark:bg-zinc-800 dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <flux:sidebar.brand href="{{ route('dashboard') }}" name="CopyCabana" />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Panel" class="grid">
                    <flux:sidebar.item icon="home" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')" wire:navigate>Pulpit</flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" href="{{ route('admin.orders.index') }}" :current="request()->routeIs('admin.orders.*')" wire:navigate>Zamówienia</flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-left-right" href="{{ route('admin.quote-requests.index') }}" :current="request()->routeIs('admin.quote-requests.*')" wire:navigate>Zapytania B2B</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Zarządzanie" class="grid">
                    @if(auth()->user()->isOwner())
                        <flux:sidebar.item icon="users" href="{{ route('admin.clients.index') }}" :current="request()->routeIs('admin.clients.*')" wire:navigate>Klienci</flux:sidebar.item>
                    @endif
                    <flux:sidebar.item icon="bell" href="{{ route('admin.notifications.index') }}" :current="request()->routeIs('admin.notifications.*')" wire:navigate>
                        Powiadomienia
                        @if(auth()->user()->unreadNotifications()->count() > 0)
                            <flux:badge size="sm" class="ms-auto">{{ auth()->user()->unreadNotifications()->count() }}</flux:badge>
                        @endif
                    </flux:sidebar.item>
                    @if(auth()->user()->isOwner())
                        <flux:sidebar.item icon="shield-check" href="{{ route('admin.privacy.index') }}" :current="request()->routeIs('admin.privacy.*')" wire:navigate>Wnioski RODO</flux:sidebar.item>
                        <flux:sidebar.item icon="academic-cap" href="{{ route('admin.printing.edit', 'thesis') }}" :current="request()->routeIs('admin.printing.*') && request()->route('type') === 'thesis'" wire:navigate>Prace Dyplomowe</flux:sidebar.item>
                        <flux:sidebar.item icon="photo" href="{{ route('admin.products.index') }}" :current="request()->routeIs('admin.products.*')" wire:navigate>Produkty</flux:sidebar.item>
                        <flux:sidebar.item icon="adjustments-horizontal" href="{{ route('admin.options.index') }}" :current="request()->routeIs('admin.options.*')" wire:navigate>Opcje i ceny</flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:dropdown position="top" align="start" class="hidden lg:block">
                <flux:sidebar.profile :name="auth()->user()->name" :initials="auth()->user()->initials()" icon:trailing="chevron-up-down" />
                <flux:menu>
                    <div class="flex items-center gap-2 px-2 py-2 text-sm">
                        <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                        <div class="min-w-0">
                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">Wyloguj</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:profile :initials="auth()->user()->initials()" />
        </flux:header>

        <flux:main class="admin-shell min-h-screen bg-zinc-950 p-5 text-zinc-100 dark:bg-zinc-950 sm:p-8">
            @if(session('status'))
                <flux:callout variant="success" class="mb-5" icon="check-circle">{{ session('status') }}</flux:callout>
            @endif
            @if($errors->any())
                <flux:callout variant="danger" class="mb-5" icon="exclamation-triangle">{{ $errors->first() }}</flux:callout>
            @endif
            @yield('content')
        </flux:main>

        @fluxScripts
    </body>
</html>
