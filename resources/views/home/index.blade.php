@extends('layouts.app')

@section('title', 'Juntos')

@section('hide_navbar', true)

@section('content')
    <div class="mx-auto flex min-h-[80vh] max-w-4xl flex-col items-center justify-center py-6 text-center">
        <h1 class="animate-fade-up font-display text-4xl font-semibold tracking-tight sm:text-5xl">
            Juntos há
        </h1>

        <div
            class="animate-fade-up mt-10 w-full"
            style="animation-delay: 120ms"
            x-data="loveTimer('{{ $since }}')"
            x-init="start()"
        >
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5 sm:gap-4">
                <template x-for="unit in units" :key="unit.key">
                    <div class="nh-card flex flex-col items-center justify-center px-3 py-5 sm:py-6">
                        <span
                            class="font-display text-3xl font-semibold tabular-nums text-[var(--brand-yellow-soft)] sm:text-4xl"
                            x-text="unit.value"
                        ></span>
                        <span class="mt-2 text-[10px] uppercase tracking-[0.18em] text-[var(--color-muted)] sm:text-xs" x-text="unit.label"></span>
                    </div>
                </template>
            </div>
        </div>

        <nav class="animate-fade-up mt-14 grid w-full grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5" style="animation-delay: 240ms" aria-label="Navegação principal">
            @foreach ([
                ['route' => 'timeline', 'label' => 'Linha do Tempo', 'image' => 'images/home/nav-timeline.png'],
                ['route' => 'gallery', 'label' => 'Nossa Galeria', 'image' => 'images/home/nav-gallery.png'],
                ['route' => 'plans.index', 'label' => 'Nossos Planos', 'image' => 'images/home/nav-plans.png'],
                ['route' => 'calendar.index', 'label' => 'Nosso Calendário', 'image' => 'images/home/nav-events.png'],
            ] as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="group nh-card nh-card-hover flex flex-col overflow-hidden p-0 text-left"
                >
                    <div class="aspect-square overflow-hidden bg-[var(--brand-purple-800)]">
                        <img
                            src="{{ asset($item['image']) }}"
                            alt="{{ $item['label'] }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        >
                    </div>
                    <span class="px-3 py-3 text-center text-sm font-medium text-[var(--color-ink)] group-hover:text-[var(--brand-yellow-soft)]">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endforeach
        </nav>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('loveTimer', (isoStart) => ({
            startAt: new Date(isoStart),
            units: [
                { key: 'years', label: 'Ano', value: 0 },
                { key: 'months', label: 'Mês', value: 0 },
                { key: 'days', label: 'Dia', value: 0 },
                { key: 'hours', label: 'Hora', value: 0 },
                { key: 'minutes', label: 'Minuto', value: 0 },
            ],
            timer: null,
            labelFor(key, value) {
                const singular = {
                    years: 'Ano',
                    months: 'Mês',
                    days: 'Dia',
                    hours: 'Hora',
                    minutes: 'Minuto',
                };
                const plural = {
                    years: 'Anos',
                    months: 'Meses',
                    days: 'Dias',
                    hours: 'Horas',
                    minutes: 'Minutos',
                };

                return value === 0 || value === 1 ? singular[key] : plural[key];
            },
            start() {
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            },
            tick() {
                const now = new Date();
                let cursor = new Date(this.startAt);

                if (now < cursor) {
                    this.units = ['years', 'months', 'days', 'hours', 'minutes'].map((key) => ({
                        key,
                        value: 0,
                        label: this.labelFor(key, 0),
                    }));
                    return;
                }

                let years = now.getFullYear() - cursor.getFullYear();
                cursor.setFullYear(cursor.getFullYear() + years);
                if (cursor > now) {
                    years -= 1;
                    cursor = new Date(this.startAt);
                    cursor.setFullYear(cursor.getFullYear() + years);
                }

                let months = (now.getFullYear() - cursor.getFullYear()) * 12 + (now.getMonth() - cursor.getMonth());
                cursor.setMonth(cursor.getMonth() + months);
                if (cursor > now) {
                    months -= 1;
                    cursor = new Date(this.startAt);
                    cursor.setFullYear(cursor.getFullYear() + years);
                    cursor.setMonth(cursor.getMonth() + months);
                }

                let diffMs = now - cursor;
                const days = Math.floor(diffMs / 86400000);
                diffMs -= days * 86400000;
                const hours = Math.floor(diffMs / 3600000);
                diffMs -= hours * 3600000;
                const minutes = Math.floor(diffMs / 60000);

                const values = { years, months, days, hours, minutes };
                this.units = Object.entries(values).map(([key, value]) => ({
                    key,
                    value,
                    label: this.labelFor(key, value),
                }));
            },
        }));
    });
</script>
@endpush
