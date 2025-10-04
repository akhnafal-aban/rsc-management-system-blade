<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'amount',
        'method',
        'notes',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
