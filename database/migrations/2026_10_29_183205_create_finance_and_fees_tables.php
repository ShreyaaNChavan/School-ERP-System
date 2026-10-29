<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()
                ->constrained('school_classes')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id']);
        });

        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('category', 50)->default('tuition');
            $table->boolean('is_refundable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')
                ->constrained('fee_structures')->cascadeOnDelete();
            $table->foreignId('fee_type_id')
                ->constrained('fee_types')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('frequency', 30)->default('annual');
            $table->unsignedSmallInteger('installment_number')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['fee_structure_id', 'due_date']);
        });

        Schema::create('student_fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('fee_structure_id')
                ->constrained('fee_structures')->restrictOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->decimal('concession_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(
                ['student_id', 'academic_year_id', 'fee_structure_id'],
                'student_fee_assignment_unique'
            );
        });

        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->restrictOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->restrictOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('unpaid');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['school_id', 'invoice_date']);
        });

        Schema::create('fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')
                ->constrained('fee_invoices')->cascadeOnDelete();
            $table->foreignId('fee_type_id')
                ->constrained('fee_types')->restrictOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_amount', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });

        Schema::create('fee_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('discount_type', 20)->default('fixed');
            $table->decimal('value', 12, 2);
            $table->text('eligibility_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
        });

        Schema::create('student_fee_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('fee_discount_id')
                ->constrained('fee_discounts')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->text('eligibility_criteria')->nullable();
            $table->string('award_type', 20)->default('fixed');
            $table->decimal('award_value', 12, 2);
            $table->date('application_deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')
                ->constrained('scholarships')->restrictOnDelete();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->decimal('awarded_amount', 12, 2)->nullable();
            $table->string('status', 30)->default('applied');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['scholarship_id', 'student_id', 'academic_year_id'],
                'student_scholarship_year_unique'
            );
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')
                ->constrained('fee_invoices')->restrictOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30);
            $table->string('transaction_reference')->nullable();
            $table->dateTime('paid_at');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fee_invoice_id', 'paid_at']);
        });

        Schema::create('fee_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_payment_id')
                ->constrained('fee_payments')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->string('status', 30)->default('requested');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('school_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()
                ->constrained('campuses')->nullOnDelete();
            $table->string('category', 100);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('vendor_name')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'expense_date']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_expenses');
        Schema::dropIfExists('fee_refunds');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('student_scholarships');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('student_fee_discounts');
        Schema::dropIfExists('fee_discounts');
        Schema::dropIfExists('fee_invoice_items');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('student_fee_assignments');
        Schema::dropIfExists('fee_structure_items');
        Schema::dropIfExists('fee_types');
        Schema::dropIfExists('fee_structures');
    }
};
