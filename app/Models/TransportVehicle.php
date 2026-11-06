<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TransportVehicle extends Model {
protected $fillable=['school_id','campus_id','registration_number','vehicle_type','make_model','capacity','insurance_expiry','permit_expiry','status'];
protected function casts():array{return ['insurance_expiry'=>'date','permit_expiry'=>'date'];}
public function driverAssignments(){return $this->hasMany(TransportDriverAssignment::class);}
}
