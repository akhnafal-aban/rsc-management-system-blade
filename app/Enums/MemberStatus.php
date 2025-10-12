<?php

declare(strict_types=1);

namespace App\Enums;

enum MemberStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
}
