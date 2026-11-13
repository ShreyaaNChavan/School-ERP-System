<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $fillable = ['school_id','vendor_id','order_number','order_date','expected_delivery_date','subtotal','tax_amount','total_amount','status','created_by'];
    protected $casts = ['order_date'=>'date','expected_delivery_date'=>'date','subtotal'=>'decimal:2','tax_amount'=>'decimal:2','total_amount'=>'decimal:2'];
    public function vendor(): BelongsTo { return $this->belongsTo(InventoryVendor::class, 'vendor_id'); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id'); }
}
