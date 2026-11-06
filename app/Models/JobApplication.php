<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JobApplication extends Model {
 protected $fillable=['recruitment_job_id','applicant_name','email','phone','resume_path','cover_letter','status','reviewed_by'];
 public function job(){ return $this->belongsTo(RecruitmentJob::class,'recruitment_job_id'); }
}
