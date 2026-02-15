<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'member_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function createdAttendances()
    {
        return $this->hasMany(Attendance::class, 'created_by');
    }

    public function updatedAttendances()
    {
        return $this->hasMany(Attendance::class, 'updated_by');
    }

    public function staffSchedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function shiftConfirmations()
    {
        return $this->hasMany(StaffShiftConfirmation::class);
    }

    public function createdNonMemberVisits()
    {
        return $this->hasMany(NonMemberVisit::class, 'created_by');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
