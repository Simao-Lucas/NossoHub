@extends('layouts.app')

@section('title', 'Novo compromisso')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-4xl font-semibold">Novo compromisso</h1>
        <p class="mt-2 text-[var(--color-muted)]">Campos alinhados ao Google Calendar.</p>
    </div>

    <livewire:calendar.appointment-form />
@endsection
