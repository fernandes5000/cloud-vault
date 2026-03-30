<?php

namespace Database\Seeders;

use App\Enums\DriveItemType;
use App\Enums\UserRole;
use App\Models\DriveItem;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'CloudVault Admin',
            'email' => 'admin@cloudvault.test',
            'role' => UserRole::Admin,
            'preferred_locale' => 'en',
            'storage_quota_bytes' => 50 * 1024 * 1024 * 1024,
            'used_storage_bytes' => 4_194_304,
        ]);

        $user = User::factory()->create([
            'name' => 'Ana Silva',
            'email' => 'ana@cloudvault.test',
            'preferred_locale' => 'pt_BR',
            'storage_quota_bytes' => 20 * 1024 * 1024 * 1024,
            'used_storage_bytes' => 2_097_152,
        ]);

        $photos = DriveItem::create([
            'user_id' => $user->id,
            'type' => DriveItemType::Folder,
            'name' => 'Photos',
            'disk' => 'local',
            'metadata' => [],
        ]);

        DriveItem::create([
            'user_id' => $user->id,
            'parent_id' => $photos->id,
            'type' => DriveItemType::File,
            'name' => 'beach.jpg',
            'disk' => 'local',
            'storage_path' => 'seed/beach.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 1_048_576,
            'preview_status' => 'ready',
            'metadata' => ['seed' => true],
        ]);

        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'welcome',
            'title' => 'Welcome to CloudVault',
            'body' => 'Your personal cloud is ready.',
            'data' => ['admin_user_id' => $admin->id],
        ]);
    }
}
