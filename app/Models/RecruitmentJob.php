<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RecruitmentJob extends Model {
 protected $fillable=['school_id','campus_id','designation_id','title','description','vacancies','application_deadline','status'];
 protected function casts(): array { return ['application_deadline'=>'date']; }
 public function applications(){ return $this->hasMany(JobApplication::class); }
}
