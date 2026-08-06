<?php

namespace App\Http\Requests\Event;

use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
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
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'cover_immich_asset_id' => ['nullable', 'string', 'max:64'],
            'media' => ['nullable', 'array'],
            'media.*.immich_asset_id' => ['required_with:media', 'string', 'max:64'],
            'media.*.type' => ['required_with:media', Rule::enum(MediaType::class)],
        ];
    }
}
