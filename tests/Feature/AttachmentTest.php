<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $assignee;

    private User $otherMember;

    private Project $project;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystems.default'));

        $this->owner = User::forceCreate([
            'name' => 'Owner User',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $this->assignee = User::forceCreate([
            'name' => 'Assignee User',
            'email' => 'assignee@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $this->otherMember = User::forceCreate([
            'name' => 'Other Member',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $this->project = Project::create([
            'owner_id' => $this->owner->id,
            'name' => 'Test Project',
            'description' => 'Test Project Description',
        ]);

        $this->task = Task::create([
            'project_id' => $this->project->id,
            'assignee_id' => $this->assignee->id,
            'title' => 'Test Task',
            'description' => 'Test Task Description',
            'status' => 'todo',
        ]);
    }

    public function test_user_with_permission_can_upload_attachment(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->assignee)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'task_id', 'original_name', 'size', 'created_at'],
            ]);

        $attachment = $this->task->attachments()->first();
        $this->assertNotNull($attachment);
        $this->assertEquals('document.pdf', $attachment->original_name);
        Storage::disk(config('filesystems.default'))->assertExists($attachment->path);
    }

    public function test_upload_fails_when_file_exceeds_size_or_wrong_mime(): void
    {
        // 6 MB file (exceeds 5120 KB limit)
        $largeFile = UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf');

        $this->actingAs($this->assignee)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $largeFile,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        // Invalid extension (.exe)
        $exeFile = UploadedFile::fake()->create('danger.exe', 500, 'application/x-msdownload');

        $this->actingAs($this->assignee)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $exeFile,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_other_member_cannot_upload_attachment_returns_403(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $this->actingAs($this->otherMember)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $file,
            ])
            ->assertStatus(403);
    }

    public function test_authorized_user_can_download_and_delete_attachment(): void
    {
        $file = UploadedFile::fake()->create('notes.docx', 800, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $uploadResponse = $this->actingAs($this->assignee)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $file,
            ]);

        $attachmentId = $uploadResponse->json('data.id');
        $attachment = $this->task->attachments()->find($attachmentId);

        // Download by owner
        $this->actingAs($this->owner)
            ->get("/api/attachments/{$attachment->id}/download")
            ->assertStatus(200);

        // Delete by assignee (who has update right on task)
        $this->actingAs($this->assignee)
            ->deleteJson("/api/attachments/{$attachment->id}")
            ->assertStatus(200);

        // Assert removed from DB and disk
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk(config('filesystems.default'))->assertMissing($attachment->path);
    }

    public function test_authorized_user_can_get_s3_temporary_signed_url(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $file = UploadedFile::fake()->create('report.pdf', 500, 'application/pdf');

        $uploadResponse = $this->actingAs($this->assignee)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $file,
            ]);

        $uploadResponse->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'task_id', 'original_name', 'size', 'created_at'],
            ]);

        $attachmentId = $uploadResponse->json('data.id');

        $response = $this->actingAs($this->owner)
            ->getJson("/api/attachments/{$attachmentId}/signed-url");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'download_url',
                'expires_in',
            ]);

        // Test download endpoint with S3 returns signed url
        $downloadResponse = $this->actingAs($this->owner)
            ->getJson("/api/attachments/{$attachmentId}/download");

        $downloadResponse->assertStatus(200)
            ->assertJsonStructure([
                'download_url',
                'expires_in',
            ]);
    }

    public function test_public_download_route_works_without_authentication(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $file = UploadedFile::fake()->create('public-notes.pdf', 300, 'application/pdf');

        $uploadResponse = $this->actingAs($this->assignee)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $file,
            ]);

        $attachmentId = $uploadResponse->json('data.id');

        // Without actingAs (no auth token), local disk streams 200 OK
        $this->get("/api/public/attachments/{$attachmentId}/download")
            ->assertStatus(200);

        // On S3 disk, redirects to temporary signed URL (302)
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $s3Upload = $this->actingAs($this->assignee)
            ->postJson("/api/tasks/{$this->task->id}/attachments", [
                'file' => $file,
            ]);

        $s3AttachmentId = $s3Upload->json('data.id');

        $this->get("/api/public/attachments/{$s3AttachmentId}/download")
            ->assertStatus(302);
    }
}
