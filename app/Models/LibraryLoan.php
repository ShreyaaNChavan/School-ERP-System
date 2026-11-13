<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryLoan extends Model
{
    protected $table = 'library_loans';
    protected $fillable = ['library_copy_id','student_id','staff_profile_id','issued_by','received_by','issued_at','due_at','returned_at','fine_amount','status'];
    protected $casts = ['issued_at'=>'date','due_at'=>'date','returned_at'=>'date','fine_amount'=>'decimal:2'];
    public function copy(): BelongsTo { return $this->belongsTo(LibraryCopy::class, 'library_copy_id'); }
    public function student(): BelongsTo { return $this->belongsTo(StudentProfile::class, 'student_id'); }
    public function staff(): BelongsTo { return $this->belongsTo(StaffProfile::class, 'staff_profile_id'); }
}
