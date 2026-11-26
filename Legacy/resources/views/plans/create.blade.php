@extends('layouts.app')

@section('title', 'Novo plano')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-4xl font-semibold">Novo plano</h1>
        <p class="mt-2 text-[var(--color-muted)]">Uma ideia para fazerem juntos.</p>
    </div>

    <livewire:plans.plan-form />
@endsection
