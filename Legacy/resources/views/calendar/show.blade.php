@extends('layouts.app')

@section('title', $appointment->summary)

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-3xl flex-col items-center py-6">
        <div class="animate-fade-up w-full text-center">
            <p class="text-sm uppercase tracking-[0.28em] text-[var(--brand-yellow)]">
                {{ $appointment->status->label() }}
            </p>

            <h1 class="font-display mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">
                {{ $appointment->summary }}
            </h1>

            <p class="mx-auto mt-6 max-w-lg text-sm text-[var(--color-muted)] sm:text-base">
                @if ($appointment->all_day)
                    {{ $appointment->starts_at->translatedFormat('d \d\e F \d\e Y') }}
                    @if (! $appointment->starts_at->isSameDay($appointment->ends_at))
                        — {{ $appointment->ends_at->translatedFormat('d \d\e F \d\e Y') }}
                    @endif
                    <span class="mt-1 block text-xs uppercase tracking-[0.16em]">Dia inteiro</span>
                @else
                    {{ $appointment->starts_at->translatedFormat('d \d\e F \d\e Y, H:i') }}
                    —
                    {{ $appointment->ends_at->translatedFormat('d \d\e F \d\e Y, H:i') }}
                    <span class="mt-1 block text-xs text-[var(--color-muted)]">{{ $appointment->timezone }}</span>
                @endif
            </p>

            @if (filled($appointment->location))
                <p class="mt-4 text-sm text-[var(--brand-yellow-soft)]">
                    {{ $appointment->location }}
                </p>
            @endif

            <div class="mx-auto mt-8 h-px w-24 bg-gradient-to-r from-transparent via-[var(--brand-yellow)]/60 to-transparent"></div>
        </div>

        @if (filled($appointment->description))
            <div class="animate-fade-up mt-10 w-full" style="animation-delay: 100ms">
                <p class="mx-auto max-w-xl whitespace-pre-line text-center text-base leading-relaxed text-[var(--color-muted)]">
                    {{ $appointment->description }}
                </p>
            </div>
        @endif

        <div class="animate-fade-up mt-8 flex flex-wrap items-center justify-center gap-2 text-xs uppercase tracking-[0.16em] text-[var(--color-muted)]" style="animation-delay: 140ms">
            <span>{{ $appointment->visibility->label() }}</span>
            <span class="h-1 w-1 rounded-full bg-[var(--brand-yellow)]/70"></span>
            <span>{{ $appointment->transparency->label() }}</span>
        </div>

        <div class="animate-fade-up mt-10 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 180ms">
            <a href="{{ $googleUrl }}" target="_blank" rel="noopener" class="nh-btn-primary">
                Enviar ao Google Calendar
            </a>
            <a href="{{ route('calendar.ics', $appointment) }}" class="nh-btn-ghost">
                Baixar .ics
            </a>
        </div>

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 240ms">
            <a href="{{ route('calendar.index') }}" class="nh-btn-ghost">Calendário</a>
            <a href="{{ route('calendar.edit', $appointment) }}" class="nh-btn-ghost">Editar</a>
            <form method="POST" action="{{ route('calendar.destroy', $appointment) }}" onsubmit="return confirm('Remover este compromisso?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="nh-btn-ghost text-rose-300">Excluir</button>
            </form>
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>
    </div>
@endsection
