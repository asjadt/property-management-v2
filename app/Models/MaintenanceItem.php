<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_inspection_id',
        'maintenance_item_type_id',
        'status',
        'comment',
        'next_follow_up_date',
    ];

    public function inspection()
    {
        return $this->belongsTo(TenantInspection::class,"tenant_inspection_id","id");
    }

}
