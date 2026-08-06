@extends('layouts.app')

@section('title', 'Linha do Tempo')

@section('content')
    <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-[var(--brand-yellow)]">História</p>
            <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight sm:text-5xl">Linha do Tempo</h1>
            <p class="mt-3 max-w-xl text-[var(--color-muted)]">
                Os momentos especiais, em ordem cronológica — elegantes, leves e sempre à mão.
            </p>
        </div>
        <a href="{{ route('events.create') }}" class="nh-btn-primary shrink-0">Adicionar momento</a>
    </div>

    @if ($events->isEmpty())
        <x-empty-state
            title="Ainda sem eventos"
            description="Criem o primeiro momento da história de vocês."
        >
            <x-slot:action>
                <a href="{{ route('events.create') }}" class="nh-btn-primary">Criar evento</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <ol class="relative space-y-8 before:absolute before:left-[1.15rem] before:top-3 before:h-[calc(100%-1.5rem)] before:w-px before:bg-gradient-to-b before:from-[var(--brand-yellow)]/60 before:via-white/15 before:to-transparent sm:before:left-6">
            @foreach ($events as $event)
                <li class="relative pl-12 sm:pl-16">
                    <span class="absolute left-2 top-6 flex h-5 w-5 items-center justify-center rounded-full border-2 border-[var(--brand-yellow)] bg-[var(--brand-purple-deep)] sm:left-3.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--brand-yellow)]"></span>
                    </span>

                    <x-card :href="route('events.show', $event)" class="group grid gap-0 overflow-hidden sm:grid-cols-[220px_1fr]">
                        <div class="relative min-h-44 overflow-hidden bg-[var(--brand-purple-800)] sm:min-h-full">
                            @if ($event->cover_immich_asset_id)
                                <img
                                    src="{{ route('immich.thumbnail', ['assetId' => $event->cover_immich_asset_id, 'size' => 'preview']) }}"
                                    alt="{{ $event->title }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                >
                            @else
                                <div class="flex h-full min-h-44 items-center justify-center text-[var(--color-muted)]">
                                    Sem capa
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 p-5 sm:p-6">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-[var(--color-muted)]">
                                <time datetime="{{ $event->occurred_at->toDateString() }}">
                                    {{ $event->occurred_at->translatedFormat('d M Y') }}
                                </time>
                                @if ($event->location)
                                    <span>·</span>
                                    <span>{{ $event->location }}</span>
                                @endif
                            </div>

                            <h2 class="font-display text-2xl font-semibold text-[var(--color-ink)] transition group-hover:text-[var(--brand-yellow-soft)]">
                                {{ $event->title }}
                            </h2>

                            <p class="text-sm leading-relaxed text-[var(--color-muted)]">
                                {{ $event->shortDescription() }}
                            </p>

                            <div class="mt-auto flex flex-wrap gap-2 pt-2">
                                <x-badge tone="yellow">{{ $event->photos_count }} fotos</x-badge>
                                <x-badge tone="purple">{{ $event->videos_count }} vídeos</x-badge>
                            </div>
                        </div>
                    </x-card>
                </li>
            @endforeach
        </ol>
    @endif
@endsection
