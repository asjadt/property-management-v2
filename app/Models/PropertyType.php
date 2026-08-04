<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PropertyType extends Model
{
    protected $fillable = ['title', 'description', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->sort_order = static::max('sort_order') + 1;
        });
    }

    public function scopePropertyTypeFilters($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        return $query;
    }

    public function beds(): BelongsToMany
    {
        return $this->belongsToMany(Bed::class);
    }

    public function baths(): BelongsToMany
    {
        return $this->belongsToMany(Bath::class);
    }
}
