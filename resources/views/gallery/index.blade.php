@extends('layouts.app')

@section('title', 'Nossa Galeria — '.config('app.name'))

@section('content')
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.2em] text-[var(--brand-yellow)]">Memórias</p>
        <h1 class="mt-2 font-display text-4xl font-semibold">Nossa Galeria</h1>
        <p class="mt-3 max-w-xl text-[var(--color-muted)]">
            Fotos e vídeos direto do Immich — sem duplicar arquivos no Laravel.
        </p>
    </div>

    <livewire:gallery.gallery-index />
@endsection
