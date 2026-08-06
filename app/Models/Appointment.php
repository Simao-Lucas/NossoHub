<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentTransparency;
use App\Enums\AppointmentVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'summary',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'all_day',
        'timezone',
        'status',
        'visibility',
        'transparency',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
            'status' => AppointmentStatus::class,
            'visibility' => AppointmentVisibility::class,
            'transparency' => AppointmentTransparency::class,
        ];
    }
}
