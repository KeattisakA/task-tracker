<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Events\TaskAssigned;
use Illuminate\Support\Facades\Log;

class TaskService
{
    public function assign(Task $task, User $to): Task
    {
        $task->update(['assignee_id' => $to->id]);
        TaskAssigned::dispatch($task);
        Log::info('Task assigned', [
            'task_id' => $task->id,
            'assignee_id' => $to->id,
            'by_user_id' => auth()->id(),
        ]);
        return $task;
    }
}
