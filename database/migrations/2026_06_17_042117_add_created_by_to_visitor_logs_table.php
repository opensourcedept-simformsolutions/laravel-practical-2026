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
        if (Schema::hasTable('visitor_logs') && ! Schema::hasColumn('visitor_logs', 'created_by')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('flat_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
