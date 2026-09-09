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
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('center_id')->constrained('centers')->cascadeOnDelete();
            $table->foreignId('therapist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('birth_date');
            $table->unsignedInteger('current_week')->default(1);
            $table->text('patient_history')->nullable();
            $table->text('chief_complain')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('special_tests_notes')->nullable();
            $table->text('objective_findings')->nullable();
            $table->timestamps();

            $table->index(['therapist_id', 'center_id'], 'idx_patient_therapist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
