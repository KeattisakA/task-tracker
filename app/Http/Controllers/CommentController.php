<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;

class CommentController extends Controller
{
    public function allComments()
    {
        $comments = Comment::latest()->paginate(10);

        return CommentResource::collection($comments);
    }

    public function index(Task $task)
    {
        $comments = $task->comments()->latest()->paginate(10);

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, Task $task)
    {
        $comment = $task->comments()->create($request->validated());

        return response()->json(new CommentResource($comment), 201);
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

        return response()->json([
            'message' => 'Comment deleted successfully',
        ], 200);
    }
}
