<?php

namespace App\Livewire\Plans;

use App\Enums\PlanCategory;
use App\Enums\PlanPriority;
use App\Enums\PlanStatus;
use App\Services\PlanService;
use Livewire\Attributes\Url;
use Livewire\Component;

class PlanIndex extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $formCategory = 'experience';

    public string $formPriority = 'medium';

    public string $formStatus = 'pending';

    public string $link = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->resetFormFields();
    }

    public function openCreate(): void
    {
        $this->resetFormFields();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function openEdit(int $id, PlanService $plans): void
    {
        $item = $plans->find($id);

        $this->editingId = $item->id;
        $this->title = $item->title;
        $this->description = (string) $item->description;
        $this->formCategory = $item->category->value;
        $this->formPriority = $item->priority->value;
        $this->formStatus = $item->status->value;
        $this->link = (string) $item->link;
        $this->notes = (string) $item->notes;
        $this->showForm = true;
    }

    public function save(PlanService $plans): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'formCategory' => ['required', 'in:'.implode(',', array_column(PlanCategory::cases(), 'value'))],
            'formPriority' => ['required', 'in:'.implode(',', array_column(PlanPriority::cases(), 'value'))],
            'formStatus' => ['required', 'in:'.implode(',', array_column(PlanStatus::cases(), 'value'))],
            'link' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'category' => $validated['formCategory'],
            'priority' => $validated['formPriority'],
            'status' => $validated['formStatus'],
            'link' => $validated['link'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->editingId) {
            $plans->update($plans->find($this->editingId), $payload);
            session()->flash('success', 'Item atualizado.');
        } else {
            $plans->create($payload);
            session()->flash('success', 'Item adicionado aos planos.');
        }

        $this->showForm = false;
        $this->resetFormFields();
    }

    public function delete(int $id, PlanService $plans): void
    {
        $plans->delete($plans->find($id));
        session()->flash('success', 'Item removido.');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetFormFields();
    }

    public function render(PlanService $plans)
    {
        return view('livewire.plans.index', [
            'items' => $plans->list([
                'search' => $this->search ?: null,
                'category' => $this->category ?: null,
                'status' => $this->status ?: null,
                'priority' => $this->priority ?: null,
            ]),
            'categories' => PlanCategory::options(),
            'priorities' => PlanPriority::options(),
            'statuses' => PlanStatus::options(),
        ]);
    }

    private function resetFormFields(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->formCategory = PlanCategory::Experience->value;
        $this->formPriority = PlanPriority::Medium->value;
        $this->formStatus = PlanStatus::Pending->value;
        $this->link = '';
        $this->notes = '';
        $this->resetValidation();
    }
}
