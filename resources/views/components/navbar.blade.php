@php
    $links = [
        ['route' => 'home', 'label' => 'Início'],
        ['route' => 'timeline', 'label' => 'Linha do Tempo'],
        ['route' => 'gallery', 'label' => 'Nossa Galeria'],
        ['route' => 'wishlist.index', 'label' => 'Wishlist'],
        ['route' => 'events.index', 'label' => 'Eventos'],
    ];
@endphp

<header
    x-data="{ open: false }"
    class="sticky top-0 z-40 border-b border-white/5 bg-[var(--brand-purple-deep)]/80 backdrop-blur-xl"
>
    <div class="nh-container flex h-14 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="group flex items-center gap-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-2xl bg-[var(--brand-yellow)] text-base text-[var(--brand-purple-deep)] transition group-hover:scale-105">
                ♥
            </span>
            <span class="font-display text-lg font-semibold tracking-tight text-[var(--color-ink)]">
                Nosso Hub
            </span>
        </a>

        <nav class="hidden items-center gap-1 md:flex">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    @class([
                        'rounded-2xl px-3 py-2 text-sm transition',
                        'bg-white/10 text-[var(--brand-yellow-soft)]' => request()->routeIs($link['route']) || request()->routeIs(str_replace('.index', '.*', $link['route'])),
                        'text-[var(--color-muted)] hover:bg-white/5 hover:text-[var(--color-ink)]' => ! (request()->routeIs($link['route']) || request()->routeIs(str_replace('.index', '.*', $link['route']))),
                    ])
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <button
            type="button"
            class="nh-btn-ghost md:hidden"
            @click="open = !open"
            aria-label="Menu"
        >
            <span x-text="open ? 'Fechar' : 'Menu'"></span>
        </button>
    </div>

    <div
        x-show="open"
        x-transition.origin.top
        class="border-t border-white/5 md:hidden"
        style="display: none;"
    >
        <nav class="nh-container flex flex-col gap-1 py-3">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="rounded-2xl px-3 py-2.5 text-sm text-[var(--color-ink)] hover:bg-white/5">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
