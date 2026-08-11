<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Landlord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_Name',
        'last_Name',
        'phone',
        'image',
        'address_line_1',
        'address_line_2',
        'country',
        'city',
        'postcode',
        'lat',
        'long',
        'email',
        'files',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'files'     => 'array',
        'is_active' => 'boolean',
    ];


    // RELATIONSHIPS

    /**
     * The User account linked to this landlord (for login).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * The admin User who created this landlord record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Properties owned by this landlord (via property_landlords pivot).
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_landlords', 'landlord_id', 'property_id');
    }

    // SCOPES

    /**
     * Scope landlords visible to the currently-authenticated user.
     *
     * - Landlord role  → returns ONLY the landlord record linked to Auth::id()
     * - Admin / owner  → returns all landlords created by Auth::id() (existing behaviour)
     */
    public function scopeForAuthUser($query)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($authUser->hasRole('landlord')) {
            // LANDLORD SELF-SCOPE — only their own record
            return $query->where('landlords.user_id', $authUser->id);
        }

        // ADMIN SCOPE — all landlords this admin created
        return $query->where('landlords.created_by', $authUser->id);
    }

    /**
     * Scope: apply optional filter parameters to the landlord query.
     * Used by controller listing methods.
     */
    public function scopeLandlordFilters($query, array $filters = [])
    {
        if (!empty($filters['search_key'])) {
            $searchKey = $filters['search_key'];
            $query->where(function ($q) use ($searchKey) {
                $q->where('landlords.first_Name', 'like', "%{$searchKey}%")
                  ->orWhere('landlords.last_Name',  'like', "%{$searchKey}%")
                  ->orWhere('landlords.email',       'like', "%{$searchKey}%")
                  ->orWhere('landlords.phone',       'like', "%{$searchKey}%")
                  ->orWhere('landlords.city',        'like', "%{$searchKey}%")
                  ->orWhere('landlords.postcode',    'like', "%{$searchKey}%");
            });
        }

        if (!empty($filters['is_active'])) {
            $query->where('landlords.is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('landlords.created_at', [
                \Carbon\Carbon::parse($filters['start_date'])->startOfDay(),
                \Carbon\Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        } elseif (!empty($filters['start_date'])) {
            $query->where('landlords.created_at', '>=', \Carbon\Carbon::parse($filters['start_date'])->startOfDay());
        } elseif (!empty($filters['end_date'])) {
            $query->where('landlords.created_at', '<=', \Carbon\Carbon::parse($filters['end_date'])->endOfDay());
        }

        return $query;
    }
}
