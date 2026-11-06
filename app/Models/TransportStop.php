<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TransportStop extends Model {
protected $fillable=['transport_route_id','name','address','latitude','longitude','pickup_time','drop_time','sequence'];
public function route(){return $this->belongsTo(TransportRoute::class,'transport_route_id');}
}
