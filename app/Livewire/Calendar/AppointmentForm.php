<?php

namespace App\Livewire\Calendar;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentTransparency;
use App\Enums\AppointmentVisibility;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Livewire\Component;
use Throwable;

class AppointmentForm extends Component
{
    public ?int $appointmentId = null;

    public string $summary = '';

    public string $description = '';

    public string $location = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public bool $all_day = false;

    public string $timezone = 'America/Sao_Paulo';

    public string $status = 'confirmed';

    public string $visibility = 'default';

    public string $transparency = 'opaque';

    public function mount(?Appointment $appointment = null): void
    {
        if ($appointment?->exists) {
            $item = app(AppointmentService::class)->find($appointment->id);
            $this->appointmentId = $item->id;
            $this->summary = $item->summary;
            $this->description = (string) $item->description;
            $this->location = (string) $item->location;
            $this->all_day = $item->all_day;
            $this->timezone = $item->timezone;
            $this->status = $item->status->value;
            $this->visibility = $item->visibility->value;
            $this->transparency = $item->transparency->value;
            $this->starts_at = $item->all_day
                ? $item->starts_at->format('Y-m-d')
                : $item->starts_at->format('Y-m-d\TH:i');
            $this->ends_at = $item->all_day
                ? $item->ends_at->format('Y-m-d')
                : $item->ends_at->format('Y-m-d\TH:i');
        } else {
            $start = now()->addHour()->startOfHour();
            $this->starts_at = $start->format('Y-m-d\TH:i');
            $this->ends_at = $start->copy()->addHour()->format('Y-m-d\TH:i');
            $this->timezone = config('app.timezone', 'America/Sao_Paulo');
        }
    }

    public function updatedAllDay(bool $value): void
    {
        if ($value) {
            $this->starts_at = Carbon::parse($this->starts_at)->format('Y-m-d');
            $this->ends_at = Carbon::parse($this->ends_at ?: $this->starts_at)->format('Y-m-d');
        } else {
            $start = Carbon::parse($this->starts_at)->setTime(9, 0);
            $end = Carbon::parse($this->ends_at ?: $this->starts_at)->setTime(10, 0);
            $this->starts_at = $start->format('Y-m-d\TH:i');
            $this->ends_at = $end->format('Y-m-d\TH:i');
        }
    }

    public function save(AppointmentService $appointments)
    {
        $this->validate([
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date'],
            'all_day' => ['boolean'],
            'timezone' => ['required', 'string', 'max:64'],
            'status' => ['required', 'in:'.implode(',', array_column(AppointmentStatus::cases(), 'value'))],
            'visibility' => ['required', 'in:'.implode(',', array_column(AppointmentVisibility::cases(), 'value'))],
            'transparency' => ['required', 'in:'.implode(',', array_column(AppointmentTransparency::cases(), 'value'))],
        ]);

        $starts = Carbon::parse($this->starts_at);
        $ends = Carbon::parse($this->ends_at);

        if ($this->all_day) {
            $starts = $starts->startOfDay();
            $ends = $ends->startOfDay();
        }

        $payload = [
            'summary' => $this->summary,
            'description' => $this->description !== '' ? $this->description : null,
            'location' => $this->location !== '' ? $this->location : null,
            'starts_at' => $starts,
            'ends_at' => $ends,
            'all_day' => $this->all_day,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'transparency' => $this->transparency,
        ];

        try {
            if ($this->appointmentId) {
                $item = $appointments->update($appointments->find($this->appointmentId), $payload);
                session()->flash('success', 'Compromisso atualizado.');
            } else {
                $item = $appointments->create($payload);
                session()->flash('success', 'Compromisso criado.');
            }
        } catch (Throwable $e) {
            $this->addError('ends_at', $e->getMessage());

            return;
        }

        return redirect()->route('calendar.show', $item);
    }

    public function render()
    {
        return view('livewire.calendar.form', [
            'statuses' => AppointmentStatus::options(),
            'visibilities' => AppointmentVisibility::options(),
            'transparencies' => AppointmentTransparency::options(),
        ]);
    }
}
