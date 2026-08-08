<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class RepairCategory extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
        "created_by",
        "is_default",
        "business_id"
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function scopeRepairCategoryQuery($query)
    {
        // GET AUTHENTICATED USER
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        return $query
            // FILTER BY BUSINESS ID OR DEFAULT ITEMS
            ->when(!$authUser->hasRole("superadmin"), function ($query) use ($authUser) {
                return $query->where(function ($q) use ($authUser) {
                    $q->where('repair_categories.is_default', 1)
                      ->orWhere('repair_categories.business_id', $authUser->business_id);
                });
            })
            ->when(request()->filled("name"), function ($query) {
                return $query->where(
                    'repair_categories.name',
                    request()->input("name")
                );
            })
            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query->where("repair_categories.name", "like", "%" . $term . "%");
                });
            })
            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('repair_categories.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('repair_categories.created_at', "<=", request()->input("end_date"));
            });
    }
}
