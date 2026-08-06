@extends('layouts.app')

@section('title', 'Novo evento')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-4xl font-semibold">Novo evento</h1>
        <p class="mt-2 text-[var(--color-muted)]">Título, data e fotos ou vídeos do momento.</p>
    </div>

    <livewire:events.event-form />
@endsection
