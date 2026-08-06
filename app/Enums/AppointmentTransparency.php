<?php

namespace App\Enums;

enum AppointmentTransparency: string
{
    case Opaque = 'opaque';
    case Transparent = 'transparent';

    public function label(): string
    {
        return match ($this) {
            self::Opaque => 'Ocupado',
            self::Transparent => 'Livre',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
