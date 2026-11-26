@extends('layouts.app')

@section('title', 'Linha do Tempo')

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] w-full max-w-6xl flex-col items-center justify-center py-6">
        <div class="animate-fade-up w-full text-center">
            <h1 class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">
                Linha do Tempo
            </h1>
        </div>

        @if ($items->isEmpty())
            <div class="animate-fade-up mt-14 w-full max-w-md" style="animation-delay: 120ms">
                <x-empty-state
                    title="Ainda sem eventos"
                    description="Criem o primeiro momento da história de vocês."
                >
                    <x-slot:action>
                        <a href="{{ route('events.create') }}" class="nh-btn-primary">Criar evento</a>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @else
            <div class="animate-fade-up relative mt-14 w-full" style="animation-delay: 120ms">
                <div class="nh-timeline-scroll -mx-4 px-4 sm:-mx-6 sm:px-6">
                    <div class="relative flex w-max items-stretch gap-0 pb-4 pt-8">
                        <div class="pointer-events-none absolute left-0 right-0 top-[2.15rem] h-px bg-gradient-to-r from-transparent via-[var(--brand-yellow)]/50 to-transparent"></div>

                        @foreach ($items as $item)
                            @php($event = $item['event'])
                            <div
                                class="relative flex w-[min(78vw,280px)] shrink-0 flex-col items-center px-3 sm:w-[300px]"
                                @if ($item['spacer_px'] > 0)
                                    style="margin-left: {{ $item['spacer_px'] }}px"
                                @endif
                            >
                                <span class="relative z-10 mb-5 flex h-4 w-4 items-center justify-center rounded-full border-2 border-[var(--brand-yellow)] bg-[var(--brand-purple-deep)]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--brand-yellow)]"></span>
                                </span>

                                <a
                                    href="{{ route('events.show', $event) }}"
                                    class="group nh-card nh-card-hover flex h-full w-full flex-col p-5 text-left"
                                >
                                    <time
                                        datetime="{{ $event->occurred_at->toDateString() }}"
                                        class="text-xs uppercase tracking-[0.16em] text-[var(--brand-yellow)]"
                                    >
                                        {{ $event->occurred_at->translatedFormat('d M Y') }}
                                    </time>

                                    <h2 class="font-display mt-3 text-xl font-semibold text-[var(--color-ink)] transition group-hover:text-[var(--brand-yellow-soft)]">
                                        {{ $event->title }}
                                    </h2>

                                    @if (filled($event->description))
                                        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-[var(--color-muted)]">
                                            {{ $event->shortDescription(120) }}
                                        </p>
                                    @endif

                                    <div class="mt-auto flex flex-wrap gap-2 pt-4">
                                        <x-badge tone="yellow">{{ $event->photos_count }} {{ $event->photos_count === 1 ? 'foto' : 'fotos' }}</x-badge>
                                        <x-badge tone="purple">{{ $event->videos_count }} {{ $event->videos_count === 1 ? 'vídeo' : 'vídeos' }}</x-badge>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <p class="mt-4 text-center text-xs text-[var(--color-muted)]">
                    Deslize para o lado para ver mais
                </p>
            </div>
        @endif

        <div class="animate-fade-up mt-10 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 220ms">
            <a href="{{ route('home') }}" class="nh-btn-ghost">Início</a>
            <a href="{{ route('events.create') }}" class="nh-btn-primary">Adicionar momento</a>
        </div>
    </div>
@endsection
