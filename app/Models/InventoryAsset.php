<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAsset extends Model
{
    protected $table = 'asset_register';
    protected $fillable = ['school_id','campus_id','inventory_category_id','asset_tag','name','serial_number','purchase_date','purchase_cost','condition','location','status'];
    protected $casts = ['purchase_date'=>'date','purchase_cost'=>'decimal:2'];
    public function category(): BelongsTo { return $this->belongsTo(InventoryCategory::class, 'inventory_category_id'); }
}
