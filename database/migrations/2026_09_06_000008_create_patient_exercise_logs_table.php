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
        Schema::create('patient_exercise_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_exercise_id')->constrained('patient_exercises')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('completed_sets')->default(0);
            $table->unsignedInteger('completed_repeats')->default(0);
            $table->unsignedInteger('duration_spent')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->date('logged_at');
            $table->timestamps();

            $table->index(['patient_id', 'logged_at'], 'idx_logs_patient_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_exercise_logs');
    }
};
