<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    
    {
        if (!Schema::hasTable('wings')) {
            Schema::create('wings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('society_id')->constrained()->cascadeOnDelete();
                $table->string('name', 50);
                $table->integer('total_floors')->default(1);
                $table->integer('flats_per_floor')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['society_id', 'name'], 'wings_society_name_unique');
            });

            // add wing_id to flats and migrate existing wing values into wings table
            Schema::table('flats', function (Blueprint $table) {
                $table->unsignedBigInteger('wing_id')->nullable()->after('id');
            });

            // migrate existing wings into new table and link
            $existing = DB::table('flats')
                ->select('society_id', 'wing')
                ->distinct()
                ->get();

            foreach ($existing as $row) {
                if (empty($row->wing)) {
                    continue;
                }

                $wingId = DB::table('wings')
                    ->where('society_id', $row->society_id)
                    ->where('name', $row->wing)
                    ->value('id');

                if (! $wingId) {
                    $wingId = DB::table('wings')->insertGetId([
                        'society_id' => $row->society_id,
                        'name' => strtoupper($row->wing),
                        'total_floors' => 1,
                        'flats_per_floor' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('flats')
                    ->where('society_id', $row->society_id)
                    ->where('wing', $row->wing)
                    ->update(['wing_id' => $wingId]);
            }

            // make wing_id foreign key and add unique constraint per wing-floor-flat
            Schema::table('flats', function (Blueprint $table) {
                $table->foreign('wing_id')->references('id')->on('wings')->nullOnDelete();
                // keep existing unique key for backward compat; add new unique for wing_id
                $table->unique(['wing_id', 'floor', 'flat_number'], 'flats_wing_floor_flat_unique');
            });
    
        }
    }

    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $table->dropForeign(['wing_id']);
            $table->dropUnique('flats_wing_floor_flat_unique');
            $table->dropColumn('wing_id');
        });

        Schema::dropIfExists('wings');
    }
};
