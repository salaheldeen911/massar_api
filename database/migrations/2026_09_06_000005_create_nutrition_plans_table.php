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
        Schema::create('nutrition_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('therapist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('breakfast')->nullable();
            $table->text('lunch')->nullable();
            $table->text('dinner')->nullable();
            $table->text('snacks')->nullable();
            $table->text('supplements')->nullable();
            $table->text('foods_to_avoid')->nullable();
            $table->timestamps();

            $table->index('patient_id', 'idx_nutrition_patient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_plans');
    }
};
