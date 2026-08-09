<?php

namespace App\Services;

use App\Models\Rent;
use App\Models\TenancyAgreement;
use Carbon\Carbon;

class RentService
{
    /**
     * Generate retroactive rents for a given tenancy agreement
     * up to the current date, ensuring no duplicates.
     */
    public function generateRetroactiveRents(TenancyAgreement $agreement)
    {
        if (empty($agreement->rent_due_day)) {
            return;
        }

        $moving_date = Carbon::parse($agreement->date_of_moving)->startOfDay();
        $due_day = (int)$agreement->rent_due_day;
        $current_date = $moving_date->copy()->startOfMonth();
        
        $today = Carbon::today()->endOfDay();
        $agreement_end_date = !empty($agreement->tenant_contact_expired_date) ? Carbon::parse($agreement->tenant_contact_expired_date)->endOfDay() : null;

        while ($current_date <= $today) {
            $due_date = $current_date->copy()->day(min($due_day, $current_date->daysInMonth));

            if ($due_date >= $moving_date && $due_date <= $today && ($agreement_end_date === null || $due_date <= $agreement_end_date)) {
                
                // Check if rent already exists for this agreement, year and month
                $rentExists = Rent::where('tenancy_agreement_id', $agreement->id)
                                  ->where('year', $due_date->year)
                                  ->where('month', $due_date->month)
                                  ->exists();

                if (!$rentExists) {
                    Rent::create([
                        'tenancy_agreement_id' => $agreement->id,
                        'year' => $due_date->year,
                        'month' => $due_date->month,
                        'rent_amount' => $agreement->agreed_rent,
                        'paid_amount' => 0,
                        'arrear' => $agreement->agreed_rent,
                        'payment_status' => 'pending',
                        'payment_date' => $due_date->toDateString(),
                        'payment_method' => "",
                        'rent_taken_by' => "",
                        'remarks' => 'This rent is auto generated and due on ' . $due_date->toDateString(),
                        'created_by' => auth()->user()->id ?? $agreement->created_by ?? 1,
                    ]);
                }
            }
            $current_date->addMonth();
        }
    }
}
