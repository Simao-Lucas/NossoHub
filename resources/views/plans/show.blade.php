@extends('layouts.app')

@section('title', $item->title)

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-3xl flex-col items-center py-6">
        <div class="animate-fade-up w-full text-center">
            <div class="mb-5 flex flex-wrap items-center justify-center gap-2">
                <x-badge tone="yellow">{{ $item->category->label() }}</x-badge>
                <x-badge tone="purple">{{ $item->priority->label() }}</x-badge>
                <x-badge :tone="$item->status === \App\Enums\PlanStatus::Completed ? 'success' : ($item->status === \App\Enums\PlanStatus::InProgress ? 'warning' : 'muted')">
                    {{ $item->status->label() }}
                </x-badge>
            </div>

            <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">
                {{ $item->title }}
            </h1>

            @if (filled($item->description))
                <p class="mx-auto mt-5 max-w-2xl whitespace-pre-line text-sm leading-relaxed text-[var(--color-muted)] sm:text-base">
                    {{ $item->description }}
                </p>
            @endif

            @if (filled($item->notes))
                <p class="mx-auto mt-4 max-w-2xl text-sm text-[var(--color-muted)]">
                    <span class="text-[var(--color-ink)]">Obs:</span> {{ $item->notes }}
                </p>
            @endif

            @if (filled($item->link))
                <p class="mt-6">
                    <a
                        href="{{ $item->link }}"
                        target="_blank"
                        rel="noopener"
                        class="text-sm text-[var(--brand-yellow-soft)] hover:underline"
                    >
                        Abrir link
                    </a>
                </p>
            @endif
        </div>

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 120ms">
            <a href="{{ route('plans.index') }}" class="nh-btn-ghost">Planos</a>
            <a href="{{ route('plans.edit', $item) }}" class="nh-btn-ghost">Editar</a>
            <form method="POST" action="{{ route('plans.destroy', $item) }}" onsubmit="return confirm('Remover este plano?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="nh-btn-ghost text-rose-300">Excluir</button>
            </form>
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>
    </div>
@endsection
