<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Services\GoogleCalendarExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointments,
        private readonly GoogleCalendarExportService $google,
    ) {}

    public function index(): View
    {
        return view('calendar.index');
    }

    public function create(): View
    {
        return view('calendar.create');
    }

    public function emails(): View
    {
        return view('calendar.emails');
    }

    public function show(Appointment $appointment): View
    {
        $item = $this->appointments->find($appointment->id);

        return view('calendar.show', [
            'appointment' => $item,
            'googleUrl' => $this->google->templateUrl($item),
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        return view('calendar.edit', [
            'appointment' => $this->appointments->find($appointment->id),
        ]);
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->appointments->delete($appointment);

        return redirect()
            ->route('calendar.index')
            ->with('success', 'Compromisso removido.');
    }

    public function ics(Appointment $appointment): Response
    {
        $item = $this->appointments->find($appointment->id);
        $body = $this->google->ics($item);

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$this->google->icsFilename($item).'"',
        ]);
    }
}
