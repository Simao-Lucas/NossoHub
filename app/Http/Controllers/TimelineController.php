<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Contracts\View\View;

class TimelineController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function __invoke(): View
    {
        return view('timeline.index', [
            'events' => $this->events->timeline(ascending: true),
        ]);
    }
}
