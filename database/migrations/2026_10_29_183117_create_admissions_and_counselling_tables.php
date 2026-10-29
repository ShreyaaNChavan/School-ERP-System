
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('student_name');
            $table->string('parent_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('prospective_grade')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('status', 30)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['phone', 'email']);
        });

        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_lead_id')->nullable()
                ->constrained('admission_leads')->nullOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->restrictOnDelete();

            $table->string('application_number')->unique();
            $table->string('applicant_name');
            $table->date('date_of_birth')->nullable();
            $table->string('applying_grade');
            $table->string('parent_name')->nullable();
            $table->string('parent_phone', 30)->nullable();
            $table->string('parent_email')->nullable();
            $table->json('documents')->nullable();
            $table->string('status', 30)->default('submitted');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['academic_year_id', 'applying_grade']);
        });

        Schema::create('admission_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_lead_id')
                ->constrained('admission_leads')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('followup_type', 50)->default('call');
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->index(['admission_lead_id', 'status']);
            $table->index(['assigned_to', 'scheduled_at']);
        });

        Schema::create('admission_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->constrained('admission_applications')->cascadeOnDelete();
            $table->string('assessment_type', 100);
            $table->date('assessment_date')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('maximum_score', 8, 2)->nullable();
            $table->string('result', 30)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['application_id', 'assessment_date']);
        });

        Schema::create('admission_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                ->constrained('admission_applications')->cascadeOnDelete();
            $table->string('decision', 30);
            $table->text('remarks')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('offer_expires_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'decision']);
        });

        Schema::create('counselling_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()
                ->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('admission_application_id')->nullable()
                ->constrained('admission_applications')->nullOnDelete();

            $table->foreignId('counsellor_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('session_at');
            $table->string('session_type', 50)->nullable();
            $table->text('summary');
            $table->text('action_plan')->nullable();
            $table->timestamp('next_session_at')->nullable();
            $table->string('status', 30)->default('open');
            $table->timestamps();

            $table->index(['school_id', 'session_at']);
            $table->index(['counsellor_id', 'session_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselling_records');
        Schema::dropIfExists('admission_decisions');
        Schema::dropIfExists('admission_assessments');
        Schema::dropIfExists('admission_followups');
        Schema::dropIfExists('admission_applications');
        Schema::dropIfExists('admission_leads');
    }
};
