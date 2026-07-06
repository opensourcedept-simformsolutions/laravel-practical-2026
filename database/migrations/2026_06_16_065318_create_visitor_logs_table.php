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
        if (!Schema::hasTable('visitor_logs')) {
            Schema::create('visitor_logs', function (Blueprint $table) {
                $table->id();

                $table->foreignId('visitor_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('flat_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('gatekeeper_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->string('purpose');

                $table->timestamp('entry_time')->nullable();
                $table->timestamp('exit_time')->nullable();

                $table->enum('status', [
                    'accepted',
                    'pending',
                    'entered',
                    'exited',
                    'cancelled',
                ])->default('pending');

                $table->string('photo_path')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
    
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
