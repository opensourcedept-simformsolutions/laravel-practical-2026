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
        if (Schema::hasTable('visitor_logs') && ! Schema::hasColumn('visitor_logs', 'visit_date')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $table->date('visit_date')->nullable();
            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropColumn('visit_date');
        });
    }
};
