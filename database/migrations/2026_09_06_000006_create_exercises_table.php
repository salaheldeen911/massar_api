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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->nullable()->constrained('centers')->cascadeOnDelete();
            $table->foreignId('therapist_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('default_sets')->default(3);
            $table->unsignedInteger('default_repeats')->default(12);
            $table->unsignedInteger('default_duration')->default(90);
            $table->text('therapist_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
