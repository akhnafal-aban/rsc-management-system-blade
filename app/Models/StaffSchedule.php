<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ShiftType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_date',
        'shift_type',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'shift_type' => ShiftType::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmations()
    {
        return $this->hasMany(StaffShiftConfirmation::class);
    }

    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('schedule_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    public function scopeByShiftType(Builder $query, ShiftType $shiftType): Builder
    {
        return $query->where('shift_type', $shiftType);
    }
}
