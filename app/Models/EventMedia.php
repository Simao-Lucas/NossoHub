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
        'immich_asset_id',
        'type',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'sort_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
