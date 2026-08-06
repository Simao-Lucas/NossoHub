<div
    x-data="{
        lightbox: null,
        open(item) { this.lightbox = item },
        close() { this.lightbox = null },
    }"
    @keydown.escape.window="close()"
>
    @unless ($configured)
        <div class="mx-auto mb-10 max-w-md">
            <x-empty-state
                title="Galeria indisponível"
                description="Configure o Immich no .env do homelab para carregar as fotos."
            />
        </div>
    @else
        <div class="mx-auto mb-10 w-full max-w-3xl space-y-3">
            <input
                wire:model.live.debounce.400ms="search"
                type="search"
                class="nh-input w-full"
                placeholder="Buscar fotos e vídeos"
                aria-label="Buscar"
            >
            <select wire:model.live="albumId" class="nh-input w-full" aria-label="Álbum">
                <option value="">Todos os álbuns</option>
                @foreach ($albums as $album)
                    <option value="{{ $album['id'] ?? '' }}">{{ $album['albumName'] ?? $album['name'] ?? 'Álbum' }}</option>
                @endforeach
            </select>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="nh-label" for="gallery-date-from">De</label>
                    <input
                        id="gallery-date-from"
                        wire:model.live="dateFrom"
                        type="date"
                        class="nh-input w-full"
                    >
                </div>
                <div>
                    <label class="nh-label" for="gallery-date-to">Até</label>
                    <input
                        id="gallery-date-to"
                        wire:model.live="dateTo"
                        type="date"
                        class="nh-input w-full"
                    >
                </div>
            </div>
        </div>

        @if ($assets->isEmpty())
            <div class="mx-auto max-w-md">
                <x-empty-state
                    title="Nenhuma mídia encontrada"
                    description="Ajuste a busca, o álbum ou o período, ou verifique a conexão com o Immich."
                />
            </div>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($assets as $asset)
                    <button
                        type="button"
                        class="group overflow-hidden rounded-3xl border border-white/8 bg-black/20 text-left focus:outline-none focus:ring-2 focus:ring-[var(--brand-yellow)]/50"
                        @click="open({
                            type: @js($asset['type']),
                            url: @js($asset['preview_url']),
                            name: @js($asset['originalFileName'] ?? 'Mídia'),
                        })"
                    >
                        <div class="relative aspect-square">
                            <img
                                src="{{ $asset['thumbnail_url'] }}"
                                alt="{{ $asset['originalFileName'] ?? 'Mídia' }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                            @if ($asset['type'] === 'video')
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

    <div
        x-show="lightbox"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
        style="display: none;"
        @click.self="close()"
    >
        <button type="button" class="absolute right-4 top-4 nh-btn-ghost" @click="close()">Fechar</button>
        <template x-if="lightbox?.type === 'video'">
            <video :src="lightbox.url" class="max-h-[90vh] max-w-full rounded-2xl" controls autoplay></video>
        </template>
        <template x-if="lightbox && lightbox.type !== 'video'">
            <img :src="lightbox.url" :alt="lightbox.name || 'Foto'" class="max-h-[90vh] max-w-full rounded-2xl object-contain">
        </template>
    </div>
</div>
