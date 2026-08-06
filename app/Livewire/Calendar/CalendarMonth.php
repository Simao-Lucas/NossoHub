<?php

namespace App\Livewire\Calendar;

use App\Services\AppointmentService;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class CalendarMonth extends Component
{
    #[Url]
    public int $year = 0;

    #[Url]
    public int $month = 0;

    public function mount(): void
    {
        // #region agent log
        $logPath = storage_path('logs/debug-15b0e0.log');
        $payload = [
            'sessionId' => '15b0e0',
            'runId' => 'post-fix',
            'hypothesisId' => 'A',
            'location' => 'CalendarMonth.php:mount:entry',
            'message' => 'mount entry with defaults',
            'data' => [
                'year' => $this->year,
                'month' => $this->month,
                'queryYear' => request()->query('year'),
                'queryMonth' => request()->query('month'),
            ],
            'timestamp' => (int) (microtime(true) * 1000),
        ];
        @file_put_contents($logPath, json_encode($payload)."\n", FILE_APPEND);
        // #endregion

        $now = now();
        if ($this->year < 1) {
            $this->year = $now->year;
        }
        if ($this->month < 1 || $this->month > 12) {
            $this->month = $now->month;
        }

        // #region agent log
        @file_put_contents($logPath, json_encode([
            'sessionId' => '15b0e0',
            'runId' => 'post-fix',
            'hypothesisId' => 'A',
            'location' => 'CalendarMonth.php:mount:exit',
            'message' => 'mount completed',
            'data' => ['year' => $this->year, 'month' => $this->month],
            'timestamp' => (int) (microtime(true) * 1000),
        ])."\n", FILE_APPEND);
        // #endregion
    }

    public function previousMonth(): void
    {
        $cursor = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $cursor->year;
        $this->month = $cursor->month;
    }

    public function nextMonth(): void
    {
        $cursor = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $cursor->year;
        $this->month = $cursor->month;
    }

    public function goToday(): void
    {
        $now = now();
        $this->year = $now->year;
        $this->month = $now->month;
    }

    public function render(AppointmentService $appointments)
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // Grade começa na segunda-feira
        $gridStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $items = $appointments->between($gridStart, $gridEnd->copy()->addSecond());

        $byDay = [];
        foreach ($items as $item) {
            $day = $item->starts_at->copy()->startOfDay();
            $end = $item->ends_at->copy()->startOfDay();

            while ($day <= $end && $day <= $gridEnd) {
                if ($day >= $gridStart) {
                    $key = $day->toDateString();
                    $byDay[$key] ??= [];
                    $byDay[$key][] = $item;
                }
                $day->addDay();
            }
        }

        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->toDateString();
                $week[] = [
                    'date' => $cursor->copy(),
                    'inMonth' => $cursor->month === $this->month,
                    'isToday' => $cursor->isToday(),
                    'appointments' => $byDay[$key] ?? [],
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return view('livewire.calendar.month', [
            'title' => $startOfMonth->translatedFormat('F Y'),
            'weeks' => $weeks,
            'weekdays' => ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        ]);
    }
}
