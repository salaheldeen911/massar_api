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
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->string('category_letter', 5)->nullable();
            $table->timestamps();

            $table->index('category_letter', 'idx_diagnoses_category');
            $table->index('code', 'idx_diagnoses_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
