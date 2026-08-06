<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wishlist\StoreWishlistItemRequest;
use App\Http\Requests\Wishlist\UpdateWishlistItemRequest;
use App\Models\WishlistItem;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlist,
    ) {}

    public function index(): View
    {
        return view('wishlist.index');
    }

    public function store(StoreWishlistItemRequest $request): RedirectResponse
    {
        $this->wishlist->create($request->validated());

        return redirect()
            ->route('wishlist.index')
            ->with('success', 'Item adicionado à wishlist.');
    }

    public function update(UpdateWishlistItemRequest $request, WishlistItem $wishlistItem): RedirectResponse
    {
        $this->wishlist->update($wishlistItem, $request->validated());

        return redirect()
            ->route('wishlist.index')
            ->with('success', 'Item atualizado.');
    }

    public function destroy(WishlistItem $wishlistItem): RedirectResponse
    {
        $this->wishlist->delete($wishlistItem);

        return redirect()
            ->route('wishlist.index')
            ->with('success', 'Item removido.');
    }
}
