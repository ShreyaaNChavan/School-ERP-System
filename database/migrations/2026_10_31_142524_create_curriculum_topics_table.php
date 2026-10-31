<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_topics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('chapter_id')
                ->constrained('curriculum_chapters')
                ->cascadeOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('estimated_periods')->default(0);

            $table->enum('status', [
                'planned',
                'in_progress',
                'completed',
            ])->default('planned');

            $table->unsignedInteger('sequence')->default(0);
            $table->date('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_topics');
    }
};
