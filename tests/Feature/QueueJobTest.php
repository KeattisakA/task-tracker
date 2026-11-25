<?php

namespace Tests\Feature;

use App\Jobs\GenerateProjectReport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueJobTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherMember;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::forceCreate([
            'name' => 'Project Owner',
            'email' => 'owner@test.com',
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
            'name' => 'Report Test Project',
            'description' => 'Test project for report generation queue',
        ]);
    }

    public function test_owner_can_dispatch_project_report_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->owner)
            ->postJson("/api/projects/{$this->project->id}/report");

        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Project report generation has been queued.',
            ]);

        Queue::assertPushed(GenerateProjectReport::class, function ($job) {
            return $job->project->id === $this->project->id;
        });
    }

    public function test_unauthorized_member_cannot_dispatch_report_job_returns_403(): void
    {
        Queue::fake();

        $this->actingAs($this->otherMember)
            ->postJson("/api/projects/{$this->project->id}/report")
            ->assertStatus(403);

        Queue::assertNotPushed(GenerateProjectReport::class);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->postJson("/api/projects/{$this->project->id}/report")
            ->assertStatus(401);
    }
}
