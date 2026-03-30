<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DriveUploadShareFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_folder_upload_file_list_it_and_share_it(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $user = User::factory()->create();
        $token = $user->createToken('test-suite')->plainTextToken;

        $folderResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/drive/folders', [
                'name' => 'Projects',
            ]);

        $folderId = $folderResponse->json('data.id');

        $folderResponse->assertCreated()
            ->assertJsonPath('data.type', 'folder');

        $uploadSessionResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/uploads', [
                'name' => 'architecture.pdf',
                'folder_id' => $folderId,
                'total_chunks' => 2,
                'total_size_bytes' => 12,
                'mime_type' => 'application/pdf',
                'metadata' => ['client_path' => '/docs/architecture.pdf'],
            ]);

        $uploadSessionId = $uploadSessionResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/v1/uploads/'.$uploadSessionId.'/chunks', [
                'chunk_index' => 0,
                'chunk' => UploadedFile::fake()->createWithContent('chunk-0.part', 'hello '),
            ])->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/v1/uploads/'.$uploadSessionId.'/chunks', [
                'chunk_index' => 1,
                'chunk' => UploadedFile::fake()->createWithContent('chunk-1.part', 'world!'),
            ])->assertOk();

        $completeResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/uploads/'.$uploadSessionId.'/complete');

        $fileId = $completeResponse->json('data.id');

        $completeResponse->assertOk()
            ->assertJsonPath('data.type', 'file')
            ->assertJsonPath('data.name', 'architecture.pdf');

        $listResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/drive?parent_id='.$folderId);

        $listResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $fileId);

        $shareResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/shares', [
                'drive_item_id' => $fileId,
                'visibility' => 'public',
                'permission' => 'download',
            ]);

        $publicToken = $shareResponse->json('data.token');

        $shareResponse->assertCreated()
            ->assertJsonPath('data.visibility', 'public');

        $publicShareResponse = $this->getJson('/api/v1/shares/public/'.$publicToken);

        $publicShareResponse
            ->assertOk()
            ->assertJsonPath('data.item.id', $fileId)
            ->assertJsonPath('data.item.previewUrl', route('api.v1.public-shares.preview', $publicToken))
            ->assertJsonPath('data.item.downloadUrl', route('api.v1.public-shares.download', $publicToken));

        $this->get('/api/v1/shares/public/'.$publicToken.'/preview')
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=architecture.pdf');

        $this->get('/api/v1/shares/public/'.$publicToken.'/download')
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=architecture.pdf');

        $this->assertDatabaseHas('drive_items', [
            'id' => $fileId,
            'name' => 'architecture.pdf',
            'preview_status' => 'ready',
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type' => 'upload.completed',
        ]);
    }

    public function test_protected_download_returns_unauthorized_json_when_token_is_missing(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('manual.pdf', 'download me');
        $path = $file->store('drive', 'local');

        $driveItem = $user->driveItems()->create([
            'type' => 'file',
            'name' => 'manual.pdf',
            'disk' => 'local',
            'storage_path' => $path,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => strlen('download me'),
            'preview_status' => 'ready',
            'metadata' => [],
        ]);

        $this->get('/api/v1/drive/items/'.$driveItem->id.'/download')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
