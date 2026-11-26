<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventMedia extends Model
{
    use HasFactory;

    protected $table = 'event_media';

    protected $fillable = [
        'event_id',
        'path',
        'disk',
        'original_name',
        'mime_type',
        'size',
        'type',
        'sort_order',
    ];

    protected $appends = [
        'url',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'sort_order' => 'integer',
            'size' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getUrlAttribute(): string
    {
        return route('media.show', $this, absolute: false);
    }
}
