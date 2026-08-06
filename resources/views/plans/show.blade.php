@extends('layouts.app')

@section('title', $item->title)

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-3xl flex-col items-center py-6">
        <div class="animate-fade-up relative w-full text-center">
            <p class="text-sm uppercase tracking-[0.28em] text-[var(--brand-yellow)]">
                {{ $item->category?->name ?? 'Plano' }}
            </p>

            <h1 class="font-display mt-5 text-4xl font-semibold tracking-tight sm:text-5xl md:text-6xl">
                {{ $item->title }}
            </h1>

            <div class="mx-auto mt-6 flex max-w-md items-center justify-center gap-3 text-xs uppercase tracking-[0.18em] text-[var(--color-muted)]">
                <span>{{ $item->priority->label() }}</span>
                <span class="h-1 w-1 rounded-full bg-[var(--brand-yellow)]/70"></span>
                <span
                    @class([
                        'text-emerald-300' => $item->status === \App\Enums\PlanStatus::Completed,
                        'text-amber-300' => $item->status === \App\Enums\PlanStatus::InProgress,
                    ])
                >
                    {{ $item->status->label() }}
                </span>
            </div>

            <div
                class="mx-auto mt-10 h-px w-24 bg-gradient-to-r from-transparent via-[var(--brand-yellow)]/60 to-transparent"
                aria-hidden="true"
            ></div>
        </div>

        @if (filled($item->description))
            <div class="animate-fade-up mt-10 w-full" style="animation-delay: 100ms">
                <p class="mx-auto max-w-xl whitespace-pre-line text-center text-base leading-relaxed text-[var(--color-muted)] sm:text-lg">
                    {{ $item->description }}
                </p>
            </div>
        @endif

        @if (filled($item->notes))
            <div class="animate-fade-up mt-10 w-full max-w-lg" style="animation-delay: 160ms">
                <p class="text-center text-[10px] uppercase tracking-[0.22em] text-[var(--brand-yellow)]/80">
                    Observações
                </p>
                <p class="mt-3 whitespace-pre-line text-center text-sm leading-relaxed text-[var(--color-muted)]">
                    {{ $item->notes }}
                </p>
            </div>
        @endif

        @if (filled($item->link))
            <div class="animate-fade-up mt-10" style="animation-delay: 200ms">
                <a
                    href="{{ $item->link }}"
                    target="_blank"
                    rel="noopener"
                    class="nh-btn-primary"
                >
                    Abrir link
                </a>
            </div>
        @endif

        <div class="animate-fade-up mt-14 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 260ms">
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
