<?php

namespace App\Enums;

enum MediaType: string
{
    case Photo = 'photo';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Foto',
            self::Video => 'Vídeo',
        };
    }
}
