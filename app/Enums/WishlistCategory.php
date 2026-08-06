<?php

namespace App\Enums;

enum WishlistCategory: string
{
    case Restaurant = 'restaurant';
    case Travel = 'travel';
    case Movie = 'movie';
    case Gift = 'gift';
    case Experience = 'experience';

    public function label(): string
    {
        return match ($this) {
            self::Restaurant => 'Restaurante',
            self::Travel => 'Viagem',
            self::Movie => 'Filme',
            self::Gift => 'Presente',
            self::Experience => 'Experiência',
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
