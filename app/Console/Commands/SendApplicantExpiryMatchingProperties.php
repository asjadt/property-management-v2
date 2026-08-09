<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Services\ApplicantService;
use App\Mail\ApplicantMatchingPropertiesMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendApplicantExpiryMatchingProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applicants:send-expiry-matching-properties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send matching properties 15 days and 1 day before applicant expires';

    /**
     * Execute the console command.
     */
    public function handle(ApplicantService $applicantService)
    {
        $in15Days = Carbon::today()->addDays(15)->toDateString();
        $in1Day = Carbon::today()->addDay()->toDateString();

        $applicants = Applicant::where(function ($query) use ($in15Days, $in1Day) {
            $query->whereDate('expiry_date', $in15Days)
                  ->orWhereDate('expiry_date', $in1Day);
        })
        ->where('is_active', 1)
        ->whereNull('tenant_id')
        ->whereNotNull('email')
        ->get();

        $count = 0;

        foreach ($applicants as $applicant) {
            $matchedProperties = $applicantService->findMatchingProperties($applicant);

            if ($matchedProperties->isNotEmpty()) {
                Mail::to($applicant->email)->queue(new ApplicantMatchingPropertiesMail($applicant, $matchedProperties));
                $count++;
            }
        }

        $this->info("Sent {$count} matching properties emails for expiring applicants.");
    }
}
