@extends('layouts.app')

@section('title', 'Editar plano')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-4xl font-semibold">Editar plano</h1>
        <p class="mt-2 text-[var(--color-muted)]">{{ $item->title }}</p>
    </div>

    <livewire:plans.plan-form :plan-item="$item" />
@endsection
