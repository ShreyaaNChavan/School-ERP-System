<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('ptm_meetings')) {
            Schema::create('ptm_meetings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->index();
                $table->unsignedBigInteger('campus_id')->nullable()->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->unsignedBigInteger('teacher_id')->index();
                $table->string('parent_name', 180);
                $table->string('parent_phone', 30)->nullable();
                $table->date('meeting_date')->index();
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedSmallInteger('duration_minutes')->default(15);
                $table->string('meeting_type', 60)->default('General');
                $table->string('purpose', 1000);
                $table->string('status', 30)->default('pending')->index();
                $table->string('outcome', 1000)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ptm_teacher_availabilities')) {
            Schema::create('ptm_teacher_availabilities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->index();
                $table->unsignedBigInteger('teacher_id')->index();
                $table->unsignedTinyInteger('weekday');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedSmallInteger('slot_minutes')->default(15);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['school_id', 'teacher_id', 'weekday', 'start_time'], 'ptm_teacher_slot_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ptm_teacher_availabilities');
        Schema::dropIfExists('ptm_meetings');
    }
};
