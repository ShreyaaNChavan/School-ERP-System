<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ANNOUNCEMENTS AND COMMUNICATION
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('audience_type', 50)->default('school');
            $table->json('audience_rules')->nullable();
            $table->string('priority', 30)->default('normal');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamps();

            $table->index(['school_id', 'status', 'published_at']);
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')
                ->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()
                ->constrained('schools')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('conversation_type', 30)->default('direct');
            $table->timestamps();

            $table->index(['school_id', 'created_at']);
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->longText('body');
            $table->json('attachments')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('event_type', 50)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('venue')->nullable();
            $table->string('status', 30)->default('planned');
            $table->timestamps();

            $table->index(['school_id', 'starts_at']);
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()
                ->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('staff_profile_id')->nullable()
                ->constrained('staff_profiles')->nullOnDelete();
            $table->string('registration_status', 30)->default('registered');
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'registration_status']);
        });

        // VISITORS AND SECURITY
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('phone', 30)->nullable();
            $table->string('identity_reference')->nullable();
            $table->string('purpose');
            $table->string('person_to_visit')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();
            $table->foreignId('recorded_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'check_in_at']);
        });

        // COMPLAINTS AND SAFEGUARDING
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('reference_number')->unique();
            $table->string('category', 100);
            $table->string('subject');
            $table->longText('description');
            $table->string('priority', 30)->default('normal');
            $table->string('status', 30)->default('open');
            $table->foreignId('assigned_to')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status', 'priority']);
        });

        Schema::create('safeguarding_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('case_reference')->unique();
            $table->foreignId('student_id')->nullable()
                ->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('case_owner')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('category', 100);
            $table->string('severity', 30)->default('medium');
            $table->string('status', 30)->default('open');
            $table->text('restricted_summary');
            $table->timestamp('reported_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status', 'severity']);
        });

        Schema::create('safeguarding_case_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safeguarding_case_id')
                ->constrained('safeguarding_cases')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->longText('note');
            $table->boolean('is_restricted')->default(true);
            $table->timestamps();

            $table->index(['safeguarding_case_id', 'created_at']);
        });

        // AUDIT LOGS
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('school_id')->nullable()
                ->constrained('schools')->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 150)->nullable();
            $table->string('entity_id', 100)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['school_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });

        // WORKFLOWS AND APPROVALS
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()
                ->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('entity_type', 100);
            $table->json('steps');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('workflow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')
                ->constrained('workflow_definitions')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('entity_type', 100);
            $table->string('entity_id', 100);
            $table->unsignedSmallInteger('current_step')->default(1);
            $table->string('status', 30)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_request_id')
                ->constrained('workflow_requests')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('step_number');
            $table->string('decision', 30)->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            //$table->unique(['workflow_request_id', 'step_number', 'approver_id']);
            $table->unique(
                ['workflow_request_id', 'step_number', 'approver_id'],
                'wf_approval_step_approver_unique'
            );
            $table->index(['approver_id', 'decision']);
        });

        // PLATFORM CONFIGURATION AND INTEGRATIONS
        Schema::create('integration_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()
                ->constrained('schools')->cascadeOnDelete();
            $table->string('provider');
            $table->string('purpose', 100);
            $table->json('configuration')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'provider']);
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()
                ->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()
                ->constrained('campuses')->cascadeOnDelete();
            $table->string('setting_key');
            $table->longText('setting_value')->nullable();
            $table->string('value_type', 30)->default('string');
            $table->timestamps();

            $table->unique(['school_id', 'campus_id', 'setting_key'], 'system_settings_scope_key_unique');
        });

        // STUDENT ACTIVITIES
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->string('activity_type', 100);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('activity_date')->nullable();
            $table->string('achievement')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'activity_date']);
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()
                ->constrained('student_profiles')->nullOnDelete();
            $table->string('certificate_type', 100);
            $table->string('certificate_number')->unique();
            $table->date('issued_date');
            $table->string('file_path')->nullable();
            $table->foreignId('issued_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'issued_date']);
        });

        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->string('contact_name');
            $table->string('relationship', 50)->nullable();
            $table->string('phone', 30);
            $table->string('alternate_phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->unsignedSmallInteger('priority')->default(1);
            $table->timestamps();

            $table->index(['student_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('student_activities');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('integration_configs');
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('workflow_requests');
        Schema::dropIfExists('workflow_definitions');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('safeguarding_case_notes');
        Schema::dropIfExists('safeguarding_cases');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');
    }
};
