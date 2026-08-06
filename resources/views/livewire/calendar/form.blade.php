<div class="nh-card p-6 sm:p-8">
    <form wire:submit="save" class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="nh-label">Título (summary)</label>
            <input wire:model="summary" type="text" class="nh-input">
            @error('summary') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="nh-label">Descrição</label>
            <textarea wire:model="description" rows="4" class="nh-input"></textarea>
            @error('description') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="nh-label">Local (location)</label>
            <input wire:model="location" type="text" class="nh-input">
            @error('location') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-center gap-3 text-sm text-[var(--color-ink)]">
                <input wire:model.live="all_day" type="checkbox" class="size-4 rounded border-white/20 bg-black/30 text-[var(--brand-yellow)] focus:ring-[var(--brand-yellow)]/40">
                Dia inteiro
            </label>
        </div>

        <div>
            <label class="nh-label">Início (start)</label>
            <input
                wire:model="starts_at"
                type="{{ $all_day ? 'date' : 'datetime-local' }}"
                class="nh-input"
            >
            @error('starts_at') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Término (end)</label>
            <input
                wire:model="ends_at"
                type="{{ $all_day ? 'date' : 'datetime-local' }}"
                class="nh-input"
            >
            @error('ends_at') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Fuso (timeZone)</label>
            <input wire:model="timezone" type="text" class="nh-input" placeholder="America/Sao_Paulo">
            @error('timezone') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Status</label>
            <select wire:model="status" class="nh-input">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="nh-label">Visibilidade</label>
            <select wire:model="visibility" class="nh-input">
                @foreach ($visibilities as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="nh-label">Disponibilidade</label>
            <select wire:model="transparency" class="nh-input">
                @foreach ($transparencies as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2 flex flex-wrap gap-2 pt-2">
            <button type="submit" class="nh-btn-primary">Salvar compromisso</button>
            <a
                href="{{ $appointmentId ? route('calendar.show', $appointmentId) : route('calendar.index') }}"
                class="nh-btn-ghost"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>
