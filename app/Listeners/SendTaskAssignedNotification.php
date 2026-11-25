<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Facades\Log;

class SendTaskAssignedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(TaskAssigned $event): void
    {
        $assignee = $event->task->assignee;

        if ($assignee) {
            $assignee->notify(
                new TaskAssignedNotification($event->task)
            );

            Log::channel('email')->info('Task assigned notification queued', [
                'task_id' => $event->task->id,
                'assignee_id' => $assignee->id,
                'email' => $assignee->email,
            ]);
        }
    }
}
