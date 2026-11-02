<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('campus_id')
                ->constrained('campuses')
                ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            $table->foreignId('school_class_id')
                ->constrained('school_classes')
                ->restrictOnDelete();

            $table->foreignId('section_id')
                ->constrained('sections')
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->unsignedInteger('version')->default(1);

            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'archived',
            ])->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(
                ['academic_year_id', 'section_id', 'status'],
                'timetables_year_section_status_idx'
            );

            $table->unique(
                ['academic_year_id', 'section_id', 'version'],
                'timetables_section_version_unique'
            );
        });

        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('timetable_id')
                ->constrained('timetables')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('period_number');
            $table->string('name');

            $table->enum('period_type', [
                'teaching',
                'break',
            ])->default('teaching');

            $table->time('start_time');
            $table->time('end_time');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['timetable_id', 'period_number'],
                'timetable_period_number_unique'
            );
        });

        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('timetable_id')
                ->constrained('timetables')
                ->cascadeOnDelete();

            $table->foreignId('timetable_period_id')
                ->constrained('timetable_periods')
                ->cascadeOnDelete();

            $table->foreignId('class_subject_id')
                ->nullable()
                ->constrained('class_subjects')
                ->nullOnDelete();

            $table->foreignId('staff_profile_id')
                ->nullable()
                ->constrained('staff_profiles')
                ->nullOnDelete();

            $table->enum('day_of_week', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
            ]);

            $table->string('room_number')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['timetable_period_id', 'day_of_week'],
                'timetable_period_day_unique'
            );

            $table->index(
                ['staff_profile_id', 'day_of_week'],
                'timetable_teacher_day_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
        Schema::dropIfExists('timetable_periods');
        Schema::dropIfExists('timetables');
    }
};
