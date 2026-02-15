<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonMemberVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'amount',
        'payment_method',
        'notes',
        'created_by',
        'visit_time',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'visit_time' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('visit_time', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('visit_time', Carbon::today());
    }
}
