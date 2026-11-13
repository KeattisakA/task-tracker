<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with('task.project')->get();
        return response()->json($comments);
    }

    public function store(StoreCommentRequest $request)
    {
        $comment = Comment::create($request->validated());
        return response()->json($comment, 201);
    }

    public function show(Comment $comment)
    {
        $comment->load('task.project');
        return response()->json($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());
        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}