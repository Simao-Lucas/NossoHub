<?php

namespace App\Services;

use App\Models\WishlistItem;
use App\Repositories\WishlistRepository;
use Illuminate\Database\Eloquent\Collection;

class WishlistService
{
    public function __construct(
        private readonly WishlistRepository $wishlist,
    ) {}

    /**
     * @param  array{category?: string|null, status?: string|null, priority?: string|null, search?: string|null}  $filters
     */
    public function list(array $filters = []): Collection
    {
        return $this->wishlist->filtered($filters);
    }

    public function find(int $id): WishlistItem
    {
        return $this->wishlist->find($id);
    }

    public function create(array $payload): WishlistItem
    {
        return $this->wishlist->create($payload);
    }

    public function update(WishlistItem $item, array $payload): WishlistItem
    {
        return $this->wishlist->update($item, $payload);
    }

    public function delete(WishlistItem $item): void
    {
        $this->wishlist->delete($item);
    }
}
