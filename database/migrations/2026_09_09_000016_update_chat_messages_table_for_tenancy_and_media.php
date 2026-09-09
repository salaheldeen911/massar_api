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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('center_id')->after('id')->nullable()->constrained('centers')->cascadeOnDelete();
            $table->text('message')->nullable()->change();

            $table->index(['center_id', 'sender_id', 'receiver_id', 'created_at'], 'idx_chat_center_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_chat_center_lookup');
            $table->dropForeign(['center_id']);
            $table->dropColumn('center_id');
            $table->text('message')->nullable(false)->change();
        });
    }
};
