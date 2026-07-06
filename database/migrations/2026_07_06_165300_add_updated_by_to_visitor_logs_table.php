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
        if (Schema::hasTable('visitor_logs') && !Schema::hasColumn('visitor_logs', 'updated_by')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('approved_by');
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('visitor_logs') && Schema::hasColumn('visitor_logs', 'updated_by')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            });
        }
    }
};
