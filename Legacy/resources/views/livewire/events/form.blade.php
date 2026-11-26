<div class="nh-card p-6 sm:p-8">
    <form wire:submit="save" class="grid gap-5">
        <div>
            <label class="nh-label">Título</label>
            <input wire:model="title" type="text" class="nh-input">
            @error('title') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Descrição (opcional)</label>
            <textarea wire:model="description" rows="4" class="nh-input"></textarea>
            @error('description') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="max-w-xs">
            <label class="nh-label">Data</label>
            <input wire:model="occurred_at" type="date" class="nh-input">
            @error('occurred_at') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Fotos e vídeos</label>
            <input
                wire:model="uploads"
                type="file"
                multiple
                accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm"
                class="mt-1 block w-full text-sm text-[var(--color-muted)] file:mr-4 file:rounded-2xl file:border-0 file:bg-[var(--brand-yellow)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-[var(--brand-purple-deep)] hover:file:bg-[var(--brand-yellow-soft)]"
            >
            <div wire:loading wire:target="uploads" class="mt-2 text-xs text-[var(--brand-yellow-soft)]">
                Enviando arquivos...
            </div>
            @error('uploads.*') <p class="mt-2 text-xs text-rose-300">{{ $message }}</p> @enderror
            @error('uploads') <p class="mt-2 text-xs text-rose-300">{{ $message }}</p> @enderror

            @if (count($uploads))
                <ul class="mt-4 space-y-2">
                    @foreach ($uploads as $index => $file)
                        <li class="flex items-center justify-between gap-3 rounded-2xl border border-white/8 bg-white/5 px-3 py-2 text-sm">
                            <span class="truncate text-[var(--color-muted)]">{{ $file->getClientOriginalName() }}</span>
                            <button type="button" wire:click="removeUpload({{ $index }})" class="nh-btn-ghost !px-3 !py-1.5 text-xs text-rose-300">
                                Remover
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if (count($existingMedia))
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($existingMedia as $item)
                        <div class="relative overflow-hidden rounded-2xl border border-white/8 bg-black/30">
                            @if ($item['type'] === 'video')
                                <video src="{{ $item['url'] }}" class="aspect-square w-full object-cover" muted></video>
                                <span class="absolute left-2 top-2 rounded-full bg-black/60 px-2 py-0.5 text-[10px] uppercase text-white">Vídeo</span>
                            @else
                                <img src="{{ $item['url'] }}" alt="{{ $item['original_name'] }}" class="aspect-square w-full object-cover">
                            @endif
                            <button
                                type="button"
                                wire:click="removeExisting({{ $item['id'] }})"
                                class="absolute right-2 top-2 rounded-full bg-black/70 px-2 py-1 text-[10px] text-rose-200"
                            >
                                Remover
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex flex-wrap justify-center gap-2 pt-2 sm:justify-start">
            <button type="submit" class="nh-btn-primary" wire:loading.attr="disabled">Salvar evento</button>
            <a
                href="{{ $eventId ? route('events.show', $eventId) : route('timeline') }}"
                class="nh-btn-ghost"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>
