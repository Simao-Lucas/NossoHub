@props([
    'title' => 'Nada por aqui ainda',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'nh-card flex flex-col items-center justify-center px-6 py-16 text-center']) }}>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-3xl bg-[var(--brand-yellow)]/10 text-2xl text-[var(--brand-yellow)]">
        ✦
    </div>
    <h3 class="font-display text-xl text-[var(--color-ink)]">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-md text-sm text-[var(--color-muted)]">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-6">{{ $action }}</div>
    @endif
</div>
