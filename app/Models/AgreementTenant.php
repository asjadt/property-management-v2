<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgreementTenant extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenant_id',
        'tenancy_agreement_id',
    ];

    // Define the relationship with Tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Define the relationship with TenantAgreement
    public function tenantAgreement()
    {
        return $this->belongsTo(TenancyAgreement::class, 'tenancy_agreement_id');
    }
}
