<div>
    @unless ($configured)
        <div class="nh-card mb-6 border-[var(--brand-yellow)]/20 p-5 text-sm text-[var(--brand-yellow-soft)]">
            Configure <code class="rounded bg-black/30 px-1.5 py-0.5">IMMICH_BASE_URL</code> e
            <code class="rounded bg-black/30 px-1.5 py-0.5">IMMICH_API_KEY</code> no <code class="rounded bg-black/30 px-1.5 py-0.5">.env</code>
            do homelab para carregar a galeria.
        </div>
    @endunless

    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input wire:model.live.debounce.400ms="search" type="search" class="nh-input" placeholder="Buscar no Immich...">
        <select wire:model.live="albumId" class="nh-input">
            <option value="">Todos os álbuns</option>
            @foreach ($albums as $album)
                <option value="{{ $album['id'] ?? '' }}">{{ $album['albumName'] ?? $album['name'] ?? 'Álbum' }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateFrom" type="date" class="nh-input" title="De">
        <input wire:model.live="dateTo" type="date" class="nh-input" title="Até">
    </div>

    @if ($assets->isEmpty())
        <x-empty-state
            title="Nenhuma mídia encontrada"
            description="Ajuste os filtros ou verifique a conexão com o Immich."
        />
    @else
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($assets as $asset)
                <button
                    type="button"
                    wire:click="openLightbox('{{ $asset['id'] }}')"
                    class="group relative aspect-square overflow-hidden rounded-2xl border border-white/8 bg-black/30 focus:outline-none focus:ring-2 focus:ring-[var(--brand-yellow)]/50"
                >
                    <img
                        src="{{ $asset['thumbnail_url'] }}"
                        alt="{{ $asset['originalFileName'] ?? 'Mídia' }}"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        loading="lazy"
                    >
                    @if (str_contains(strtolower($asset['type']), 'video'))
                        <span class="absolute bottom-2 left-2 rounded-full bg-black/60 px-2 py-0.5 text-[10px] uppercase tracking-wide text-white">Vídeo</span>
                    @endif
                </button>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center">
            <button type="button" wire:click="loadMore" class="nh-btn-ghost" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="loadMore">Carregar mais</span>
                <span wire:loading wire:target="loadMore">Carregando...</span>
            </button>
        </div>
    @endif

    @if ($lightboxAssetId && $lightboxUrl)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
            x-data
            @keydown.escape.window="$wire.closeLightbox()"
        >
            <button type="button" wire:click="closeLightbox" class="absolute right-4 top-4 nh-btn-ghost">Fechar</button>
            <img src="{{ $lightboxUrl }}" alt="Fullscreen" class="max-h-[90vh] max-w-full rounded-2xl object-contain">
        </div>
    @endif
</div>
