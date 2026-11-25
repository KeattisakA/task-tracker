<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;
use App\Events\TaskAssigned;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::with(['project', 'comments'])
            ->when($request->search, fn($q, $search) => $q->where ('title', 'like',"%{$search}%"))
            ->paginate(20);
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated());
        return response()->json($task, 201);
    }


    public function show(Task $task)
    {
        $task->load(['project', 'comments']);
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);
        $task->update($request->validated());
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']); 
    }

    public function assign(
        AssignTaskRequest $request,
        Task $task,
        TaskService $service,
    ) {
        Gate::authorize('update', $task);
        $to = User::findOrFail($request->assignee_id);
        return (new TaskResource($service->assign($task, $to)))
            ->additional(['message' => 'Task assigned successfully']);
    }
}