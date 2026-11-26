<?php

namespace App\Services;

use App\Models\PlanItem;
use App\Repositories\PlanRepository;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    public function __construct(
        private readonly PlanRepository $plans,
    ) {}

    /**
     * @param  array{category?: string|null, status?: string|null, priority?: string|null, search?: string|null}  $filters
     */
    public function list(array $filters = []): Collection
    {
        return $this->plans->filtered($filters);
    }

    public function find(int $id): PlanItem
    {
        return $this->plans->find($id);
    }

    public function create(array $payload): PlanItem
    {
        return $this->plans->create($payload);
    }

    public function update(PlanItem $item, array $payload): PlanItem
    {
        return $this->plans->update($item, $payload);
    }

    public function delete(PlanItem $item): void
    {
        $this->plans->delete($item);
    }
}
