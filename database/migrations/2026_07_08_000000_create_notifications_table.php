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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type', 50);
                $table->string('title', 255);
                $table->text('message')->nullable();
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'read_at'], 'notifications_user_id_read_at_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
