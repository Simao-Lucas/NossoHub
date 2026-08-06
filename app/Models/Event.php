<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'date',
        ];
    }

    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class)->orderBy('sort_order');
    }

    public function photos(): HasMany
    {
        return $this->media()->where('type', MediaType::Photo->value);
    }

    public function videos(): HasMany
    {
        return $this->media()->where('type', MediaType::Video->value);
    }

    public function shortDescription(int $limit = 140): string
    {
        if (! filled($this->description)) {
            return '';
        }

        return str($this->description)->limit($limit)->toString();
    }
}
