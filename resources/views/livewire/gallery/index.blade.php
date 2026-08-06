<div
    x-data="{
        lightbox: null,
        open(item) {
            this.lightbox = item
            document.body.classList.add('overflow-hidden')
        },
        close() {
            this.lightbox = null
            document.body.classList.remove('overflow-hidden')
        },
    }"
    @keydown.escape.window="lightbox && close()"
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
                                style="image-orientation: from-image;"
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

    <template x-teleport="body">
        <div
            x-show="lightbox"
            x-cloak
            class="nh-lightbox"
            style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,.92); padding: 1rem;"
            wire:ignore
            @click.self="close()"
        >
            <button
                type="button"
                class="nh-btn-ghost nh-lightbox-close"
                style="position: absolute; top: 1rem; right: 1rem; z-index: 1;"
                @click="close()"
            >
                Fechar
            </button>

            <div
                class="nh-lightbox-frame"
                style="display: flex; align-items: center; justify-content: center; width: 96vw; height: 92vh; margin: auto;"
            >
                <template x-if="lightbox?.type === 'video'">
                    <video
                        :src="lightbox.url"
                        class="nh-lightbox-media"
                        style="width: 100%; height: 100%; object-fit: contain; object-position: center; border-radius: 1rem; background: #000;"
                        controls
                        autoplay
                    ></video>
                </template>
                <template x-if="lightbox && lightbox.type !== 'video'">
                    <img
                        :src="lightbox.url"
                        :alt="lightbox.name || 'Foto'"
                        class="nh-lightbox-media"
                        style="width: 100%; height: 100%; object-fit: contain; object-position: center; border-radius: 1rem; image-orientation: from-image;"
                    >
                </template>
            </div>
        </div>
    </template>
</div>
