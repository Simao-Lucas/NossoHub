@extends('layouts.app')

@section('title', $event->title)

@section('content')
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm text-[var(--color-muted)]">
                <time datetime="{{ $event->occurred_at->toDateString() }}">
                    {{ $event->occurred_at->translatedFormat('d \d\e F \d\e Y') }}
                </time>
                @if ($event->location)
                    · {{ $event->location }}
                @endif
            </p>
            <h1 class="mt-2 font-display text-4xl font-semibold sm:text-5xl">{{ $event->title }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('events.edit', $event) }}" class="nh-btn-ghost">Editar</a>
            <a href="{{ route('home') }}" class="nh-btn-ghost">Início</a>
            <a href="{{ route('timeline') }}" class="nh-btn-ghost">Linha do Tempo</a>
        </div>
    </div>

    @if (filled($event->description))
        <section class="nh-card mb-8 p-6 sm:p-8">
            <h2 class="font-display text-2xl">Sobre este momento</h2>
            <p class="mt-4 whitespace-pre-line text-[var(--color-muted)] leading-relaxed">{{ $event->description }}</p>
        </section>
    @endif

    <section class="nh-card mb-8 p-6 sm:p-8">
        <h2 class="font-display text-2xl">Localização</h2>
        <p class="mt-2 text-sm text-[var(--color-muted)]">
            {{ $event->location ?: 'Local não informado' }}
        </p>
        <div class="mt-4 flex min-h-48 items-center justify-center rounded-3xl border border-dashed border-white/10 bg-black/20 text-sm text-[var(--color-muted)]">
            Mapa — estrutura preparada para implementação futura
        </div>
    </section>

    <section class="mb-8">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-2xl">Fotos</h2>
            <x-badge tone="yellow">{{ count($photos) }}</x-badge>
        </div>

        @if (count($photos) === 0)
            <x-empty-state title="Sem fotos" description="Associe IDs Immich ao editar o evento." />
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($photos as $photo)
                    <a href="{{ $photo['original_url'] }}" target="_blank" rel="noopener" class="group overflow-hidden rounded-2xl border border-white/8 bg-black/20">
                        <img
                            src="{{ $photo['thumbnail_url'] }}"
                            alt="Foto do evento"
                            class="aspect-square w-full object-cover transition duration-300 group-hover:scale-105"
                            loading="lazy"
                        >
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-2xl">Vídeos</h2>
            <x-badge tone="purple">{{ count($videos) }}</x-badge>
        </div>

        @if (count($videos) === 0)
            <x-empty-state title="Sem vídeos" description="Associe IDs Immich do tipo vídeo ao editar o evento." />
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($videos as $video)
                    <a href="{{ $video['original_url'] }}" target="_blank" rel="noopener" class="nh-card nh-card-hover overflow-hidden">
                        <div class="relative aspect-video bg-black/40">
                            <img src="{{ $video['thumbnail_url'] }}" alt="Vídeo" class="h-full w-full object-cover" loading="lazy">
                            <span class="absolute inset-0 flex items-center justify-center">
                                <span class="rounded-full bg-[var(--brand-yellow)] px-4 py-2 text-sm font-medium text-[var(--brand-purple-deep)]">▶ Assistir</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
