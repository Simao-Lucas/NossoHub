<div class="nh-card p-6 sm:p-8">
    <form wire:submit="save" class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="nh-label">Título</label>
            <input wire:model="title" type="text" class="nh-input">
            @error('title') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="nh-label">Descrição (opcional)</label>
            <textarea wire:model="description" rows="4" class="nh-input"></textarea>
            @error('description') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Categoria</label>
            <select wire:model="category" class="nh-input">
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('category') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Prioridade</label>
            <select wire:model="priority" class="nh-input">
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('priority') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Status</label>
            <select wire:model="status" class="nh-input">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="nh-label">Link (opcional)</label>
            <input wire:model="link" type="url" class="nh-input" placeholder="https://">
            @error('link') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="nh-label">Observações (opcional)</label>
            <textarea wire:model="notes" rows="3" class="nh-input"></textarea>
            @error('notes') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2 flex flex-wrap gap-2 pt-2">
            <button type="submit" class="nh-btn-primary">Salvar plano</button>
            <a
                href="{{ $planId ? route('plans.show', $planId) : route('plans.index') }}"
                class="nh-btn-ghost"
            >
                Cancelar
            </a>
        </div>
    </form>
</div>
