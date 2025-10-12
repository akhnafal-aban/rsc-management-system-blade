<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemberStatus;
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
}
