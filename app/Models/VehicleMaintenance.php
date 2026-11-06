<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VehicleMaintenance extends Model {
protected $fillable=['transport_vehicle_id','service_date','next_service_date','service_type','description','cost','vendor_name','status'];
protected function casts():array{return ['service_date'=>'date','next_service_date'=>'date','cost'=>'decimal:2'];}
}
