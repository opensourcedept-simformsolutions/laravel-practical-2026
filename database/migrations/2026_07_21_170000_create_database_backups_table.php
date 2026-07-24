<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('table_name')->nullable()->comment('Null for full database dump, or table name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('disk')->default('local');
            $table->text('cloudinary_url')->nullable();
            $table->string('cloudinary_public_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
