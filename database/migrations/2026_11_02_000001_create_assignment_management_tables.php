<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('assignment_type', 30)->default('individual');
            $table->decimal('maximum_marks', 8, 2)->default(100);
            $table->decimal('passing_marks', 8, 2)->default(35);
            $table->date('start_date');
            $table->date('due_date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamps();
            $table->index(['section_id', 'due_date']);
        });

        Schema::create('assignment_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->string('criterion');
            $table->decimal('maximum_marks', 8, 2);
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->string('submission_path')->nullable();
            $table->longText('submission_text')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->longText('rubric_scores')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignment_rubrics');
        Schema::dropIfExists('assignments');
    }
};
