<?php

namespace App\Mail;

use App\Models\VisitorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VisitorExitMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 5;

    public function backoff(): array
    {
        return [
            30,
            60,
            90,
            120,
            150,
        ];
    }

    public function __construct(
        public VisitorLog $visitorLog
    ) {
        Log::info('VisitorExitMail Created', [
            'visitor_log_id' => $visitorLog->id,
        ]);

        $this->visitorLog->loadMissing([
            'visitor',
            'flat',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Visitor Exit Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.visitor-exit',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
