
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date');
            $table->string('session_name', 50)->default('Full Day');
            $table->string('status', 30)->default('open');
            $table->timestamps();

            $table->unique(
                ['section_id', 'attendance_date', 'session_name'],
                'attendance_sessions_section_date_session_unique'
            );
        });

        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')
                ->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->string('status', 30)->default('present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')->restrictOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->string('title');
            $table->longText('instructions')->nullable();
            $table->date('assigned_date');
            $table->date('due_date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 30)->default('published');
            $table->timestamps();

            $table->index(['section_id', 'due_date']);
        });

        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_assignment_id')
                ->constrained('homework_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->string('submission_path')->nullable();
            $table->longText('submission_text')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->unique(['homework_assignment_id', 'student_id']);
        });

        Schema::create('study_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()
                ->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()
                ->constrained('subjects')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('material_type', 50)->default('document');
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'is_published']);
        });

        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->string('name');
            $table->string('exam_type', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });

        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')
                ->constrained('examinations')->cascadeOnDelete();
            $table->foreignId('school_class_id')
                ->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')
                ->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects')->restrictOnDelete();
            $table->date('exam_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('maximum_marks', 8, 2)->default(100);
            $table->decimal('passing_marks', 8, 2)->default(35);
            $table->string('room_number')->nullable();
            $table->timestamps();

            $table->unique(
                ['examination_id', 'section_id', 'subject_id'],
                'exam_subjects_exam_section_subject_unique'
            );
        });

        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_subject_id')
                ->constrained('exam_subjects')->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_subject_id', 'student_id']);
        });

        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('examination_id')
                ->constrained('examinations')->cascadeOnDelete();
            $table->decimal('total_marks', 10, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('overall_grade', 10)->nullable();
            $table->unsignedSmallInteger('rank')->nullable();
            $table->text('teacher_remarks')->nullable();
            $table->text('principal_remarks')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'examination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exam_subjects');
        Schema::dropIfExists('examinations');
        Schema::dropIfExists('study_materials');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_assignments');
        Schema::dropIfExists('student_attendance');
        Schema::dropIfExists('attendance_sessions');
    }
};
