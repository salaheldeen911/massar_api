<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->foreignId('center_id')->nullable()->after('patient_id')->constrained('centers')->cascadeOnDelete();
        });

        Schema::table('nutrition_plans', function (Blueprint $table) {
            $table->foreignId('center_id')->nullable()->after('patient_id')->constrained('centers')->cascadeOnDelete();
        });

        Schema::table('patient_exercises', function (Blueprint $table) {
            $table->foreignId('center_id')->nullable()->after('patient_id')->constrained('centers')->cascadeOnDelete();
        });

        Schema::table('patient_exercise_logs', function (Blueprint $table) {
            $table->foreignId('center_id')->nullable()->after('patient_id')->constrained('centers')->cascadeOnDelete();
        });

        // Backfill existing data with patient center_id using DB-agnostic SQL
        DB::statement("UPDATE treatment_plans SET center_id = (SELECT center_id FROM users WHERE users.id = treatment_plans.patient_id) WHERE center_id IS NULL");
        DB::statement("UPDATE nutrition_plans SET center_id = (SELECT center_id FROM users WHERE users.id = nutrition_plans.patient_id) WHERE center_id IS NULL");
        DB::statement("UPDATE patient_exercises SET center_id = (SELECT center_id FROM users WHERE users.id = patient_exercises.patient_id) WHERE center_id IS NULL");
        DB::statement("UPDATE patient_exercise_logs SET center_id = (SELECT center_id FROM users WHERE users.id = patient_exercise_logs.patient_id) WHERE center_id IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_exercise_logs', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropColumn('center_id');
        });

        Schema::table('patient_exercises', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropColumn('center_id');
        });

        Schema::table('nutrition_plans', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropColumn('center_id');
        });

        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropColumn('center_id');
        });
    }
};