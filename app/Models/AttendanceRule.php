<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceRule extends Model {
 protected $fillable=['school_id','name','code','shift_start','shift_end','grace_minutes','minimum_hours','is_active'];
 protected function casts(): array { return ['is_active'=>'boolean','minimum_hours'=>'decimal:2']; }
}
