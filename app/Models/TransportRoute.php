<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TransportRoute extends Model {
protected $fillable=['school_id','name','code','distance_km','default_fee','is_active'];
protected function casts():array{return ['is_active'=>'boolean','distance_km'=>'decimal:2','default_fee'=>'decimal:2'];}
public function stops(){return $this->hasMany(TransportStop::class)->orderBy('sequence');}
}
