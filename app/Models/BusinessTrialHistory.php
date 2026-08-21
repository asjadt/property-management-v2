<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessTrialHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'granted_by',
        'days_granted',
        'reason',
        'total_used_before',
        'remaining_free_days'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function granter()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
