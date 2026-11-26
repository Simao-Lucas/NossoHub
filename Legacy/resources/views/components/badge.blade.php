@props([
    'tone' => 'muted',
])

@php
    $tones = [
        'muted' => 'bg-white/10 text-[var(--color-muted)]',
        'yellow' => 'bg-[var(--brand-yellow)]/15 text-[var(--brand-yellow-soft)]',
        'purple' => 'bg-[var(--brand-purple-600)]/40 text-[var(--color-ink)]',
        'success' => 'bg-emerald-500/15 text-emerald-300',
        'warning' => 'bg-amber-500/15 text-amber-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'nh-badge '.($tones[$tone] ?? $tones['muted'])]) }}>
    {{ $slot }}
</span>
