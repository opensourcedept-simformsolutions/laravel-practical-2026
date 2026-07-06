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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('society_id');

                $table->foreignId('society_id')
                    ->nullable()
                    ->constrained('societies')
                    ->nullOnDelete();
            });
    
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('society_id');

            $table->foreignId('society_id')
                ->constrained('societies')
                ->cascadeOnDelete();
        });
    }
};
