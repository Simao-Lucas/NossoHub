<?php

namespace App\Repositories;

use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository
{
    /**
     * @param  array{category?: string|null, status?: string|null, priority?: string|null, search?: string|null}  $filters
     */
    public function filtered(array $filters = []): Collection
    {
        return $this->query($filters)
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderByDesc('updated_at')
            ->get();
    }

    public function find(int $id): WishlistItem
    {
        return WishlistItem::query()->findOrFail($id);
    }

    public function create(array $data): WishlistItem
    {
        return WishlistItem::query()->create($data);
    }

    public function update(WishlistItem $item, array $data): WishlistItem
    {
        $item->update($data);

        return $item->refresh();
    }

    public function delete(WishlistItem $item): void
    {
        $item->delete();
    }

    /**
     * @param  array{category?: string|null, status?: string|null, priority?: string|null, search?: string|null}  $filters
     */
    private function query(array $filters): Builder
    {
        return WishlistItem::query()
            ->when($filters['category'] ?? null, fn (Builder $q, string $category) => $q->where('category', $category))
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
