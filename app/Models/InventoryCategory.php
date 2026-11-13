<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    protected $table = 'inventory_categories';
    protected $fillable = ['school_id','name','description'];
    public function items(): HasMany { return $this->hasMany(InventoryItem::class, 'inventory_category_id'); }
}
