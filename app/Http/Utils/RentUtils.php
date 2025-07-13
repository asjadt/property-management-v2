<?php

namespace App\Http\Utils;

use App\Models\Rent;
use App\Models\TenancyAgreement;
use Carbon\Carbon;

class RentUtils
{
    public function rent_report()
    {
        $user_id = auth()->id();
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();

        // START OF MONTH
        $start_of_month = $now->copy()->startOfMonth();
        // END OF MONTH
        $end_of_month = $now->copy()->endOfMonth();
        // START OF NEXT MONTH
        $next_month_start = $now->copy()->addMonth()->startOfMonth();

        // GET ALL RENT
        $rents_query = Rent::where('created_by', $user_id);
        // TOTAL RENT COLLECTED
        $total_collected = (clone $rents_query)->sum('paid_amount');
        // TOTAL RENT COLLECTED CURRENT MONTH
        $total_collected_this_month = (clone $rents_query)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->sum('paid_amount');

        $total_due_this_month = (clone $rents_query)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->selectRaw('SUM(arrear + rent_amount - paid_amount) as total_due')
            ->value('total_due');

        // Fetch agreements with rents
        $agreements = TenancyAgreement::with([
            'property',
            'tenants',
            'rents' => function ($q) {
                $q->select('tenancy_agreement_id', 'month', 'year', 'paid_amount');
            }
        ])->whereHas('property', function ($q) use ($user_id) {
            $q->where('created_by', $user_id);
        })->get();


        $total_upcoming_rent_next_month = 0;

        $alerts_summary = [
            'due_in_15_days' => 0,
            'due_in_30_days' => 0,
            'due_in_45_days' => 0,
        ];

        foreach ($agreements as $agreement) {
            $rent_due_day = $agreement->rent_due_day;

            // Skip agreements with no rent_due_day or expired contract
            if (!$rent_due_day || !$agreement->tenant_contact_expired_date) {
                continue;
            }

            $expiredDate = Carbon::parse($agreement->tenant_contact_expired_date)->endOfMonth();
            if ($expiredDate->lt($today)) {
                continue;
            }

            $rent_amount = $agreement->agreed_rent;

            $rent_map = collect($agreement->rents)->mapToGroups(function ($item) {
                return [Carbon::createFromDate($item->year, $item->month, 1)->format('Y-m') => $item->paid_amount];
            });

            $currentMonthKey = $start_of_month->format('Y-m');
            $nextMonthKey = $next_month_start->format('Y-m');

            $dueDateCurrentMonth = Carbon::create($now->year, $now->month, $rent_due_day);
            $dueDateNextMonth = Carbon::create($now->copy()->addMonth()->year, $now->copy()->addMonth()->month, $rent_due_day);

            $isPaidCurrent = $rent_map->has($currentMonthKey);
            $isPaidNext = $rent_map->has($nextMonthKey);

            $expected_until_current = 0;
            $expected_until_next = 0;
            $paid_until_current = 0;
            $paid_until_next = 0;

            // Only count current month expected if due date passed and within contract
            if ($dueDateCurrentMonth->lessThanOrEqualTo($today) && $dueDateCurrentMonth->lte($expiredDate)) {
                $expected_until_current += $rent_amount;
                if ($isPaidCurrent) {
                    $paid_until_current += $rent_map->get($currentMonthKey)?->sum() ?? 0;
                }
            }

            // Always include next month if within contract
            if ($dueDateNextMonth->lte($expiredDate)) {
                $expected_until_next += $rent_amount;
                if ($isPaidNext) {
                    $paid_until_next += $rent_map->get($nextMonthKey)?->sum() ?? 0;
                }
            }

            // Add to this month's due if rent_due_day has passed and unpaid
            if ($dueDateCurrentMonth->lte($today) && !$isPaidCurrent && $dueDateCurrentMonth->lte($expiredDate)) {
                $total_due_this_month += $rent_amount;
            }

            // Add to next month's upcoming if unpaid and within contract
            if (!$isPaidNext && $dueDateNextMonth->lte($expiredDate)) {
                $total_upcoming_rent_next_month += $rent_amount;
            }

            // Alert date logic
            $alert_ranges = [
                'due_in_15_days' => $today->copy()->addDays(15),
                'due_in_30_days' => $today->copy()->addDays(30),
                'due_in_45_days' => $today->copy()->addDays(45),
            ];

            foreach ($alert_ranges as $key => $alertDate) {
                $alertDueDate = Carbon::create($alertDate->year, $alertDate->month, $rent_due_day);

                if ($alertDueDate->lte($expiredDate)) {
                    $alertMonthKey = $alertDueDate->format('Y-m');
                    $isPaidAlert = $rent_map->has($alertMonthKey);

                    if (!$isPaidAlert) {
                        $alerts_summary[$key] += $rent_amount;
                    }
                }
            }
        }

        return [
            'total_collected' => $total_collected,
            'total_collected_this_month' => $total_collected_this_month,
            'total_due_this_month' => $total_due_this_month,
            'total_upcoming_rent_next_month' => $total_upcoming_rent_next_month,
            'expected_until_next' => $expected_until_next,
            'paid_until_next' => $paid_until_next,
            'alerts_summary' => $alerts_summary,
        ];
    }
}
