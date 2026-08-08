<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BillItem extends Model
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








    public function scopeBillItemQuery($query)
    {
        // GET AUTHENTICATED USER
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        return $query
            // FILTER BY BUSINESS ID OR DEFAULT ITEMS
            ->when(!$authUser->hasRole("superadmin"), function ($query) use ($authUser) {
                return $query->where(function ($q) use ($authUser) {
                    $q->where('bill_items.is_default', 1)
                      ->orWhere('bill_items.business_id', $authUser->business_id);
                });
            })
            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query->where("bill_items.name", "like", "%" . $term . "%")
                          ->orWhere("bill_items.description", "like", "%" . $term . "%")
                          ->orWhere("bill_items.price", "like", "%" . $term . "%");
                });
            })
            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('bill_items.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('bill_items.created_at', "<=", request()->input("end_date"));
            });
    }

}
