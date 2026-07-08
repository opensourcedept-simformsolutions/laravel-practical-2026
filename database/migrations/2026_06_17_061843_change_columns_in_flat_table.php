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
        if (Schema::hasTable('flats') && ! Schema::hasColumn('flats', 'society_id')) {
            Schema::table('flats', function (Blueprint $table) {
                $table->foreignId('society_id')
                    ->constrained('societies')
                    ->cascadeOnDelete()
                    ->after('id');
                $table->string('wing', 5)->change();
                $table->integer('floor')->change();
                $table->integer('flat_number')->change();
                $table->unique(
                    ['society_id', 'wing', 'floor', 'flat_number'],
                    'flats_society_wing_floor_flat_unique'
                );
            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $table->dropUnique('flats_society_wing_floor_flat_unique');
            $table->dropConstrainedForeignId('society_id');

            $table->string('wing')->change();
            $table->string('floor')->change();
            $table->string('flat_number')->change();
        });
    }
};
