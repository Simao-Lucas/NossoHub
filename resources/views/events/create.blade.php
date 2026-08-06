@extends('layouts.app')

@section('title', 'Novo evento')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-4xl font-semibold">Novo evento</h1>
        <p class="mt-2 text-[var(--color-muted)]">Associe apenas IDs de assets do Immich — sem upload local.</p>
    </div>

    <livewire:events.event-form />
@endsection
