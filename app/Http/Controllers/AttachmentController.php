<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Attachment;
use App\Http\Resources\AttachmentResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{

    public function store(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,png,docx'],
        ]);

        $file = $request->file('file');
        $path = $file->store("tasks/{$task->id}", 'local');

        $attachment = $task->attachments()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }

    public function download(Attachment $attachment)
    {
        Gate::authorize('view', $attachment->task);

        return Storage::download(
            $attachment->path,
            $attachment->original_name
        );
    }

    public function destroy(Attachment $attachment)
    {
        Gate::authorize('update', $attachment->task);

        Storage::delete($attachment->path);
        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully'
        ], 200);
    }
}
