<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('target_folder_id')->nullable()->constrained('drive_items')->nullOnDelete();
            $table->string('original_name');
            $table->string('disk', 50)->default('local');
            $table->string('temp_directory');
            $table->unsignedBigInteger('total_size_bytes')->nullable();
            $table->unsignedInteger('total_chunks');
            $table->unsignedInteger('uploaded_chunks')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('last_chunk_at')->nullable();
            $table->foreignUlid('completed_drive_item_id')->nullable()->constrained('drive_items')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
    }
};
