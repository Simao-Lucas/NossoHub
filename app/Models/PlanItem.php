<?php

namespace App\Models;

use App\Enums\PlanCategory;
use App\Enums\PlanPriority;
use App\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'priority',
        'status',
        'link',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'category' => PlanCategory::class,
            'priority' => PlanPriority::class,
            'status' => PlanStatus::class,
        ];
    }
}
