<?php

namespace App\Models;
use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceItemType extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                  'name',
                  "is_active",
                  "created_by"
    ];

    protected $casts = [
           ];


















}

