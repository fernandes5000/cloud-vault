<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drive_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('parent_id')->nullable()->constrained('drive_items')->nullOnDelete();
            $table->string('type', 20);
            $table->string('name');
            $table->string('disk', 50)->default('local');
            $table->string('storage_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('preview_status', 20)->default('none');
            $table->string('preview_path')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'parent_id']);
            $table->index(['user_id', 'is_favorite']);
            $table->index(['user_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_items');
    }
};
