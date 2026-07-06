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
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $table->enum('status', [
                    'accepted',
                    'pending',
                    'pending_approval',
                    'approved',
                    'rejected',
                    'entered',
                    'exited',
                    'cancelled',
                    'expired',
                ])->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $table->enum('status', [
                    'accepted',
                    'pending',
                    'pending_approval',
                    'approved',
                    'rejected',
                    'entered',
                    'exited',
                    'cancelled',
                ])->default('pending')->change();
            });
        }
    }
};
