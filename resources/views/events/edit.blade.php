@extends('layouts.app')

@section('title', 'Editar evento')

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-3xl flex-col items-center py-6">
        <div class="animate-fade-up w-full text-center">
            <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">
                Editar evento
            </h1>
            <p class="mt-3 text-sm text-[var(--color-muted)]">{{ $event->title }}</p>
        </div>

        <div class="animate-fade-up mt-10 w-full" style="animation-delay: 120ms">
            <livewire:events.event-form :event="$event" />
        </div>

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 240ms">
            <a href="{{ route('events.show', $event) }}" class="nh-btn-ghost">Voltar</a>
            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Remover este evento?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="nh-btn-ghost text-rose-300">Excluir evento</button>
            </form>
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>
    </div>
@endsection
