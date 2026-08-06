<?php

namespace App\Livewire\Plans;

use App\Enums\PlanCategory;
use App\Enums\PlanPriority;
use App\Enums\PlanStatus;
use App\Models\PlanItem;
use App\Services\PlanService;
use Livewire\Component;

class PlanForm extends Component
{
    public ?int $planId = null;

    public string $title = '';

    public string $description = '';

    public string $category = 'experience';

    public string $priority = 'medium';

    public string $status = 'pending';

    public string $link = '';

    public string $notes = '';

    public function mount(?PlanItem $planItem = null): void
    {
        if ($planItem?->exists) {
            $item = app(PlanService::class)->find($planItem->id);
            $this->planId = $item->id;
            $this->title = $item->title;
            $this->description = (string) $item->description;
            $this->category = $item->category->value;
            $this->priority = $item->priority->value;
            $this->status = $item->status->value;
            $this->link = (string) $item->link;
            $this->notes = (string) $item->notes;
        } else {
            $this->category = PlanCategory::Experience->value;
            $this->priority = PlanPriority::Medium->value;
            $this->status = PlanStatus::Pending->value;
        }
    }

    public function save(PlanService $plans)
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:'.implode(',', array_column(PlanCategory::cases(), 'value'))],
            'priority' => ['required', 'in:'.implode(',', array_column(PlanPriority::cases(), 'value'))],
            'status' => ['required', 'in:'.implode(',', array_column(PlanStatus::cases(), 'value'))],
            'link' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'link' => $validated['link'] !== '' ? $validated['link'] : null,
            'notes' => $validated['notes'] !== '' ? $validated['notes'] : null,
        ];

        if ($this->planId) {
            $item = $plans->update($plans->find($this->planId), $payload);
            session()->flash('success', 'Plano atualizado.');
        } else {
            $item = $plans->create($payload);
            session()->flash('success', 'Plano adicionado.');
        }

        return redirect()->route('plans.show', $item);
    }

    public function render()
    {
        return view('livewire.plans.form', [
            'categories' => PlanCategory::options(),
            'priorities' => PlanPriority::options(),
            'statuses' => PlanStatus::options(),
        ]);
    }
}
