<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_chapters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('curriculum_id')
                ->constrained('curricula')
                ->cascadeOnDelete();

            $table->unsignedInteger('chapter_number');
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

            $table->unique(
                ['curriculum_id', 'chapter_number'],
                'curriculum_chapter_number_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_chapters');
    }
};
