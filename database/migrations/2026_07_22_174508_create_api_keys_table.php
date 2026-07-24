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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key', 64)->unique()->index();
            $table->string('secret_hash', 255);
            $table->string('status', 20)->default('active'); // active, suspended, revoked
            $table->json('scopes')->nullable();
            $table->json('ip_whitelist')->nullable();
            $table->unsignedInteger('rate_limit_limit')->default(60); // requests per minute
            $table->unsignedInteger('quota_limit')->default(10000); // total quota
            $table->unsignedInteger('quota_used')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->foreignId('rotated_key_id')->nullable()->constrained('api_keys')->nullOnDelete();
            $table->timestamp('rotation_grace_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
