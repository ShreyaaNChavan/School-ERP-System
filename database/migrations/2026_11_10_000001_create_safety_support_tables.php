<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('safety_incidents')) {
            Schema::create('safety_incidents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->string('reference_number', 40)->unique();
                $table->foreignId('reported_by')->constrained('users');
                $table->foreignId('student_id')->nullable()->constrained('student_profiles')->nullOnDelete();
                $table->string('title');
                $table->string('incident_type', 100);
                $table->string('severity', 20)->default('medium');
                $table->dateTime('incident_at');
                $table->string('location');
                $table->text('description');
                $table->text('action_taken')->nullable();
                $table->string('status', 30)->default('reported');
                $table->timestamps();
                $table->index(['school_id', 'status']);
            });
        }

        if (!Schema::hasTable('emergency_control_logs')) {
            Schema::create('emergency_control_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->string('reference_number', 40)->unique();
                $table->foreignId('reported_by')->constrained('users');
                $table->string('title');
                $table->string('emergency_type', 100);
                $table->string('severity', 20)->default('high');
                $table->string('location');
                $table->text('description');
                $table->string('response_lead', 180)->nullable();
                $table->string('status', 30)->default('active');
                $table->dateTime('started_at')->nullable();
                $table->dateTime('closed_at')->nullable();
                $table->timestamps();
                $table->index(['school_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_control_logs');
        Schema::dropIfExists('safety_incidents');
    }
};
