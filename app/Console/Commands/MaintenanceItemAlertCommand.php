<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MaintenanceItemAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance_items_alerts:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alerts for maintenance items 30, 15, and 1 day before follow up date';

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

        $items = \App\Models\MaintenanceItem::with('inspection.property.landlords', 'inspection.tenant', 'inspection')
            ->where('is_send_alert', 1)
            ->whereNotNull('next_follow_up_date')
            ->get();

        foreach ($items as $item) {
            $followUpDate = \Carbon\Carbon::parse($item->next_follow_up_date)->startOfDay();
            
            $shouldSend = false;
            foreach ($targetDates as $targetDate) {
                if ($followUpDate->equalTo($targetDate)) {
                    $shouldSend = true;
                    break;
                }
            }

            if ($shouldSend) {
                $emails = [];
                
                // Get business owner email
                $businessOwner = \App\Models\User::find($item->inspection->created_by);
                if ($businessOwner && $businessOwner->email) {
                    $emails[] = $businessOwner->email;
                }

                // Get tenant email
                if ($item->inspection->tenant && $item->inspection->tenant->email) {
                    $emails[] = $item->inspection->tenant->email;
                }

                // Get landlord emails
                if ($item->inspection->property && $item->inspection->property->landlords) {
                    foreach ($item->inspection->property->landlords as $landlord) {
                        if ($landlord->email) {
                            $emails[] = $landlord->email;
                        }
                    }
                }

                $emails = array_unique(array_filter($emails));

                if (!empty($emails)) {
                    \Illuminate\Support\Facades\Mail::to($emails)
                        ->send(new \App\Mail\MaintenanceItemAlertMail($item));
                }
            }
        }
    }
}
