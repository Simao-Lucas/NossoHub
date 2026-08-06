<div class="mx-auto w-full max-w-2xl">
    <form wire:submit="add" class="mb-10 grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <div>
            <label class="nh-label" for="calendar-email">E-mail do Google</label>
            <input id="calendar-email" wire:model="email" type="email" class="nh-input">
            @error('email') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="nh-label" for="calendar-label">Nome (opcional)</label>
            <input id="calendar-label" wire:model="label" type="text" class="nh-input">
        </div>
        <button type="submit" class="nh-btn-primary">Adicionar</button>
    </form>

    @if ($emails->isEmpty())
        <x-empty-state
            title="Nenhum e-mail vinculado"
            description="Adicione os Gmails que devem receber os compromissos no Google Calendar."
        />
    @else
        <ul class="space-y-3">
            @foreach ($emails as $item)
                <li class="nh-card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $item->email }}</p>
                        @if ($item->label)
                            <p class="mt-1 text-xs text-[var(--color-muted)]">{{ $item->label }}</p>
                        @endif
                        <p class="mt-1 text-[10px] uppercase tracking-[0.16em] {{ $item->is_active ? 'text-emerald-300' : 'text-[var(--color-muted)]' }}">
                            {{ $item->is_active ? 'Ativo' : 'Inativo' }}
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" wire:click="toggle({{ $item->id }})" class="nh-btn-ghost">
                            {{ $item->is_active ? 'Desativar' : 'Ativar' }}
                        </button>
                        <button
                            type="button"
                            wire:click="delete({{ $item->id }})"
                            wire:confirm="Remover este e-mail?"
                            class="nh-btn-ghost text-rose-300"
                        >
                            Excluir
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
