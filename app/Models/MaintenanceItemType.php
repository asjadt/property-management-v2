<?php

namespace App\Models;
use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MaintenanceItemType extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
        'name',
        'is_default',
        'business_id',
        "is_active",
        "created_by"
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
    ];


















    public function scopeMaintenanceItemQuery($query)
    {
        // GET AUTHENTICATED USER
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        return $query
            // FILTER BY BUSINESS ID OR DEFAULT ITEMS
            ->when(!$authUser->hasRole("superadmin"), function ($query) use ($authUser) {
                return $query->where(function ($q) use ($authUser) {
                    $q->where('maintenance_item_types.is_default', 1)
                      ->orWhere('maintenance_item_types.business_id', $authUser->business_id);
                });
            })
            ->when(request()->filled("name"), function ($query) {
                return $query->where(
                    'maintenance_item_types.name',
                    request()->input("name")
                );
            })
            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query->orWhere("maintenance_item_types.name", "like", "%" . $term . "%");
                });
            })
            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('maintenance_item_types.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('maintenance_item_types.created_at', "<=", request()->input("end_date"));
            });
    }

}

