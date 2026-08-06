<?php

namespace App\Enums;

enum AppointmentVisibility: string
{
    case Default = 'default';
    case Public = 'public';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Padrão',
            self::Public => 'Público',
            self::Private => 'Privado',
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
