<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'command',
        'status',
        'message',
        'member_name',
        'checkout_at',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'checkout_at' => 'datetime',
            'is_read' => 'boolean',
        ];
    }
}
