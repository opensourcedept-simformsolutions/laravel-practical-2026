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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('resident_id')
                ->constrained('residents')
                ->cascadeOnDelete();
                
            $table->foreignId('receiver_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
                
            $table->string('type'); 
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])
                ->default('pending');
                
            $table->string('gateway_payment_id')->nullable(); 
            $table->string('gateway_order_id')->nullable(); 
            $table->string('gateway_signature')->nullable();  
            
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
