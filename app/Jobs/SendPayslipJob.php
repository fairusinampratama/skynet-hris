<?php

namespace App\Jobs;

use App\Models\Payroll;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendPayslipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Payroll $payroll
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $service): void
    {
        $user = $this->payroll->employee->user;
        
        if (!$user || !$user->phone_number) {
            echo "Job: User or Phone missing.\n";
            return;
        }

        // Generate signed URL valid for 24 hours
        $url = URL::temporarySignedRoute(
            'payroll.stream',
            now()->addHours(24),
            ['payroll' => $this->payroll->id]
        );

        $caption = "Slip Gaji {$this->payroll->period->month}/{$this->payroll->period->year}";

        // Send WhatsApp
        $service->sendDocument($user->phone_number, $url, $caption);

        // Update sent timestamp (allowed on locked periods via model event exception)
        $this->payroll->update(['wa_sent_at' => now()]);
    }
}
