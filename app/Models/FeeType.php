<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    protected $table = 'fee_types';

    protected $fillable = [
        'school_id', 'name', 'code', 'category', 'is_refundable', 'is_active',
    ];

    protected $casts = [
        'is_refundable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function invoiceItems()
    {
        return $this->hasMany(FeeInvoiceItem::class, 'fee_type_id');
    }
}
