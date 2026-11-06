<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TransportDriverAssignment extends Model {
protected $fillable=['transport_vehicle_id','staff_profile_id','start_date','end_date'];
public function vehicle(){return $this->belongsTo(TransportVehicle::class,'transport_vehicle_id');}
public function staff(){return $this->belongsTo(StaffProfile::class,'staff_profile_id');}
}
