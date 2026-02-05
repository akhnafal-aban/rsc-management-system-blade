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

    /**
     * Mapping harga per bulan (sementara, sebelum ada setting dari admin)
     */
    public const PRICE_MAP = [
        1 => 135000,
        3 => 400000,
        6 => 750000,
        12 => 1400000,
    ];

    /**
     * Ambil harga berdasarkan durasi per bulan membership.
     */
    public static function getPriceForDuration(int $months): int
    {
        return static::PRICE_MAP[$months] ?? 0;
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
