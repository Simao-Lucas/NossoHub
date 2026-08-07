<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class TimelineController extends Controller
{
    private const PX_PER_DAY = 4;

    public function __construct(
        private readonly EventService $events,
    ) {}

    public function __invoke(): View
    {
        $events = $this->events->timeline(ascending: true);

        return view('timeline.index', [
            'items' => $this->withSpacers($events),
        ]);
    }

    /**
     * @return Collection<int, array{event: \App\Models\Event, spacer_px: int}>
     */
    private function withSpacers(Collection $events): Collection
    {
        $previous = null;

        return $events->values()->map(function ($event) use (&$previous) {
            $spacerPx = 0;

            if ($previous !== null) {
                $gapDays = (int) $previous->occurred_at->diffInDays($event->occurred_at);
                $spacerPx = max(0, $gapDays) * self::PX_PER_DAY;
            }

            $previous = $event;

            return [
                'event' => $event,
                'spacer_px' => $spacerPx,
            ];
        });
    }
}
