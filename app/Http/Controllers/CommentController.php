<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = Comment::with('task.project')->get();
        return response()->json($comments);
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
            'task_id' => 'required|integer|exists:tasks,id',
            'body' => 'required|string',
        ]);
        $comment = Comment::create([
            'task_id' => $request->input('task_id'),
            'body' => $request->input('body'),
        ]);
        return response()->json($comment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::with('task.project')->find($id);
        if (!$comment){
            return response()->json(['message' => 'comment not found'], 404);
        }
        return response()->json($comment);
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
            'task_id' => 'required|integer|exists:tasks,id',
        ]);
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['messege' => 'comment not found'], 404);
        }

        $comment->task_id = $request->input('task_id');
        $comment->body = $request->input('body');
        $comment->save();
        return response()->json($comment);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = Comment::Find($id);
        if (!$comment) {
            return response()->json(['messege' => 'comment not found'], 404);
        }
        $comment->delete();
        return response()->json(['messege' => 'delete successfully']);
    }
}