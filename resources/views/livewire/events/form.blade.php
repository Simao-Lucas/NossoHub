<div class="nh-card p-6 sm:p-8">
    <form wire:submit="save" class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="nh-label">Título</label>
            <input wire:model="title" type="text" class="nh-input">
            @error('title') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="nh-label">Descrição (opcional)</label>
            <textarea wire:model="description" rows="5" class="nh-input"></textarea>
            @error('description') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Data</label>
            <input wire:model="occurred_at" type="date" class="nh-input">
            @error('occurred_at') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Local (opcional)</label>
            <input wire:model="location" type="text" class="nh-input">
        </div>

        <div class="sm:col-span-2 rounded-3xl border border-white/8 bg-black/15 p-4 sm:p-5">
            <h3 class="font-display text-lg">Mídias Immich</h3>
            <p class="mt-1 text-sm text-[var(--color-muted)]">Cole o ID do asset — arquivos ficam só no Immich.</p>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <input wire:model="newAssetId" type="text" class="nh-input" placeholder="Immich asset ID">
                <select wire:model="newAssetType" class="nh-input sm:max-w-40">
                    @foreach ($mediaTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                <button type="button" wire:click="addMedia" class="nh-btn-ghost shrink-0">Adicionar</button>
            </div>
            @error('newAssetId') <p class="mt-2 text-xs text-rose-300">{{ $message }}</p> @enderror

            @if (count($media))
                <ul class="mt-4 space-y-2">
                    @foreach ($media as $index => $item)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-white/8 bg-white/5 px-3 py-2 text-sm">
                            <div class="min-w-0">
                                <span class="font-medium">{{ $item['type'] === 'video' ? 'Vídeo' : 'Foto' }}</span>
                                <span class="ml-2 break-all text-[var(--color-muted)]">{{ $item['immich_asset_id'] }}</span>
                            </div>
                            <button type="button" wire:click="removeMedia({{ $index }})" class="nh-btn-ghost !px-3 !py-1.5 text-xs text-rose-300">Remover</button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="sm:col-span-2 flex flex-wrap gap-2 pt-2">
            <button type="submit" class="nh-btn-primary">Salvar evento</button>
            <a href="{{ route('events.index') }}" class="nh-btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
