<?php

namespace App\Livewire\Calendar;

use App\Services\CalendarEmailService;
use Livewire\Component;
use Throwable;

class CalendarEmails extends Component
{
    public string $email = '';

    public string $label = '';

    public function add(CalendarEmailService $emails): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $emails->create([
                'email' => $this->email,
                'label' => $this->label,
                'is_active' => true,
            ]);
            $this->email = '';
            $this->label = '';
            session()->flash('success', 'E-mail vinculado.');
        } catch (Throwable $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    public function toggle(int $id, CalendarEmailService $emails): void
    {
        $emails->toggleActive($emails->find($id));
    }

    public function delete(int $id, CalendarEmailService $emails): void
    {
        $emails->delete($emails->find($id));
        session()->flash('success', 'E-mail removido.');
    }

    public function render(CalendarEmailService $emails)
    {
        return view('livewire.calendar.emails', [
            'emails' => $emails->list(),
        ]);
    }
}
