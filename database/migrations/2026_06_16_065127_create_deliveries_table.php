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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('flat_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('resident_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('vendor');
            $table->text('package_details');

            $table->enum('status', [
                'received',
                'delivered',
            ])->default('received');

            $table->timestamp('received_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
