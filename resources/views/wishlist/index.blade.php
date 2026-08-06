@extends('layouts.app')

@section('title', 'Wishlist — '.config('app.name'))

@section('content')
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.2em] text-[var(--brand-yellow)]">Ideias</p>
        <h1 class="mt-2 font-display text-4xl font-semibold">Wishlist</h1>
        <p class="mt-3 max-w-xl text-[var(--color-muted)]">
            Restaurantes, viagens, filmes, presentes e experiências — uma lista compartilhada.
        </p>
    </div>

    <livewire:wishlist.wishlist-index />
@endsection
