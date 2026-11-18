<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $owner;
    private User $assignee;
    private User $otherMember;
    private Project $project;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $this->assignee = User::create([
            'name' => 'Assignee User',
            'email' => 'assignee@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $this->otherMember = User::create([
            'name' => 'Other Member',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $this->project = Project::create([
            'owner_id' => $this->owner->id,
            'name' => 'Project Alpha',
            'description' => 'Test Project',
        ]);

        $this->task = Task::create([
            'project_id' => $this->project->id,
            'assignee_id' => $this->assignee->id,
            'title' => 'Task Alpha',
            'description' => 'Test Task',
            'status' => 'todo',
        ]);
    }

    public function test_unauthenticated_requests_return_401(): void
    {
        $this->putJson("/api/tasks/{$this->task->id}", ['title' => 'Updated'])
            ->assertStatus(401);

        $this->deleteJson("/api/tasks/{$this->task->id}")
            ->assertStatus(401);

        $this->putJson("/api/projects/{$this->project->id}", ['name' => 'Updated'])
            ->assertStatus(401);

        $this->deleteJson("/api/projects/{$this->project->id}")
            ->assertStatus(401);
    }

    public function test_admin_can_update_and_delete_any_task(): void
    {
        $this->actingAs($this->admin)
            ->putJson("/api/tasks/{$this->task->id}", [
                'title' => 'Admin Edited Task',
            ])
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->deleteJson("/api/tasks/{$this->task->id}")
            ->assertStatus(200);
    }

    public function test_project_owner_can_update_and_delete_task(): void
    {
        $this->actingAs($this->owner)
            ->putJson("/api/tasks/{$this->task->id}", [
                'title' => 'Owner Edited Task',
            ])
            ->assertStatus(200);

        $this->actingAs($this->owner)
            ->deleteJson("/api/tasks/{$this->task->id}")
            ->assertStatus(200);
    }

    public function test_assignee_can_update_task(): void
    {
        $this->actingAs($this->assignee)
            ->putJson("/api/tasks/{$this->task->id}", [
                'title' => 'Assignee Edited Task',
            ])
            ->assertStatus(200);
    }

    public function test_assignee_cannot_delete_task_returns_403(): void
    {
        $this->actingAs($this->assignee)
            ->deleteJson("/api/tasks/{$this->task->id}")
            ->assertStatus(403);
    }

    public function test_other_member_cannot_update_or_delete_task_returns_403(): void
    {
        $this->actingAs($this->otherMember)
            ->putJson("/api/tasks/{$this->task->id}", [
                'title' => 'Hacker Edited Task',
            ])
            ->assertStatus(403);

        $this->actingAs($this->otherMember)
            ->deleteJson("/api/tasks/{$this->task->id}")
            ->assertStatus(403);
    }

    public function test_admin_and_owner_can_update_and_delete_project(): void
    {
        $this->actingAs($this->owner)
            ->putJson("/api/projects/{$this->project->id}", [
                'name' => 'Owner Renamed Project',
            ])
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->deleteJson("/api/projects/{$this->project->id}")
            ->assertStatus(200);
    }

    public function test_other_member_cannot_update_or_delete_project_returns_403(): void
    {
        $this->actingAs($this->otherMember)
            ->putJson("/api/projects/{$this->project->id}", [
                'name' => 'Hacker Renamed Project',
            ])
            ->assertStatus(403);

        $this->actingAs($this->otherMember)
            ->deleteJson("/api/projects/{$this->project->id}")
            ->assertStatus(403);
    }
}
