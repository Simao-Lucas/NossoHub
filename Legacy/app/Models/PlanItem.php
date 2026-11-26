<?php

namespace App\Models;

use App\Enums\PlanPriority;
use App\Enums\PlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'plan_category_id',
        'priority',
        'status',
        'link',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'priority' => PlanPriority::class,
            'status' => PlanStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlanCategory::class, 'plan_category_id');
    }
}
