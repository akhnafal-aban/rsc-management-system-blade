<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ShiftType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffShiftConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'staff_schedule_id',
        'confirmation_date',
        'shift_type',
        'confirmed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'confirmation_date' => 'date',
            'shift_type' => ShiftType::class,
            'confirmed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staffSchedule()
    {
        return $this->belongsTo(StaffSchedule::class);
    }

    public function scopeToday(Builder $query, ?int $userId = null): Builder
    {
        $query = $query->whereDate('confirmation_date', Carbon::today());

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query;
    }

    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('confirmation_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }
}
