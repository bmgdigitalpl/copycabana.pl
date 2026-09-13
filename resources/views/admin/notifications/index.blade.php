@extends('layouts.admin')
@section('title', 'Powiadomienia | CopyCabana')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <flux:heading size="xl">Powiadomienia</flux:heading>
        <flux:text class="mt-2">Aktualności wymagające uwagi zespołu CopyCabana.</flux:text>
    </div>
    @if(auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
            @csrf
            @method('PUT')
            <flux:button type="submit" variant="ghost">Oznacz wszystkie jako przeczytane</flux:button>
        </form>
    @endif
</div>

<div class="mt-6 flex gap-2">
    <flux:button :href="route('admin.notifications.index')" :variant="request('filter') === 'unread' ? 'ghost' : 'primary'">Wszystkie</flux:button>
    <flux:button :href="route('admin.notifications.index', ['filter' => 'unread'])" :variant="request('filter') === 'unread' ? 'primary' : 'ghost'">Nieprzeczytane</flux:button>
</div>

<div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
    @forelse($notifications as $notification)
        @php($data = $notification->data)
        <article class="flex flex-col gap-4 border-b border-zinc-200 p-5 last:border-b-0 dark:border-zinc-700 sm:flex-row sm:items-start sm:justify-between {{ $notification->read_at ? '' : 'bg-pink-50/50 dark:bg-pink-950/20' }}">
            <div class="flex gap-3">
                <span class="mt-1 size-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-zinc-300 dark:bg-zinc-600' : 'bg-[#D51A70]' }}"></span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="sm">{{ data_get($data, 'title', 'Powiadomienie') }}</flux:heading>
                        @if(data_get($data, 'category'))
                            <flux:badge size="sm">{{ data_get($data, 'category') }}</flux:badge>
                        @endif
                    </div>
                    <flux:text class="mt-1">{{ data_get($data, 'message') }}</flux:text>
                    <flux:text class="mt-2 text-xs">{{ $notification->created_at->diffForHumans() }}</flux:text>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                @if(data_get($data, 'action_url'))
                    <flux:button :href="data_get($data, 'action_url')" variant="ghost" size="sm">Otwórz</flux:button>
                @endif
                @if(!$notification->read_at)
                    <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                        @csrf
                        @method('PUT')
                        <flux:button type="submit" variant="ghost" size="sm">Oznacz jako przeczytane</flux:button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="p-8 text-center">
            <flux:heading size="sm">Brak powiadomień</flux:heading>
            <flux:text class="mt-1">Nie ma teraz żadnych aktualności do obsłużenia.</flux:text>
        </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="mt-6">{{ $notifications->links() }}</div>
@endif
@endsection
