<?php

declare(strict_types=1);

namespace App\Enums;

enum ShiftType: string
{
    case MORNING = 'MORNING';
    case EVENING = 'EVENING';

    public function label(): string
    {
        return match ($this) {
            self::MORNING => 'Pagi',
            self::EVENING => 'Sore',
        };
    }
}
