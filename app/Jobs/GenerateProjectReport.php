<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateProjectReport implements ShouldQueue
{
    use Queueable;

    // ลองใหม่ได้สูงสุด 3 ครั้ง เว้นระยะ 10 วินาที
    public int $tries = 3;

    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(public Project $project)
    {
        // รับ Project Model เข้ามาใช้งาน
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::channel('job')->info('Report generation started', [
                'project_id' => $this->project->id,
            ]);

            // throw new \Exception('ระบบฐานข้อมูลภายนอกขัดข้อง (จำลอง Error)');
            sleep(3);

            Log::channel('job')->info('Report generated', [
                'project_id' => $this->project->id,
            ]);
        } catch (Throwable $e) {
            Log::channel('job')->error('Report failed', [
                'project_id' => $this->project->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * ทำงานอัตโนมัติเมื่อ Job ล้มเหลวครบตามจำนวน $tries แล้ว
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('job')->error('Report job failed permanently', [
            'project_id' => $this->project->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
