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
        if (! Schema::hasTable('residents')) {
            Schema::create('residents', function (Blueprint $table) {
                $table->id();

                $table->foreignId('user_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('flat_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->enum('resident_type', ['owner', 'tenant']);

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
        Schema::dropIfExists('residents');
    }
};
