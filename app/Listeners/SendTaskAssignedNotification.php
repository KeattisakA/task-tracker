<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTaskAssignedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

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
