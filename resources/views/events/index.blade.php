@extends('layouts.app')

@section('title', 'Eventos')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-4xl font-semibold">Eventos</h1>
            <p class="mt-2 text-[var(--color-muted)]">Gerencie os momentos da linha do tempo.</p>
        </div>
        <a href="{{ route('events.create') }}" class="nh-btn-primary">Novo evento</a>
    </div>

    @if ($events->isEmpty())
        <x-empty-state title="Nenhum evento" description="Comece adicionando o primeiro momento." />
    @else
        <div class="grid gap-4">
            @foreach ($events as $event)
                <x-card class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between" :hover="false">
                    <div>
                        <h2 class="font-display text-xl">{{ $event->title }}</h2>
                        <p class="mt-1 text-sm text-[var(--color-muted)]">
                            {{ $event->occurred_at->translatedFormat('d/m/Y') }}
                            @if ($event->location) · {{ $event->location }} @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('events.show', $event) }}" class="nh-btn-ghost">Ver</a>
                        <a href="{{ route('events.edit', $event) }}" class="nh-btn-ghost">Editar</a>
                        <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Remover este evento?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="nh-btn-ghost text-rose-300">Excluir</button>
                        </form>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
@endsection
