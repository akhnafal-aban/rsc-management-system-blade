<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    public $timestamps = true;

    const UPDATED_AT = null;

    protected $fillable = [
        'member_id',
        'start_date',
        'end_date',
        'duration_months',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
