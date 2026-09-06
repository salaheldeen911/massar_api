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
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('therapist_id')->constrained('users')->cascadeOnDelete();
            $table->text('manual_therapy')->nullable();
            $table->date('manual_therapy_date')->nullable();
            $table->text('electrotherapy')->nullable();
            $table->date('electrotherapy_date')->nullable();
            $table->text('medications')->nullable();
            $table->text('goals')->nullable();
            $table->timestamps();

            $table->index('patient_id', 'idx_treatment_patient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_plans');
    }
};
