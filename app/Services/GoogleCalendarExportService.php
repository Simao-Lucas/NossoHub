<?php

namespace App\Services;

use App\Models\Appointment;
use App\Repositories\CalendarEmailRepository;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GoogleCalendarExportService
{
    public function __construct(
        private readonly CalendarEmailRepository $emails,
    ) {}

    public function templateUrl(Appointment $appointment): string
    {
        $params = [
            'action' => 'TEMPLATE',
            'text' => $appointment->summary,
            'details' => (string) $appointment->description,
            'location' => (string) $appointment->location,
            'dates' => $this->googleDates($appointment),
        ];

        $attendees = $this->emails->activeEmails()->pluck('email')->filter()->implode(',');
        if ($attendees !== '') {
            $params['add'] = $attendees;
        }

        return 'https://calendar.google.com/calendar/render?'.http_build_query($params);
    }

    public function ics(Appointment $appointment): string
    {
        $uid = 'appointment-'.$appointment->id.'@'.parse_url((string) config('app.url'), PHP_URL_HOST);
        $stamp = Carbon::now('UTC')->format('Ymd\THis\Z');
        $summary = $this->escapeIcs((string) $appointment->summary);
        $description = $this->escapeIcs((string) $appointment->description);
        $location = $this->escapeIcs((string) $appointment->location);
        $status = strtoupper($appointment->status->value);
        $transp = strtoupper($appointment->transparency->value);
        $class = match ($appointment->visibility->value) {
            'public' => 'PUBLIC',
            'private' => 'PRIVATE',
            default => 'PUBLIC',
        };

        if ($appointment->all_day) {
            $dtStart = 'DTSTART;VALUE=DATE:'.$appointment->starts_at->format('Ymd');
            // ICS all-day end is exclusive
            $dtEnd = 'DTEND;VALUE=DATE:'.$appointment->ends_at->copy()->addDay()->format('Ymd');
        } else {
            $start = $appointment->starts_at->copy()->timezone('UTC');
            $end = $appointment->ends_at->copy()->timezone('UTC');
            $dtStart = 'DTSTART:'.$start->format('Ymd\THis\Z');
            $dtEnd = 'DTEND:'.$end->format('Ymd\THis\Z');
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Nosso Hub//Calendar//PT',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$stamp,
            $dtStart,
            $dtEnd,
            'SUMMARY:'.$summary,
            'DESCRIPTION:'.$description,
            'LOCATION:'.$location,
            'STATUS:'.$status,
            'TRANSP:'.$transp,
            'CLASS:'.$class,
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    public function icsFilename(Appointment $appointment): string
    {
        $slug = Str::slug($appointment->summary) ?: 'compromisso';

        return $slug.'-'.$appointment->id.'.ics';
    }

    private function googleDates(Appointment $appointment): string
    {
        if ($appointment->all_day) {
            $start = $appointment->starts_at->format('Ymd');
            // Google all-day end is exclusive
            $end = $appointment->ends_at->copy()->addDay()->format('Ymd');

            return $start.'/'.$end;
        }

        $start = $appointment->starts_at->copy()->timezone('UTC')->format('Ymd\THis\Z');
        $end = $appointment->ends_at->copy()->timezone('UTC')->format('Ymd\THis\Z');

        return $start.'/'.$end;
    }

    private function escapeIcs(string $value): string
    {
        return str_replace(
            ["\\", ';', ',', "\n", "\r"],
            ['\\\\', '\;', '\,', '\\n', ''],
            $value,
        );
    }
}
