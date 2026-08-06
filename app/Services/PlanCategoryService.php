<?php

namespace App\Services;

use App\Models\PlanCategory;
use App\Repositories\PlanCategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class PlanCategoryService
{
    public function __construct(
        private readonly PlanCategoryRepository $categories,
    ) {}

    public function list(): Collection
    {
        return $this->categories->allOrdered();
    }

    public function options(): array
    {
        return $this->categories->options();
    }

    public function find(int $id): PlanCategory
    {
        return $this->categories->find($id);
    }

    public function create(string $name): PlanCategory
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('Informe o nome da categoria.');
        }

        return $this->categories->create([
            'name' => $name,
            'slug' => PlanCategory::uniqueSlug($name),
        ]);
    }

    public function delete(PlanCategory $category): void
    {
        if ($this->categories->plansCount($category) > 0) {
            throw new InvalidArgumentException(
                'Não é possível excluir: existem planos usando esta categoria.'
            );
        }

        $this->categories->delete($category);
    }
}
