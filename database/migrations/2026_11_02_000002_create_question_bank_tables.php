<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained('curriculum_chapters')->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('curriculum_topics')->nullOnDelete();
            $table->string('question_type', 30)->default('mcq');
            $table->longText('question_text');
            $table->string('difficulty', 20)->default('medium');
            $table->decimal('marks', 8, 2)->default(1);
            $table->longText('answer_text')->nullable();
            $table->longText('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['subject_id', 'difficulty']);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('option_label', 5);
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};
