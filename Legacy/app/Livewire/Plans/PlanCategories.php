<?php

namespace App\Livewire\Plans;

use App\Services\PlanCategoryService;
use Livewire\Component;
use Throwable;

class PlanCategories extends Component
{
    public string $name = '';

    public function add(PlanCategoryService $categories): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        try {
            $categories->create($this->name);
            $this->name = '';
            session()->flash('success', 'Categoria adicionada.');
        } catch (Throwable $e) {
            $this->addError('name', $e->getMessage());
        }
    }

    public function delete(int $id, PlanCategoryService $categories): void
    {
        try {
            $categories->delete($categories->find($id));
            session()->flash('success', 'Categoria removida.');
        } catch (Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(PlanCategoryService $categories)
    {
        return view('livewire.plans.categories', [
            'categories' => $categories->list(),
        ]);
    }
}
