<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    protected $fillable = [
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_status',
        'trial_ends_at',
        "name",
        "about",
        "web_page",
        "phone",
        "email",
        "additional_information",
        "address_line_1",
        "address_line_2",
        "lat",
        "long",
        "country",
        "city",
        "currency",
        "postcode",
        "logo",
        "image",
        "status",
        "owner_id",
        "created_by",
        "invoice_title",
        "footer_text",
       "is_reference_manual",
       "receipt_footer",
       "account_name" ,
       "account_number",
       "send_email_alert",
       "sort_code",
       "pin" ,
       "type" ,
       "sidebar_auto_collapse",
       "tax",
       "reseller_id",
    ];

    public function owner(){
        return $this->belongsTo(User::class,'owner_id', 'id');
    }

    public function reminder(){
        return $this->hasMany(Reminder::class,'created_by', 'owner_id');
    }

    public function reseller(){
        return $this->belongsTo(User::class,'reseller_id', 'id');
    }

    public function scopeBusinessFilters($query, array $filters = [])
    {
        if (auth()->check() && auth()->user()->hasRole('reseller') && !auth()->user()->hasRole('superadmin')) {
            $query->where('businesses.reseller_id', auth()->id());
        }

        if (!empty($filters['searchKey'])) {
            $searchKey = $filters['searchKey'];
            $query->where(function ($q) use ($searchKey) {
                $q->where('businesses.name', 'like', "%{$searchKey}%")
                  ->orWhereHas('owner', function ($q2) use ($searchKey) {
                      $q2->where('first_Name', 'like', "%{$searchKey}%")
                         ->orWhere('last_Name', 'like', "%{$searchKey}%")
                         ->orWhere('email', 'like', "%{$searchKey}%");
                  });
            });
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('businesses.created_at', [
                \Carbon\Carbon::parse($filters['start_date'])->startOfDay(),
                \Carbon\Carbon::parse($filters['end_date'])->endOfDay()
            ]);
        } elseif (!empty($filters['start_date'])) {
            $query->where('businesses.created_at', '>=', \Carbon\Carbon::parse($filters['start_date'])->startOfDay());
        } elseif (!empty($filters['end_date'])) {
            $query->where('businesses.created_at', '<=', \Carbon\Carbon::parse($filters['end_date'])->endOfDay());
        }

        if (!empty($filters['status'])) {
            $query->where('businesses.status', $filters['status']);
        }

        return $query;
    }

    public function trialHistories()
    {
        return $this->hasMany(BusinessTrialHistory::class, 'business_id', 'id');
    }
}

