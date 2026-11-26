<?php

namespace App\Livewire\Plans;

use App\Enums\PlanPriority;
use App\Enums\PlanStatus;
use App\Services\PlanCategoryService;
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

    public function render(PlanService $plans, PlanCategoryService $categories)
    {
        return view('livewire.plans.index', [
            'items' => $plans->list([
                'search' => $this->search ?: null,
                'category' => $this->category !== '' ? (int) $this->category : null,
                'status' => $this->status ?: null,
                'priority' => $this->priority ?: null,
            ]),
            'categories' => $categories->options(),
            'priorities' => PlanPriority::options(),
            'statuses' => PlanStatus::options(),
        ]);
    }
}
