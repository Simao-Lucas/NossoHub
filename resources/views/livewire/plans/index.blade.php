<div>
    <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="grid flex-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <input wire:model.live.debounce.300ms="search" type="search" class="nh-input" placeholder="Buscar...">
            <select wire:model.live="category" class="nh-input">
                <option value="">Todas categorias</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="nh-input">
                <option value="">Todos status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="priority" class="nh-input">
                <option value="">Todas prioridades</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="openCreate" class="nh-btn-primary shrink-0">Novo item</button>
    </div>

    @if ($showForm)
        <div class="nh-card mb-8 p-6">
            <h2 class="font-display text-2xl">{{ $editingId ? 'Editar item' : 'Novo item' }}</h2>
            <form wire:submit="save" class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="nh-label">Título</label>
                    <input wire:model="title" type="text" class="nh-input">
                    @error('title') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="nh-label">Descrição</label>
                    <textarea wire:model="description" rows="3" class="nh-input"></textarea>
                </div>
                <div>
                    <label class="nh-label">Categoria</label>
                    <select wire:model="formCategory" class="nh-input">
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="nh-label">Prioridade</label>
                    <select wire:model="formPriority" class="nh-input">
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="nh-label">Status</label>
                    <select wire:model="formStatus" class="nh-input">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="nh-label">Link (opcional)</label>
                    <input wire:model="link" type="url" class="nh-input" placeholder="https://">
                    @error('link') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="nh-label">Observações</label>
                    <textarea wire:model="notes" rows="2" class="nh-input"></textarea>
                </div>
                <div class="sm:col-span-2 flex flex-wrap gap-2">
                    <button type="submit" class="nh-btn-primary">Salvar</button>
                    <button type="button" wire:click="cancel" class="nh-btn-ghost">Cancelar</button>
                </div>
            </form>
        </div>
    @endif

    @if ($items->isEmpty())
        <x-empty-state title="Nenhum plano ainda" description="Adicionem a primeira ideia juntos." />
    @else
        <div class="grid gap-4">
            @foreach ($items as $item)
                <article class="nh-card p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap gap-2">
                                <x-badge tone="yellow">{{ $item->category->label() }}</x-badge>
                                <x-badge tone="purple">{{ $item->priority->label() }}</x-badge>
                                <x-badge :tone="$item->status === \App\Enums\PlanStatus::Completed ? 'success' : ($item->status === \App\Enums\PlanStatus::InProgress ? 'warning' : 'muted')">
                                    {{ $item->status->label() }}
                                </x-badge>
                            </div>
                            <h3 class="font-display text-xl">{{ $item->title }}</h3>
                            @if ($item->description)
                                <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $item->description }}</p>
                            @endif
                            @if ($item->notes)
                                <p class="mt-2 text-xs text-[var(--color-muted)]">Obs: {{ $item->notes }}</p>
                            @endif
                            @if ($item->link)
                                <a href="{{ $item->link }}" target="_blank" rel="noopener" class="mt-3 inline-block text-sm text-[var(--brand-yellow-soft)] hover:underline">
                                    Abrir link
                                </a>
                            @endif
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button type="button" wire:click="openEdit({{ $item->id }})" class="nh-btn-ghost">Editar</button>
                            <button
                                type="button"
                                wire:click="delete({{ $item->id }})"
                                wire:confirm="Remover este item?"
                                class="nh-btn-ghost text-rose-300"
                            >
                                Excluir
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
