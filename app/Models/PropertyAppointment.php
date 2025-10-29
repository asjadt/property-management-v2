<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAppointment extends Model
{
    use HasFactory;

    protected $hidden = [
        'pivot',
    ];

    protected $fillable = [
        'job_type',
        'employee_id',
        'start_date',
        'end_date',
        'description',
        'property_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Reusable Scopes

    /**
     * Scope to filter by creator
     */
    public function scopeCreatedByUser(Builder $query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to search appointments
     */
    public function scopeSearch(Builder $query, $searchKey = null)
    {
        $searchKey = $searchKey ?? request('search_key');

        if (empty($searchKey)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchKey) {
            $q->where('job_type', 'like', '%' . $searchKey . '%')
                ->orWhere('employee_id', 'like', '%' . $searchKey . '%')
                ->orWhere('description', 'like', '%' . $searchKey . '%');
        });
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange(Builder $query, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? request('start_date');
        $endDate = $endDate ?? request('end_date');

        if (!empty($startDate)) {
            $query->where('start_date', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->where('start_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to filter by property
     */
    public function scopeByProperty(Builder $query, $propertyId = null)
    {
        $propertyId = $propertyId ?? request('property_id');

        if (empty($propertyId)) {
            return $query;
        }

        return $query->where('property_id', $propertyId);
    }

    /**
     * Scope to filter by job type
     */
    public function scopeByJobType(Builder $query, $jobType = null)
    {
        $jobType = $jobType ?? request('job_type');

        if (empty($jobType)) {
            return $query;
        }

        return $query->where('job_type', $jobType);
    }

    /**
     * Scope to filter by employee
     */
    // public function scopeByEmployee(Builder $query, $employeeId = null)
    // {
    //     $employeeId = $employeeId ?? request('employee_id');

    //     if (empty($employeeId)) {
    //         return $query;
    //     }

    //     return $query->where('employee_id', 'like', '%' . $employeeId . '%');
    // }

    /**
     * Main filter scope - combines all filters
     */
    public function scopeFilter(Builder $query)
    {
        return $query->createdByUser(auth()->id())
            ->search()
            ->dateRange()
            ->byProperty()
            ->byJobType();
    }
}
