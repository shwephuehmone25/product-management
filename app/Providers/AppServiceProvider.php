<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderConfirmed;
use App\Listeners\SendOrderConfirmedNotification;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;

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
        Event::listen(OrderConfirmed::class, SendOrderConfirmedNotification::class);

        // Mail diagnostics: log when an email is being sent / sent
        Event::listen(MessageSending::class, function (MessageSending $e) {
            $to = collect($e->message->getTo() ?? [])->map(fn($a) => method_exists($a,'getAddress') ? $a->getAddress() : (string)$a)->values()->all();
            Log::info('Mail sending', [
                'subject' => $e->message->getSubject(),
                'to' => $to,
            ]);
        });

        Event::listen(MessageSent::class, function (MessageSent $e) {
            $to = collect($e->message->getTo() ?? [])->map(fn($a) => method_exists($a,'getAddress') ? $a->getAddress() : (string)$a)->values()->all();
            Log::info('Mail sent', [
                'subject' => $e->message->getSubject(),
                'to' => $to,
            ]);
        });

        // Queue diagnostics: log processed and failed jobs
        Queue::after(function (JobProcessed $event) {
            Log::info('Queue job processed', [
                'name' => $event->job->resolveName(),
            ]);
        });

        Queue::failing(function (JobFailed $event) {
            Log::error('Queue job failed', [
                'name' => $event->job->resolveName(),
                'error' => $event->exception->getMessage(),
            ]);
        });
    }
}
