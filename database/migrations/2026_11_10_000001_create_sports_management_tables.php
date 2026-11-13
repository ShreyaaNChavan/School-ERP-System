<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); $table->string('category', 80)->nullable();
            $table->text('description')->nullable(); $table->boolean('is_active')->default(true);
            $table->timestamps(); $table->index(['school_id','name']);
        });
        Schema::create('sports_teams', function (Blueprint $table) {
            $table->id(); $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coach_staff_profile_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->string('name'); $table->string('team_type', 40)->default('mixed');
            $table->string('academic_year')->nullable(); $table->string('status',30)->default('active');
            $table->timestamps(); $table->index(['school_id','sport_id']);
        });
        Schema::create('sports_team_members', function (Blueprint $table) {
            $table->id(); $table->foreignId('team_id')->constrained('sports_teams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->string('jersey_number',20)->nullable(); $table->string('position',80)->nullable();
            $table->date('joined_on')->nullable(); $table->string('status',30)->default('active');
            $table->timestamps(); $table->unique(['team_id','student_id']);
        });
        Schema::create('sports_competitions', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->string('name'); $table->string('level',50)->nullable();
            $table->string('venue')->nullable(); $table->date('starts_on')->nullable(); $table->date('ends_on')->nullable();
            $table->string('status',30)->default('planned'); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('sports_matches', function (Blueprint $table) {
            $table->id(); $table->foreignId('competition_id')->constrained('sports_competitions')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('sports_teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('sports_teams')->cascadeOnDelete();
            $table->dateTime('scheduled_at')->nullable(); $table->string('venue')->nullable();
            $table->unsignedInteger('home_score')->nullable(); $table->unsignedInteger('away_score')->nullable();
            $table->string('status',30)->default('scheduled'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sports_matches'); Schema::dropIfExists('sports_competitions'); Schema::dropIfExists('sports_team_members'); Schema::dropIfExists('sports_teams'); Schema::dropIfExists('sports'); }
};
