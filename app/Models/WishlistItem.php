<?php

namespace App\Models;

use App\Enums\WishlistCategory;
use App\Enums\WishlistPriority;
use App\Enums\WishlistStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
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
            'category' => WishlistCategory::class,
            'priority' => WishlistPriority::class,
            'status' => WishlistStatus::class,
        ];
    }
}
