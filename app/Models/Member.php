<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MemberStatus;

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



    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
