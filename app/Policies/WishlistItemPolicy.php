<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WishlistItem;

class WishlistItemPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, WishlistItem $wishlistItem): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, WishlistItem $wishlistItem): bool
    {
        return true;
    }

    public function delete(?User $user, WishlistItem $wishlistItem): bool
    {
        return true;
    }
}
