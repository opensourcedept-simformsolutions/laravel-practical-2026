<?php

namespace App\Listeners;

use App\Events\VisitorExited;
use App\Mail\VisitorExitMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVisitorExitMail
{
    public function __construct()
    {
        //
    }

    public function handle(
        VisitorExited $event
    ): void {

        $visitorLog = $event->visitorLog;

        Log::info('SendVisitorExitMail Listener Started', [
            'visitor_log_id' => $visitorLog->id,
        ]);

        $cacheKey = 'visitor-exit-mail:' . $visitorLog->id;

        if (!Cache::add($cacheKey, true, 300)) {

            Log::warning('Duplicate exit mail skipped', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            return;
        }

        $visitorLog->loadMissing([
            'flat.residents.user',
            'visitor',
        ]);

        $emails = $visitorLog
            ->flat
            ->residents
            ->map(fn($resident) => $resident->user?->email)
            ->filter()
            ->unique()
            ->values()
            ->all();

        Log::info('Resolved flat residents emails', [
            'visitor_log_id' => $visitorLog->id,
            'flat_id' => $visitorLog->flat_id,
            'emails' => $emails,
            'count' => count($emails),
        ]);

        if (empty($emails)) {

            Log::warning('No resident emails found', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            return;
        }

        Mail::to($emails)
            ->queue(
                (new VisitorExitMail($visitorLog))
                    ->onQueue('emails')
                    ->delay(now()->addSeconds(45))
            );

        Log::info('Single flat exit mail queued', [
            'visitor_log_id' => $visitorLog->id,
            'recipient_count' => count($emails),
        ]);
    }
}
