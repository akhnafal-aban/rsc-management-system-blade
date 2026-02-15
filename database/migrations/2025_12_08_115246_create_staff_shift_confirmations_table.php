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
        Schema::create('staff_shift_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_schedule_id')->nullable()->constrained()->onDelete('set null');
            $table->date('confirmation_date');
            $table->enum('shift_type', ['MORNING', 'EVENING']);
            $table->timestamp('confirmed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'confirmation_date']);
            $table->index('confirmation_date');
            $table->index('shift_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_shift_confirmations');
    }
};
