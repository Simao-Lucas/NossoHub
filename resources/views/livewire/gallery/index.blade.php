<div
    x-data="{
        lightbox: null,
        open(item) {
            this.lightbox = item
            document.body.classList.add('overflow-hidden')
            this.$nextTick(() => this.fitLightboxMedia())
        },
        close() {
            this.lightbox = null
            document.body.classList.remove('overflow-hidden')
        },
        fitLightboxMedia() {
            const el = document.querySelector('.nh-lightbox img, .nh-lightbox video')
            if (!el || !this.lightbox) return

            const apply = () => {
                let w = el.naturalWidth || el.videoWidth || this.lightbox.width || 0
                let h = el.naturalHeight || el.videoHeight || this.lightbox.height || 0
                if (!w || !h) return

                // Preview/original veio deitado, mas a foto é vertical (EXIF).
                const needsRotate = this.lightbox.portrait && w > h
                el.style.transform = needsRotate ? 'rotate(90deg)' : ''

                const displayW = needsRotate ? h : w
                const displayH = needsRotate ? w : h
                const maxW = window.innerWidth * 0.96
                const maxH = window.innerHeight * 0.92
                const scale = Math.min(maxW / displayW, maxH / displayH)

                if (needsRotate) {
                    el.style.width = Math.round(displayH * scale) + 'px'
                    el.style.height = Math.round(displayW * scale) + 'px'
                } else {
                    el.style.width = Math.round(displayW * scale) + 'px'
                    el.style.height = Math.round(displayH * scale) + 'px'
                }

                el.style.maxWidth = 'none'
                el.style.maxHeight = 'none'
                el.style.objectFit = 'contain'
                el.style.imageOrientation = 'from-image'
            }

            if (el.tagName === 'VIDEO') {
                if (el.readyState >= 1) apply()
                else el.addEventListener('loadedmetadata', apply, { once: true })
            } else if (el.complete && el.naturalWidth) {
                apply()
            } else {
                el.addEventListener('load', apply, { once: true })
            }
        },
    }"
    @keydown.escape.window="lightbox && close()"
    @resize.window="lightbox && fitLightboxMedia()"
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
                            width: @js($asset['width']),
                            height: @js($asset['height']),
                            portrait: @js($asset['portrait']),
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
            style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.92); padding: 1rem;"
            wire:ignore
            @click.self="close()"
        >
            <button
                type="button"
                class="nh-btn-ghost"
                style="position: absolute; top: 1rem; right: 1rem; z-index: 1;"
                @click="close()"
            >
                Fechar
            </button>

            <template x-if="lightbox?.type === 'video'">
                <video
                    :src="lightbox.url"
                    class="nh-lightbox-media"
                    style="background: #000; border-radius: 1rem;"
                    controls
                    autoplay
                    @loadedmetadata="fitLightboxMedia()"
                ></video>
            </template>
            <template x-if="lightbox && lightbox.type !== 'video'">
                <img
                    :src="lightbox.url"
                    :alt="lightbox.name || 'Foto'"
                    class="nh-lightbox-media"
                    style="border-radius: 1rem; image-orientation: from-image;"
                    @load="fitLightboxMedia()"
                >
            </template>
        </div>
    </template>
</div>
