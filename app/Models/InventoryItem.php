<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $table = 'inventory_items';
    protected $fillable = ['school_id','inventory_category_id','name','item_code','unit','quantity_on_hand','reorder_level','unit_cost'];
    protected $casts = ['quantity_on_hand'=>'decimal:2','reorder_level'=>'decimal:2','unit_cost'=>'decimal:2'];
    public function category(): BelongsTo { return $this->belongsTo(InventoryCategory::class, 'inventory_category_id'); }
}
