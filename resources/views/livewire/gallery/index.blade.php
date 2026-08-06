<div>
    @unless ($configured)
        <div class="mx-auto mb-10 max-w-md">
            <x-empty-state
                title="Galeria indisponível"
                description="Configure o Immich no .env do homelab para carregar as fotos."
            />
        </div>
    @else
        <div class="mx-auto mb-10 flex w-full max-w-3xl flex-col gap-3 sm:flex-row sm:flex-wrap sm:justify-center">
            <input
                wire:model.live.debounce.400ms="search"
                type="search"
                class="nh-input sm:min-w-[12rem] sm:flex-1"
                placeholder="Buscar"
                aria-label="Buscar"
            >
            <select wire:model.live="albumId" class="nh-input sm:min-w-[11rem]" aria-label="Álbum">
                <option value="">Todos os álbuns</option>
                @foreach ($albums as $album)
                    <option value="{{ $album['id'] ?? '' }}">{{ $album['albumName'] ?? $album['name'] ?? 'Álbum' }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="nh-input sm:w-auto" aria-label="De">
            <input wire:model.live="dateTo" type="date" class="nh-input sm:w-auto" aria-label="Até">
        </div>

        @if ($assets->isEmpty())
            <div class="mx-auto max-w-md">
                <x-empty-state
                    title="Nenhuma mídia encontrada"
                    description="Ajuste os filtros ou verifique a conexão com o Immich."
                />
            </div>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($assets as $asset)
                    <button
                        type="button"
                        wire:click="openLightbox('{{ $asset['id'] }}')"
                        class="group overflow-hidden rounded-3xl border border-white/8 bg-black/20 text-left focus:outline-none focus:ring-2 focus:ring-[var(--brand-yellow)]/50"
                    >
                        <div class="relative aspect-square">
                            <img
                                src="{{ $asset['thumbnail_url'] }}"
                                alt="{{ $asset['originalFileName'] ?? 'Mídia' }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                            @if (str_contains(strtolower($asset['type']), 'video'))
                                <span class="absolute bottom-2 left-2 rounded-full bg-black/60 px-2 py-0.5 text-[10px] uppercase tracking-wide text-white">
                                    Vídeo
                                </span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="mt-10 flex justify-center">
                <button type="button" wire:click="loadMore" class="nh-btn-ghost" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="loadMore">Carregar mais</span>
                    <span wire:loading wire:target="loadMore">Carregando...</span>
                </button>
            </div>
        @endif
    @endunless

    @if ($lightboxAssetId && $lightboxUrl)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
            x-data
            @keydown.escape.window="$wire.closeLightbox()"
            wire:click.self="closeLightbox"
        >
            <button type="button" wire:click="closeLightbox" class="absolute right-4 top-4 nh-btn-ghost">Fechar</button>
            <img src="{{ $lightboxUrl }}" alt="Fullscreen" class="max-h-[90vh] max-w-full rounded-2xl object-contain" @click.stop>
        </div>
    @endif
</div>
