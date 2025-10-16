<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'check_in_time',
        'check_out_time',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
        ];
    }

    /**
     * Default eager loading untuk relasi yang sering dipakai
     */
    protected $with = ['member'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope untuk filter attendance hari ini
     */
    public function scopeToday(Builder $query, ?string $date = null): Builder
    {
        $date = $date ?? Carbon::today()->format('Y-m-d');

        return $query->whereBetween('check_in_time', [
            Carbon::parse($date)->startOfDay(),
            Carbon::parse($date)->endOfDay(),
        ]);
    }

    /**
     * Scope untuk filter attendance yang belum check-out
     */
    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->whereNull('check_out_time');
    }

    /**
     * Scope untuk filter attendance yang sudah check-out
     */
    public function scopeCheckedOut(Builder $query): Builder
    {
        return $query->whereNotNull('check_out_time');
    }

    /**
     * Scope untuk filter attendance berdasarkan range tanggal
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('check_in_time', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    /**
     * Scope untuk filter attendance berdasarkan member ID
     */
    public function scopeByMember(Builder $query, int $memberId): Builder
    {
        return $query->where('member_id', $memberId);
    }
}
