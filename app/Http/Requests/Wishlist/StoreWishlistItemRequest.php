<?php

namespace App\Http\Requests\Wishlist;

use App\Enums\WishlistCategory;
use App\Enums\WishlistPriority;
use App\Enums\WishlistStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', Rule::enum(WishlistCategory::class)],
            'priority' => ['required', Rule::enum(WishlistPriority::class)],
            'status' => ['required', Rule::enum(WishlistStatus::class)],
            'link' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
