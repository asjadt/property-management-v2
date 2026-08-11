<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'created_by',
        'is_default',
        'business_id'
    ];

    public function scopeSaleItemQuery($query)
    {
        // GET AUTHENTICATED USER
        /** @var \App\Models\User $authUser */
        $authUser = \Illuminate\Support\Facades\Auth::user();

        return $query
            // FILTER BY BUSINESS ID OR DEFAULT ITEMS
            ->when($authUser->hasRole("superadmin"), function ($query) {
                // If superadmin, allow them to filter by is_default (defaulting to 1)
                $isDefault = request()->has('is_default') ? filter_var(request()->input('is_default'), FILTER_VALIDATE_BOOLEAN) : 1;
                $query->where('sale_items.is_default', $isDefault);
                
                if (request()->filled('business_id')) {
                    $query->where('sale_items.business_id', request()->input('business_id'));
                }

                return $query;
            }, function ($query) use ($authUser) {
                return $query->where(function ($q) use ($authUser) {
                    $q->where('sale_items.is_default', 1)
                      ->orWhere('sale_items.business_id', $authUser->business_id);
                });
            })
            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query->where("sale_items.name", "like", "%" . $term . "%")
                          ->orWhere("sale_items.description", "like", "%" . $term . "%")
                          ->orWhere("sale_items.price", "like", "%" . $term . "%");
                });
            })
            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('sale_items.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('sale_items.created_at', "<=", request()->input("end_date"));
            });
    }
}
