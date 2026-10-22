<?php

namespace App\Enums;

enum Validity: string
{
    case Lifetime = 'lifetime';
    case Temporary = 'temporary';

    public function label(): string
    {
        return match ($this) {
            self::Lifetime => 'Lifetime',
            self::Temporary => 'Sementara',
        };
    }
}
