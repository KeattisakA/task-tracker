<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::exceptionOccurred(function (JobExceptionOccurred $event) {
            if (
                str_contains($event->job->resolveName(), 'TaskAssignedNotification')
                && $event->exception instanceof TransportExceptionInterface
            ) {
                Log::channel('email')->error('Task assigned email SMTP failed', [
                    'attempt' => $event->job->attempts(),
                    'error' => $event->exception->getMessage(),
                ]);
            }
        });
    }
}
