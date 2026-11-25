<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;

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
        $event->task->assignee?->notify(
            new TaskAssignedNotification($event->task)
        );
    }
}
