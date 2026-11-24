<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

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
        // throw new \Exception("ระบบภายนอกขัดข้อง จำลอง Error!");
        // // คำนวณสรุปข้อมูลโปรเจกต์
        Log::info("กำลังเริ่มสร้างรายงานสำหรับ Project: {$this->project->name} (ID: {$this->project->id})");

        sleep(3);

        Log::info("สร้างรายงาน Project ID: {$this->project->id} สำเร็จเรียบร้อยแล้ว!");
    }
}