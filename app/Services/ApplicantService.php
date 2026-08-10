<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Property;

class ApplicantService
{
    public function findMatchingProperties(Applicant $applicant)
    {
        $query = Property::query()->where('is_active', 1);

        // Distance matching
        if ($applicant->latitude && $applicant->longitude && $applicant->radius) {
            $query->whereRaw(
                "ST_Distance_Sphere(point(properties.long, properties.lat), point(?, ?)) <= ? * 1609.34",
                [
                    $applicant->longitude,
                    $applicant->latitude,
                    $applicant->radius
                ]
            );
        }

        // Price range
        if ($applicant->min_price) {
            $query->where('price', '>=', $applicant->min_price);
        }
        if ($applicant->max_price) {
            $query->where('price', '<=', $applicant->max_price);
        }

        // Property Type
        $propertyTypeIds = $applicant->property_types()->pluck('property_types.id')->toArray();
        if (!empty($propertyTypeIds)) {
            $query->whereIn('property_type_id', $propertyTypeIds);
        }

        // Bed matching
        $bedIds = $applicant->beds()->pluck('beds.id')->toArray();
        if (!empty($bedIds)) {
            $query->whereIn('bed_id', $bedIds);
        }

        // Bath matching
        $bathIds = $applicant->baths()->pluck('baths.id')->toArray();
        if (!empty($bathIds)) {
            $query->whereIn('bath_id', $bathIds);
        }

        // DSS matching
        if (!is_null($applicant->is_dss)) {
            $query->where('is_dss', $applicant->is_dss);
        }

        return $query->get();
    }
    public function getApplicantReport($userId)
    {
        $now = \Carbon\Carbon::now();
        
        return [
            'total' => Applicant::where('created_by', $userId)->count(),
            'converted_to_tenant' => Applicant::where('created_by', $userId)->whereNotNull('tenant_id')->count(),
            'expired' => Applicant::where('created_by', $userId)->whereDate('expiry_date', '<', $now)->count(),
            'expire_within_15_days' => Applicant::where('created_by', $userId)
                ->whereDate('expiry_date', '>=', $now)
                ->whereDate('expiry_date', '<=', $now->copy()->addDays(15))
                ->count(),
            'expire_within_3_days' => Applicant::where('created_by', $userId)
                ->whereDate('expiry_date', '>=', $now)
                ->whereDate('expiry_date', '<=', $now->copy()->addDays(3))
                ->count(),
            'expire_within_1_day' => Applicant::where('created_by', $userId)
                ->whereDate('expiry_date', '>=', $now)
                ->whereDate('expiry_date', '<=', $now->copy()->addDays(1))
                ->count(),
        ];
    }
    public function getLeadsBreakdown($userId)
    {
        // Fetch active leads (applicants not yet converted to tenants)
        $applicants = Applicant::where('created_by', $userId)
            ->where('is_active', 1)
            ->whereNull('tenant_id')
            ->get();

        $leadsBreakdown = [];

        foreach ($applicants as $applicant) {
            $leadsBreakdown[] = [
                'id' => $applicant->id,
                'name' => $applicant->customer_name,
                'matching_property_count' => $this->findMatchingProperties($applicant)->count(),
            ];
        }

        return $leadsBreakdown;
    }
}
