
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Main lesson plans
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            $table->foreignId('term_id')
                ->nullable()
                ->constrained('terms')
                ->nullOnDelete();

            $table->foreignId('class_subject_id')
                ->constrained('class_subjects')
                ->restrictOnDelete();

            $table->foreignId('section_id')
                ->constrained('sections')
                ->restrictOnDelete();

            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')
                ->restrictOnDelete();

            $table->foreignId('curriculum_topic_id')
                ->nullable()
                ->constrained('curriculum_topics')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            $table->date('scheduled_date');
            $table->unsignedSmallInteger('duration_minutes')->default(40);

            $table->string('teaching_method', 100)->nullable();
            $table->text('teacher_notes')->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('draft');

            $table->timestamp('published_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rescheduled_at')->nullable();
            $table->text('reschedule_reason')->nullable();

            $table->timestamps();

            $table->index(
                ['school_id', 'academic_year_id', 'scheduled_date'],
                'lesson_plans_school_year_date_index'
            );

            $table->index(['staff_profile_id', 'scheduled_date']);
            $table->index(['section_id', 'scheduled_date']);
            $table->index('status');
        });

        // 2. Learning objectives
        Schema::create('lesson_plan_objectives', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_plan_id')
                ->constrained('lesson_plans')
                ->cascadeOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            $table->unsignedInteger('sequence')->default(0);

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['lesson_plan_id', 'sequence']);
        });

        // 3. Teaching activities
        Schema::create('lesson_plan_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_plan_id')
                ->constrained('lesson_plans')
                ->cascadeOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            $table->string('activity_type', 50)->nullable();

            $table->unsignedSmallInteger('duration_minutes')
                ->default(0);

            $table->unsignedInteger('sequence')->default(0);

            $table->timestamps();

            $table->index(['lesson_plan_id', 'sequence']);
        });

        // 4. Lesson resources
        Schema::create('lesson_plan_resources', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lesson_plan_id')
                ->constrained('lesson_plans')
                ->cascadeOnDelete();

            $table->string('title', 200);

            $table->string('resource_type', 50);

            $table->text('description')->nullable();

            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plan_resources');
        Schema::dropIfExists('lesson_plan_activities');
        Schema::dropIfExists('lesson_plan_objectives');
        Schema::dropIfExists('lesson_plans');
    }
};
