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
}
