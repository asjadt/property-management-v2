<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;

class DashboardManagementController extends Controller
{
    /**
     * Dashboard API Endpoint
     */
    public function index(Request $request)
    {
        // Fetching properties with their active status, current status, and full history
        $properties = Property::with(['status', 'statusHistories' => function($query) {
                // Order history so latest is first
                $query->orderBy('start_date', 'desc');
            }])
            ->where('is_active', true) // Only fetching Active properties as requested for calculations
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    // Make sure you add any other dashboard fields you need here
                    'is_active' => $property->is_active,
                    'current_status' => $property->status ? $property->status->name : 'Unknown',
                    'status_history' => $property->statusHistories,
                    
                    // Frontend needs items clickable - return a URL or route 
                    // Make sure to configure 'properties.show' route if using this
                    // 'action_url' => route('properties.show', $property->id), 
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $properties,
            'message' => 'Dashboard updated with properties, status, and history.'
        ]);
    }
}
