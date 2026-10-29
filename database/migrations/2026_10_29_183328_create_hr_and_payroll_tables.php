
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('head_staff_id')->nullable()
                ->constrained('staff_profiles')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('grade_level')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('employee_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status', 30)->default('present');
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['staff_profile_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('annual_allowance')->nullable();
            $table->boolean('carry_forward_allowed')->default(false);
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')->cascadeOnDelete();
            $table->foreignId('leave_type_id')
                ->constrained('leave_types')->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->decimal('allocated_days', 6, 2)->default(0);
            $table->decimal('used_days', 6, 2)->default(0);
            $table->decimal('adjusted_days', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['staff_profile_id', 'leave_type_id', 'academic_year_id'],
                'leave_balances_staff_type_year_unique'
            );
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')->cascadeOnDelete();
            $table->foreignId('leave_type_id')
                ->constrained('leave_types')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 6, 2);
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('reviewed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->timestamps();

            $table->index(['staff_profile_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('recruitment_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()
                ->constrained('campuses')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()
                ->constrained('designations')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('vacancies')->default(1);
            $table->date('application_deadline')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_job_id')
                ->constrained('recruitment_jobs')->cascadeOnDelete();
            $table->string('applicant_name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('resume_path')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('status', 30)->default('received');
            $table->foreignId('reviewed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['recruitment_job_id', 'status']);
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payment_date')->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('processed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'start_date', 'end_date']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')
                ->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')->restrictOnDelete();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->string('payment_status', 30)->default('pending');
            $table->date('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_period_id', 'staff_profile_id']);
            $table->index(['staff_profile_id', 'payment_status']);
        });

        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_record_id')
                ->constrained('payroll_records')->cascadeOnDelete();
            $table->string('name');
            $table->string('component_type', 30);
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payroll_record_id', 'component_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_components');
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('recruitment_jobs');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('employee_attendance');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};
