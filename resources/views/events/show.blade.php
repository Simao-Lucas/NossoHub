@extends('layouts.app')

@section('title', $event->title)

@section('hide_navbar', true)

@section('content')
    <div
        class="mx-auto flex min-h-[80vh] w-full max-w-5xl flex-col items-center py-6"
        x-data="{
            lightbox: null,
            open(item) { this.lightbox = item },
            close() { this.lightbox = null },
        }"
        @keydown.escape.window="close()"
    >
        <div class="animate-fade-up w-full text-center">
            <time
                datetime="{{ $event->occurred_at->toDateString() }}"
                class="text-sm uppercase tracking-[0.22em] text-[var(--brand-yellow)]"
            >
                {{ $event->occurred_at->translatedFormat('d \d\e F \d\e Y') }}
            </time>
            <h1 class="font-display mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">
                {{ $event->title }}
            </h1>
            @if (filled($event->description))
                <p class="mx-auto mt-5 max-w-2xl whitespace-pre-line text-sm leading-relaxed text-[var(--color-muted)] sm:text-base">
                    {{ $event->description }}
                </p>
            @endif
        </div>

        @if (count($photos) === 0 && count($videos) === 0)
            <div class="animate-fade-up mt-14 w-full max-w-md" style="animation-delay: 120ms">
                <x-empty-state
                    title="Sem mídias ainda"
                    description="Edite o evento para enviar fotos ou vídeos."
                />
            </div>
        @endif

        @if (count($photos))
            <section class="animate-fade-up mt-14 w-full" style="animation-delay: 120ms">
                <h2 class="mb-5 text-center font-display text-2xl">Fotos</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($photos as $photo)
                        <button
                            type="button"
                            class="group overflow-hidden rounded-3xl border border-white/8 bg-black/20 text-left"
                            @click="open({ type: 'photo', url: @js($photo['url']), name: @js($photo['original_name']) })"
                        >
                            <img
                                src="{{ $photo['url'] }}"
                                alt="{{ $photo['original_name'] ?? 'Foto' }}"
                                class="aspect-square w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        @if (count($videos))
            <section class="animate-fade-up mt-14 w-full" style="animation-delay: 180ms">
                <h2 class="mb-5 text-center font-display text-2xl">Vídeos</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($videos as $video)
                        <button
                            type="button"
                            class="group nh-card nh-card-hover overflow-hidden p-0 text-left"
                            @click="open({ type: 'video', url: @js($video['url']), name: @js($video['original_name']) })"
                        >
                            <div class="relative aspect-video bg-black/40">
                                <video src="{{ $video['url'] }}" class="h-full w-full object-cover" muted preload="metadata"></video>
                                <span class="absolute inset-0 flex items-center justify-center">
                                    <span class="rounded-full bg-[var(--brand-yellow)] px-4 py-2 text-sm font-medium text-[var(--brand-purple-deep)] transition group-hover:scale-105">
                                        ▶ Assistir
                                    </span>
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="animate-fade-up mt-12 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 240ms">
            <a href="{{ route('timeline') }}" class="nh-btn-ghost">Linha do Tempo</a>
            <a href="{{ route('events.edit', $event) }}" class="nh-btn-ghost">Editar</a>
            <a href="{{ route('home') }}" class="nh-btn-primary">Início</a>
        </div>

        <div
            x-show="lightbox"
            x-cloak
            class="nh-lightbox"
            style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.92); padding: 1rem;"
            @click.self="close()"
        >
            <button type="button" class="nh-btn-ghost" style="position: absolute; top: 1rem; right: 1rem; z-index: 1;" @click="close()">Fechar</button>
            <template x-if="lightbox?.type === 'photo'">
                <img
                    :src="lightbox.url"
                    :alt="lightbox.name || 'Foto'"
                    class="nh-lightbox-media"
                    style="width: 96vw; height: 92vh; object-fit: contain; border-radius: 1rem;"
                >
            </template>
            <template x-if="lightbox?.type === 'video'">
                <video
                    :src="lightbox.url"
                    class="nh-lightbox-media"
                    style="width: 96vw; height: 92vh; object-fit: contain; border-radius: 1rem; background: #000;"
                    controls
                    autoplay
                ></video>
            </template>
        </div>
    </div>
@endsection
