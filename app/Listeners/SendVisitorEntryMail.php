<?php

namespace App\Listeners;

use App\Events\VisitorEntered;
use App\Mail\VisitorEntryMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVisitorEntryMail
{
    public function __construct()
    {
        //
    }

    public function handle(
        VisitorEntered $event
    ): void {

        $visitorLog = $event->visitorLog;

        Log::info('SendVisitorEntryMail Listener Started', [
            'visitor_log_id' => $visitorLog->id,
        ]);

        $cacheKey = 'visitor-entry-mail:' . $visitorLog->id;

        if (!Cache::add($cacheKey, true, 300)) {

            Log::warning('Duplicate entry mail skipped', [
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
                (new VisitorEntryMail($visitorLog))
                    ->onQueue('emails')
                    ->delay(now()->addSeconds(45))
            );

        Log::info('Single flat entry mail queued', [
            'visitor_log_id' => $visitorLog->id,
            'recipient_count' => count($emails),
        ]);
    }
}
