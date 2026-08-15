<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApplicantAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applicants_alerts:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alerts for applicants 30, 15, and 1 day before expiry date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        $targetDates = [
            $today->copy()->addDays(30),
            $today->copy()->addDays(15),
            $today->copy()->addDays(1),
        ];

        $applicants = \App\Models\Applicant::where('is_send_alert', 1)
            ->whereNotNull('expiry_date')
            ->get();

        foreach ($applicants as $applicant) {
            $expiryDate = \Carbon\Carbon::parse($applicant->expiry_date)->startOfDay();
            
            $shouldSend = false;
            foreach ($targetDates as $targetDate) {
                if ($expiryDate->equalTo($targetDate)) {
                    $shouldSend = true;
                    break;
                }
            }

            if ($shouldSend) {
                $emails = [];
                
                // Get business owner email
                if ($applicant->created_by) {
                    $businessOwner = \App\Models\User::find($applicant->created_by);
                    if ($businessOwner && $businessOwner->email) {
                        $emails[] = $businessOwner->email;
                    }
                }

                // Get applicant's own email if we want to alert them too
                if ($applicant->email) {
                    $emails[] = $applicant->email;
                }

                $emails = array_unique(array_filter($emails));

                if (!empty($emails)) {
                    \Illuminate\Support\Facades\Mail::to($emails)
                        ->send(new \App\Mail\ApplicantAlertMail($applicant));
                }
            }
        }
    }
}
