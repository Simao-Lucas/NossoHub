<?php

namespace App\Http\Requests\Plan;

use App\Enums\PlanCategory;
use App\Enums\PlanPriority;
use App\Enums\PlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanItemRequest extends FormRequest
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
            'category' => ['required', Rule::enum(PlanCategory::class)],
            'priority' => ['required', Rule::enum(PlanPriority::class)],
            'status' => ['required', Rule::enum(PlanStatus::class)],
            'link' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
