<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoice extends Model
{
    protected $table = 'fee_invoices';

    protected $fillable = [
        'school_id', 'student_id', 'academic_year_id', 'invoice_number',
        'invoice_date', 'due_date', 'subtotal', 'discount_amount', 'tax_amount',
        'total_amount', 'paid_amount', 'balance_amount', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function items()
    {
        return $this->hasMany(FeeInvoiceItem::class, 'fee_invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'fee_invoice_id');
    }
}
