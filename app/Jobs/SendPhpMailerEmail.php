<?php

namespace App\Jobs;

use App\Services\PhpMailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPhpMailerEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $to,
        public string $subject,
        public string $view,
        public array $data = [],
        public ?string $replyToEmail = null,
        public ?string $replyToName = null,
    ) {
    }

    public function handle(PhpMailerService $mailer): void
    {
        $mailer->send(
            to: $this->to,
            subject: $this->subject,
            view: $this->view,
            data: $this->data,
            replyToEmail: $this->replyToEmail,
            replyToName: $this->replyToName,
        );
    }
}
