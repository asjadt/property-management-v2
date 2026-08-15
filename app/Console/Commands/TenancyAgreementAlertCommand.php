<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TenancyAgreementAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy_agreements_alerts:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alerts for tenancy agreements 30, 15, and 1 day before expiry date';

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

        $agreements = \App\Models\TenancyAgreement::with('property.property_landlords', 'tenants')
            ->where('is_send_alert', 1)
            ->whereNotNull('tenant_contact_expired_date')
            ->get();

        foreach ($agreements as $agreement) {
            $expiryDate = \Carbon\Carbon::parse($agreement->tenant_contact_expired_date)->startOfDay();
            
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
                if ($agreement->property && $agreement->property->created_by) {
                    $businessOwner = \App\Models\User::find($agreement->property->created_by);
                    if ($businessOwner && $businessOwner->email) {
                        $emails[] = $businessOwner->email;
                    }
                }

                // Get tenant emails
                if ($agreement->tenants) {
                    foreach ($agreement->tenants as $tenant) {
                        if ($tenant->email) {
                            $emails[] = $tenant->email;
                        }
                    }
                }

                // Get landlord emails
                if ($agreement->property && $agreement->property->property_landlords) {
                    foreach ($agreement->property->property_landlords as $landlord) {
                        if ($landlord->email) {
                            $emails[] = $landlord->email;
                        }
                    }
                }

                $emails = array_unique(array_filter($emails));

                if (!empty($emails)) {
                    \Illuminate\Support\Facades\Mail::to($emails)
                        ->send(new \App\Mail\TenancyAgreementAlertMail($agreement));
                }
            }
        }
    }
}
