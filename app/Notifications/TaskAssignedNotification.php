<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // ลองใหม่ได้สูงสุด 3 ครั้ง เว้นระยะ 10 วินาที
    public int $tries = 3;

    public int $backoff = 10;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Task $task) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            Log::channel('email')->info('Task assigned email sending', [
                'task_id' => $this->task->id,
                'to_email' => $notifiable->email,
                'subject' => 'You have a new task',
            ]);

            // throw new \Exception('จำลองการส่งอีเมลล้มเหลว (SMTP Error)');

            return (new MailMessage)
                ->subject('You have a new task')
                ->line($this->task->title);
        } catch (Throwable $e) {
            Log::channel('email')->error('Task assigned email failed', [
                'task_id' => $this->task->id,
                'to_email' => $notifiable->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
        ];
    }

    /**
     * ทำงานอัตโนมัติเมื่อส่ง Notification/Email ล้มเหลวครบตามจำนวน $tries แล้ว
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('email')->error('Task assigned email failed permanently', [
            'task_id' => $this->task->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
