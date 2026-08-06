<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <button type="button" wire:click="previousMonth" class="nh-btn-ghost">←</button>
        <div class="text-center">
            <h2 class="font-display text-2xl capitalize">{{ $title }}</h2>
            <button type="button" wire:click="goToday" class="mt-1 text-xs text-[var(--brand-yellow-soft)] hover:underline">
                Hoje
            </button>
        </div>
        <button type="button" wire:click="nextMonth" class="nh-btn-ghost">→</button>
    </div>

    <div class="overflow-hidden rounded-3xl border border-white/8 bg-black/15">
        <div class="grid grid-cols-7 border-b border-white/8">
            @foreach ($weekdays as $day)
                <div class="px-1 py-3 text-center text-[10px] uppercase tracking-[0.16em] text-[var(--color-muted)] sm:text-xs">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @foreach ($weeks as $week)
                @foreach ($week as $day)
                    <div
                        @class([
                            'min-h-[5.5rem] border-b border-r border-white/5 p-1.5 sm:min-h-[7rem] sm:p-2',
                            'bg-white/[0.02]' => $day['inMonth'],
                            'opacity-40' => ! $day['inMonth'],
                        ])
                    >
                        <div class="mb-1 flex items-center justify-between gap-1">
                            <span
                                @class([
                                    'inline-flex h-6 w-6 items-center justify-center rounded-full text-xs',
                                    'bg-[var(--brand-yellow)] font-medium text-[var(--brand-purple-deep)]' => $day['isToday'],
                                    'text-[var(--color-muted)]' => ! $day['isToday'],
                                ])
                            >
                                {{ $day['date']->day }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            @foreach (array_slice($day['appointments'], 0, 3) as $appointment)
                                <a
                                    href="{{ route('calendar.show', $appointment) }}"
                                    class="block truncate rounded-lg bg-[var(--brand-yellow)]/15 px-1.5 py-0.5 text-[10px] text-[var(--brand-yellow-soft)] hover:bg-[var(--brand-yellow)]/25 sm:text-xs"
                                    title="{{ $appointment->summary }}"
                                >
                                    @unless ($appointment->all_day)
                                        <span class="opacity-70">{{ $appointment->starts_at->format('H:i') }}</span>
                                    @endunless
                                    {{ $appointment->summary }}
                                </a>
                            @endforeach
                            @if (count($day['appointments']) > 3)
                                <p class="px-1 text-[10px] text-[var(--color-muted)]">
                                    +{{ count($day['appointments']) - 3 }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
