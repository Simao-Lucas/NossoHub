@extends('layouts.app')

@section('title', 'Editar evento — '.config('app.name'))

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-4xl font-semibold">Editar evento</h1>
        <p class="mt-2 text-[var(--color-muted)]">{{ $event->title }}</p>
    </div>

    <livewire:events.event-form :event="$event" />
@endsection
