<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class TaskNotificationTest extends TestCase
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

        $this->owner = User::forceCreate([
            'name' => 'Project Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
        ]);

        $this->assignee = User::forceCreate([
            'name' => 'Assigned Developer',
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
            'title' => 'Design API Architecture',
            'description' => 'Create REST endpoints',
            'status' => 'pending',
        ]);
    }

    public function test_task_has_assignee_relationship(): void
    {
        $this->assertInstanceOf(User::class, $this->task->assignee);
        $this->assertEquals($this->assignee->id, $this->task->assignee->id);
    }

    public function test_task_assigned_event_dispatches_and_triggers_listener_notification(): void
    {
        Notification::fake();

        // Dispatch Event
        TaskAssigned::dispatch($this->task);

        // Assert notification was sent to assignee
        Notification::assertSentTo(
            $this->assignee,
            TaskAssignedNotification::class,
            function (TaskAssignedNotification $notification) {
                return $notification->task->id === $this->task->id;
            }
        );
    }

    public function test_task_assigned_notification_structure(): void
    {
        $notification = new TaskAssignedNotification($this->task);

        $channels = $notification->via($this->assignee);
        $this->assertEquals(['mail', 'database'], $channels);

        $mail = $notification->toMail($this->assignee);
        $this->assertEquals('You have a new task', $mail->subject);
        $this->assertContains('Design API Architecture', $mail->introLines);

        $data = $notification->toArray($this->assignee);
        $this->assertEquals([
            'task_id' => $this->task->id,
            'title' => 'Design API Architecture',
        ], $data);
    }

    public function test_assign_endpoint_updates_assignee_and_dispatches_notification(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->owner)
            ->postJson("/api/tasks/{$this->task->id}/assign", [
                'assignee_id' => $this->otherMember->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Task assigned successfully',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'assignee_id' => $this->otherMember->id,
        ]);

        Notification::assertSentTo(
            $this->otherMember,
            TaskAssignedNotification::class
        );
    }

    public function test_assign_endpoint_validates_assignee_id(): void
    {
        $this->actingAs($this->owner)
            ->postJson("/api/tasks/{$this->task->id}/assign", [
                'assignee_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['assignee_id']);
    }

    public function test_assign_endpoint_unauthorized_user_forbidden(): void
    {
        $this->actingAs($this->otherMember)
            ->postJson("/api/tasks/{$this->task->id}/assign", [
                'assignee_id' => $this->otherMember->id,
            ])
            ->assertStatus(403);
    }
}
