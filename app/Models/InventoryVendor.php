<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryVendor extends Model
{
    protected $table = 'vendors';
    protected $fillable = ['school_id','name','contact_person','phone','email','address','tax_identifier'];
}
