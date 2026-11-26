<?php

namespace App\Repositories;

use App\Models\Appointment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository
{
    public function find(int $id): Appointment
    {
        return Appointment::query()->findOrFail($id);
    }

    public function create(array $data): Appointment
    {
        return Appointment::query()->create($data);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        $appointment->update($data);

        return $appointment->refresh();
    }

    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }

    public function between(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Appointment::query()
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at')
            ->get();
    }

    public function upcoming(int $limit = 20): Collection
    {
        return Appointment::query()
            ->where('starts_at', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }
}
