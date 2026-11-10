<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);
        $task = Task::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::find($id);
        if (!$task){
            return response()->json(['messege' => 'task not found'], 404);
        }
        return response()->json($task);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string'
        ]);
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['messege' => 'task not found'], 404);
        }

        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->save();
        return response()->json($task);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::Find($id);
        if (!$task) {
            return response()->json(['messege' => 'task not found'], 404);
        }
        $task->delete();
        return response()->json(['messege' => 'delete successfully']);
    }
}