<?php

namespace App\Services;

use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class AppointmentService
{
    public function __construct(
        private readonly AppointmentRepository $appointments,
    ) {}

    public function find(int $id): Appointment
    {
        return $this->appointments->find($id);
    }

    public function between(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->appointments->between($from, $to);
    }

    public function create(array $payload): Appointment
    {
        $this->assertDateRange($payload);

        return $this->appointments->create($payload);
    }

    public function update(Appointment $appointment, array $payload): Appointment
    {
        $merged = array_merge($appointment->toArray(), $payload);
        $this->assertDateRange($merged);

        return $this->appointments->update($appointment, $payload);
    }

    public function delete(Appointment $appointment): void
    {
        $this->appointments->delete($appointment);
    }

    private function assertDateRange(array $payload): void
    {
        $start = $payload['starts_at'] ?? null;
        $end = $payload['ends_at'] ?? null;

        if ($start && $end && $end < $start) {
            throw new InvalidArgumentException('O término deve ser depois do início.');
        }
    }
}
