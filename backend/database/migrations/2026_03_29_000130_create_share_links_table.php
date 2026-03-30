<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_links', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('drive_item_id')->constrained('drive_items')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('visibility', 20)->default('public');
            $table->string('permission', 20)->default('view');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->boolean('requires_password')->default(false);
            $table->string('password_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('max_downloads')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['drive_item_id', 'is_active']);
            $table->index(['recipient_user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_links');
    }
};
