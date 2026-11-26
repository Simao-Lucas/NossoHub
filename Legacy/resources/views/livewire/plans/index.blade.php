<div>
    <div class="mx-auto mb-10 w-full max-w-3xl space-y-3">
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            class="nh-input w-full"
            placeholder="Buscar planos"
            aria-label="Buscar"
        >
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <select wire:model.live="category" class="nh-input" aria-label="Categoria">
                <option value="">Todas categorias</option>
                @foreach ($categories as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="nh-input" aria-label="Status">
                <option value="">Todos status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="priority" class="nh-input" aria-label="Prioridade">
                <option value="">Todas prioridades</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($items->isEmpty())
        <div class="mx-auto max-w-md">
            <x-empty-state title="Nenhum plano ainda" description="Adicionem a primeira ideia juntos.">
                <x-slot:action>
                    <a href="{{ route('plans.create') }}" class="nh-btn-primary">Novo plano</a>
                </x-slot:action>
            </x-empty-state>
        </div>
    @else
        <div class="mx-auto grid w-full max-w-3xl gap-3">
            @foreach ($items as $item)
                <a
                    href="{{ route('plans.show', $item) }}"
                    class="group nh-card nh-card-hover block p-5 text-left sm:p-6"
                >
                    <div class="mb-2 flex flex-wrap gap-2">
                        <x-badge tone="yellow">{{ $item->category?->name ?? 'Sem categoria' }}</x-badge>
                        <x-badge tone="purple">{{ $item->priority->label() }}</x-badge>
                        <x-badge :tone="$item->status === \App\Enums\PlanStatus::Completed ? 'success' : ($item->status === \App\Enums\PlanStatus::InProgress ? 'warning' : 'muted')">
                            {{ $item->status->label() }}
                        </x-badge>
                    </div>
                    <h2 class="font-display text-xl transition group-hover:text-[var(--brand-yellow-soft)]">
                        {{ $item->title }}
                    </h2>
                    @if (filled($item->description))
                        <p class="mt-2 line-clamp-2 text-sm text-[var(--color-muted)]">
                            {{ $item->description }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
