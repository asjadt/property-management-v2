<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyInventory extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
        'inventory_item_id',
        'inventory_location_id',
        'item_quantity',
        'item_condition',
        'item_details',
        'property_id',
        'files',
        "created_by",
    ];

    protected $casts = [
        'files' => 'array',
    ];
    // HIDE ATTRIBUTES
    protected $hidden = ['pivot'];


    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }
    public function inventoryLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id', 'id');
    }
}
