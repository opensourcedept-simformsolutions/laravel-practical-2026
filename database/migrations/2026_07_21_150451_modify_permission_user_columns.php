<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission_user', function (Blueprint $table) {
            $table->string('assigned_by')->nullable()->change();

            $table->string('status')
                  ->default('active')
                  ->change();

            $table->timestamp('assigned_at')
                  ->nullable()
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('permission_user', function (Blueprint $table) {
            $table->string('assigned_by')->nullable(false)->change();

            $table->string('status')
                  ->default(null)
                  ->change();

            $table->timestamp('assigned_at')
                  ->nullable(false)
                  ->change();
        });
    }
};