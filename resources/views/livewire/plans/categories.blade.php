<div class="mx-auto w-full max-w-2xl">
    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="add" class="mb-10 flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label class="nh-label" for="category-name">Nova categoria</label>
            <input
                id="category-name"
                wire:model="name"
                type="text"
                class="nh-input"
                placeholder="Ex.: Passeio, Série, Café..."
            >
            @error('name') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="nh-btn-primary shrink-0">Adicionar</button>
    </form>

    @if ($categories->isEmpty())
        <x-empty-state
            title="Nenhuma categoria"
            description="Crie a primeira para organizar os planos."
        />
    @else
        <ul class="space-y-3">
            @foreach ($categories as $category)
                <li class="nh-card flex items-center justify-between gap-4 p-4 sm:p-5">
                    <div class="min-w-0">
                        <p class="font-display text-lg">{{ $category->name }}</p>
                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                            {{ $category->plans_count }}
                            {{ $category->plans_count === 1 ? 'plano' : 'planos' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="delete({{ $category->id }})"
                        wire:confirm="Excluir a categoria “{{ $category->name }}”?"
                        class="nh-btn-ghost shrink-0 text-rose-300"
                        @disabled($category->plans_count > 0)
                        title="{{ $category->plans_count > 0 ? 'Há planos usando esta categoria' : 'Excluir' }}"
                    >
                        Excluir
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
