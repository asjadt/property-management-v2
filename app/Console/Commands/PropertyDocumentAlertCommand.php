<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PropertyDocumentAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property_documents_alerts:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alerts for property documents 30, 15, and 1 day before expiry date';

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

        $documents = \App\Models\PropertyDocument::with('property.property_landlords', 'property.property_tenants')
            ->where('is_send_alert', 1)
            ->whereNotNull('gas_end_date')
            ->get();

        foreach ($documents as $document) {
            $expiryDate = \Carbon\Carbon::parse($document->gas_end_date)->startOfDay();
            
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
                if ($document->property && $document->property->created_by) {
                    $businessOwner = \App\Models\User::find($document->property->created_by);
                    if ($businessOwner && $businessOwner->email) {
                        $emails[] = $businessOwner->email;
                    }
                }

                if ($document->property) {
                    // Get tenant emails
                    if ($document->property->property_tenants) {
                        foreach ($document->property->property_tenants as $tenant) {
                            if ($tenant->email) {
                                $emails[] = $tenant->email;
                            }
                        }
                    }

                    // Get landlord emails
                    if ($document->property->property_landlords) {
                        foreach ($document->property->property_landlords as $landlord) {
                            if ($landlord->email) {
                                $emails[] = $landlord->email;
                            }
                        }
                    }
                }

                $emails = array_unique(array_filter($emails));

                if (!empty($emails)) {
                    \Illuminate\Support\Facades\Mail::to($emails)
                        ->send(new \App\Mail\PropertyDocumentAlertMail($document));
                }
            }
        }
    }
}
