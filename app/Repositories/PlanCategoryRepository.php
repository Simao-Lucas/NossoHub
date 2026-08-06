<?php

namespace App\Repositories;

use App\Models\PlanCategory;
use Illuminate\Database\Eloquent\Collection;

class PlanCategoryRepository
{
    public function allOrdered(): Collection
    {
        return PlanCategory::query()
            ->withCount('plans')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): PlanCategory
    {
        return PlanCategory::query()->findOrFail($id);
    }

    public function create(array $data): PlanCategory
    {
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = ((int) PlanCategory::query()->max('sort_order')) + 1;
        }

        return PlanCategory::query()->create($data);
    }

    public function delete(PlanCategory $category): void
    {
        $category->delete();
    }

    public function options(): array
    {
        return PlanCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function plansCount(PlanCategory $category): int
    {
        return (int) $category->plans()->count();
    }
}
