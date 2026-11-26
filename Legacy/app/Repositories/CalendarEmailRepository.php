<?php

namespace App\Repositories;

use App\Models\CalendarEmail;
use Illuminate\Database\Eloquent\Collection;

class CalendarEmailRepository
{
    public function allOrdered(): Collection
    {
        return CalendarEmail::query()
            ->orderByDesc('is_active')
            ->orderBy('email')
            ->get();
    }

    public function activeEmails(): Collection
    {
        return CalendarEmail::query()
            ->where('is_active', true)
            ->orderBy('email')
            ->get();
    }

    public function find(int $id): CalendarEmail
    {
        return CalendarEmail::query()->findOrFail($id);
    }

    public function create(array $data): CalendarEmail
    {
        return CalendarEmail::query()->create($data);
    }

    public function update(CalendarEmail $email, array $data): CalendarEmail
    {
        $email->update($data);

        return $email->refresh();
    }

    public function delete(CalendarEmail $email): void
    {
        $email->delete();
    }
}
