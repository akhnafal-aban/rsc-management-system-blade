<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Index untuk filter tanggal check-in
            $table->index('check_in_time');

            // Index untuk filter status check-out
            $table->index('check_out_time');

            // Composite index untuk query check-in hari ini (member_id, check_in_time, check_out_time)
            $table->index(['member_id', 'check_in_time', 'check_out_time'], 'idx_member_checkin_status');

            // Index untuk filter berdasarkan tanggal check-in dan status
            $table->index(['check_in_time', 'check_out_time'], 'idx_checkin_status');
        });

        Schema::table('members', function (Blueprint $table) {
            // Index untuk filter status member
            $table->index('status');

            // Index untuk filter tanggal expired
            $table->index('exp_date');

            // Composite index untuk scope active() - status ACTIVE dan exp_date >= today
            $table->index(['status', 'exp_date'], 'idx_status_exp_date');

            // Index untuk search member_code dan name
            $table->index('member_code');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['check_in_time']);
            $table->dropIndex(['check_out_time']);
            $table->dropIndex('idx_member_checkin_status');
            $table->dropIndex('idx_checkin_status');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['exp_date']);
            $table->dropIndex('idx_status_exp_date');
            $table->dropIndex(['member_code']);
            $table->dropIndex(['name']);
        });
    }
};
