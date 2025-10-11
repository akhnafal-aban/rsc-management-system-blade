<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key constraint sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('memberships')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil semua member yang eksis
        $memberIds = DB::table('members')->pluck('id');

        $memberships = [];

        foreach ($memberIds as $memberId) {
            // Pola tanggal mulai agar tidak acak total
            $offsetDays = floor($memberId / 10);
            $start = Carbon::now()->subDays($offsetDays)->startOfDay();

            // Lama membership bervariasi
            $duration = match (true) {
                $memberId % 15 === 0 => 12,
                $memberId % 7 === 0 => 6,
                $memberId % 3 === 0 => 3,
                default => 1,
            };

            $memberships[] = [
                'member_id'       => $memberId,
                'start_date'      => $start->format('Y-m-d'),
                'end_date'        => $start->copy()->addMonths($duration)->format('Y-m-d'),
                'duration_months' => $duration,
                'created_at'      => now(),
            ];
        }

        DB::table('memberships')->insert($memberships);
    }
}
