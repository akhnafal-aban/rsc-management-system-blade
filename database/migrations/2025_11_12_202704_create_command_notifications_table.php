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
        Schema::create('command_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->string('status', 20);
            $table->text('message')->nullable();
            $table->string('member_name')->nullable();
            $table->dateTime('checkout_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('command_notifications');
    }
};
