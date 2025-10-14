<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemberStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_code',
        'name',
        'phone',
        'email',
        'last_check_in',
        'total_visits',
        'exp_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => MemberStatus::class,
            'exp_date' => 'date',
            'last_check_in' => 'datetime',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class)->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MemberStatus::ACTIVE)
            ->where('exp_date', '>=', Carbon::today()->toDateString());
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', MemberStatus::INACTIVE);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('exp_date', '<', Carbon::today()->toDateString());
    }

    public function scopeActiveOrExpired(Builder $query): Builder
    {
        return $query->where('status', MemberStatus::ACTIVE)
            ->where('exp_date', '<', Carbon::today()->toDateString());
    }

    public function isExpired(): bool
    {
        return $this->exp_date < Carbon::today()->toDateString();
    }

    public function isActive(): bool
    {
        return $this->status === MemberStatus::ACTIVE && ! $this->isExpired();
    }
}
