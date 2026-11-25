<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
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
        $disk = config('filesystems.default');
        $path = $file->store("tasks/{$task->id}", $disk);

        $attachment = $task->attachments()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        return (new AttachmentResource($attachment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * ดาวน์โหลดไฟล์ (รองรับทั้ง Stream ตรง และ S3 Signed URL)
     */
    public function download(Request $request, Attachment $attachment)
    {
        Gate::authorize('view', $attachment->task);

        $disk = config('filesystems.default');

        if ($disk === 's3') {
            $url = $this->makeSignedUrl($attachment);

            if ($request->boolean('redirect', false)) {
                return redirect()->away($url);
            }

            return response()->json([
                'download_url' => $url,
                'expires_in' => '15 minutes',
            ]);
        }

        return Storage::disk($disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    /**
     * สร้าง Signed URL ชั่วคราว (15 นาที) สำหรับดาวน์โหลดไฟล์จาก S3 โดยตรง
     */
    public function signedUrl(Attachment $attachment)
    {
        Gate::authorize('view', $attachment->task);

        return response()->json([
            'download_url' => $this->makeSignedUrl($attachment),
            'expires_in' => '15 minutes',
        ]);
    }

    /**
     * Public Download Route (ไม่ต้องมี Auth / Token)
     * ใครที่มีลิงก์นี้สามารถดาวน์โหลดไฟล์ได้ทันที
     */
    public function publicDownload(Attachment $attachment)
    {
        $disk = config('filesystems.default');

        if ($disk === 's3') {
            return redirect()->away($this->makeSignedUrl($attachment));
        }

        return Storage::disk($disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    public function destroy(Attachment $attachment)
    {
        Gate::authorize('update', $attachment->task);
        Storage::disk(config('filesystems.default'))->delete($attachment->path);
        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully',
        ], 200);
    }

    private function makeSignedUrl(Attachment $attachment): string
    {
        return Storage::disk('s3')->temporaryUrl(
            $attachment->path,
            now()->addMinutes(15),
            [
                'ResponseContentDisposition' => 'attachment; filename="'.$attachment->original_name.'"',
            ]
        );
    }
}
