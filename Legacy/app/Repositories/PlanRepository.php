<?php

namespace App\Repositories;

use App\Models\PlanItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PlanRepository
{
    /**
     * @param  array{category?: int|string|null, status?: string|null, priority?: string|null, search?: string|null}  $filters
     */
    public function filtered(array $filters = []): Collection
    {
        return $this->query($filters)
            ->with('category')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderByDesc('updated_at')
            ->get();
    }

    public function find(int $id): PlanItem
    {
        return PlanItem::query()
            ->with('category')
            ->findOrFail($id);
    }

    public function create(array $data): PlanItem
    {
        return PlanItem::query()->create($data);
    }

    public function update(PlanItem $item, array $data): PlanItem
    {
        $item->update($data);

        return $item->refresh()->load('category');
    }

    public function delete(PlanItem $item): void
    {
        $item->delete();
    }

    /**
     * @param  array{category?: int|string|null, status?: string|null, priority?: string|null, search?: string|null}  $filters
     */
    private function query(array $filters): Builder
    {
        return PlanItem::query()
            ->when($filters['category'] ?? null, fn (Builder $q, $category) => $q->where('plan_category_id', $category))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $q, string $priority) => $q->where('priority', $priority))
            ->when($filters['search'] ?? null, function (Builder $q, string $search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            });
    }
}
