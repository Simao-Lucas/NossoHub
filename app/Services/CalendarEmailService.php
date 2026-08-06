<?php

namespace App\Services;

use App\Models\CalendarEmail;
use App\Repositories\CalendarEmailRepository;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class CalendarEmailService
{
    public function __construct(
        private readonly CalendarEmailRepository $emails,
    ) {}

    public function list(): Collection
    {
        return $this->emails->allOrdered();
    }

    public function active(): Collection
    {
        return $this->emails->activeEmails();
    }

    public function find(int $id): CalendarEmail
    {
        return $this->emails->find($id);
    }

    public function create(array $payload): CalendarEmail
    {
        $email = strtolower(trim((string) ($payload['email'] ?? '')));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }

        try {
            return $this->emails->create([
                'email' => $email,
                'label' => filled($payload['label'] ?? null) ? trim((string) $payload['label']) : null,
                'is_active' => (bool) ($payload['is_active'] ?? true),
            ]);
        } catch (\Illuminate\Database\QueryException) {
            throw new InvalidArgumentException('Este e-mail já está vinculado.');
        }
    }

    public function update(CalendarEmail $email, array $payload): CalendarEmail
    {
        if (array_key_exists('email', $payload)) {
            $value = strtolower(trim((string) $payload['email']));
            if ($value === '' || ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Informe um e-mail válido.');
            }
            $payload['email'] = $value;
        }

        if (array_key_exists('label', $payload)) {
            $payload['label'] = filled($payload['label']) ? trim((string) $payload['label']) : null;
        }

        return $this->emails->update($email, $payload);
    }

    public function delete(CalendarEmail $email): void
    {
        $this->emails->delete($email);
    }

    public function toggleActive(CalendarEmail $email): CalendarEmail
    {
        return $this->emails->update($email, [
            'is_active' => ! $email->is_active,
        ]);
    }
}
