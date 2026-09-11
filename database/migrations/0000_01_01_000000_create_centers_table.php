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
            $table->string('email')->nullable();
            $table->string('specialty', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->unsignedInteger('therapists_count')->default(1);
            $table->unsignedInteger('branches_count')->default(1);
            $table->string('referral_source')->nullable();
            $table->boolean('terms_accepted')->default(true);
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();

            // Trial & Subscriptions
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('subscription_status')->default('trialing');

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
