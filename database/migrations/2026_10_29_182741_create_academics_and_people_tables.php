
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()
                ->constrained('users')->nullOnDelete();

            $table->string('full_name');
            $table->string('relationship', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('alternate_phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();

            $table->index('phone');
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')
                ->constrained('campuses')->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->unique()
                ->constrained('users')->nullOnDelete();

            $table->string('admission_number');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('photo_path')->nullable();
            $table->date('admission_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'admission_number']);
            $table->index(['campus_id', 'status']);
            $table->index(['last_name', 'first_name']);
        });

        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('guardian_id')
                ->constrained('guardians')->cascadeOnDelete();

            $table->boolean('is_primary')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('can_pick_up_student')->default(true);
            $table->boolean('receives_communications')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'guardian_id']);
            $table->index(['student_id', 'is_primary']);
        });

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedSmallInteger('grade_level')->nullable();
            $table->string('education_stage', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
            $table->index(['school_id', 'is_active']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')
                ->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('campus_id')
                ->constrained('campuses')->cascadeOnDelete();

            $table->string('name');
            $table->string('room_number')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_class_id', 'campus_id', 'name']);
            $table->index(['campus_id', 'is_active']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')->cascadeOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->string('subject_type', 30)->default('theory');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
            $table->index(['school_id', 'is_active']);
        });

        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')
                ->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects')->cascadeOnDelete();

            $table->unsignedSmallInteger('weekly_periods')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->unique(['school_class_id', 'subject_id']);
        });

        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')
                ->constrained('school_classes')->restrictOnDelete();
            $table->foreignId('section_id')
                ->constrained('sections')->restrictOnDelete();

            $table->date('enrollment_date')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(
                ['student_id', 'academic_year_id'],
                'student_enrollments_student_year_unique'
            );
            $table->index(['section_id', 'status']);
            $table->index(['academic_year_id', 'status']);
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')
                ->constrained('campuses')->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->unique()
                ->constrained('users')->nullOnDelete();

            $table->string('employee_number');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('employment_type', 30)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('status', 30)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'employee_number']);
            $table->index(['campus_id', 'status']);
        });

        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')->cascadeOnDelete();
            $table->foreignId('class_subject_id')
                ->constrained('class_subjects')->cascadeOnDelete();
            $table->foreignId('section_id')
                ->constrained('sections')->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                [
                    'staff_profile_id',
                    'class_subject_id',
                    'section_id',
                    'academic_year_id',
                ],
                'teaching_assignments_unique'
            );
            $table->index(['section_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('student_enrollments');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('student_guardians');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('guardians');
    }
};
