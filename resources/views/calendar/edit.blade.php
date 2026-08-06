@extends('layouts.app')

@section('title', 'Editar compromisso')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-4xl font-semibold">Editar compromisso</h1>
        <p class="mt-2 text-[var(--color-muted)]">{{ $appointment->summary }}</p>
    </div>

    <livewire:calendar.appointment-form :appointment="$appointment" />
@endsection
