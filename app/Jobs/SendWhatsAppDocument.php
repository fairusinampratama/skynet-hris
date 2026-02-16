<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $receiver,
        public string $fileUrl,
        public string $caption = ''
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $service): void
    {
        $service->sendDocument($this->receiver, $this->fileUrl, $this->caption);
    }
}
