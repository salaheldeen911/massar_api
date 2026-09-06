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
        Schema::create('centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('specialty', 100)->nullable();
            $table->string('country', 100)->default('Egypt');
            $table->string('city', 100)->default('Cairo');
            $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('pending');

            // Trial & Subscriptions
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->enum('subscription_status', ['trialing', 'active', 'past_due', 'canceled'])->default('trialing');

            // Social & Contact
            $table->string('facebook')->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();

            $table->timestamps();

            $table->index(['status', 'subscription_status'], 'idx_centers_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centers');
    }
};
