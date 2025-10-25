<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryLocation extends Model
{
    use HasFactory;


    // The table associated with the model.
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    // HIDE ATTRIBUTES
    protected $hidden = ['pivot'];

    public function inventoryLocations(): HasOne
    {
        return $this->hasOne(PropertyInventory::class, 'inventory_location_id', 'id');
    }

    public function scopeFilters($query)
    {
        // Filter by active status if 'is_active' exists in request
        return $query
            ->when(request()->has('is_active'), function ($q) {
                $q->where('is_active', filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN));
            })
            // Filter by search term in 'name'
            ->when(request()->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });
    }
}
