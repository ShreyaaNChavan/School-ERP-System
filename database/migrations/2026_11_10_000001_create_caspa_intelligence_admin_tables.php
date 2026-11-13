<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('caspa_ai_conversations', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('school_id')->index(); $t->unsignedBigInteger('user_id')->index();
            $t->string('role_context', 30)->default('general'); $t->string('title')->default('New conversation'); $t->string('status', 20)->default('active'); $t->timestamps();
        });
        Schema::create('caspa_ai_messages', function (Blueprint $t) {
            $t->id(); $t->foreignId('conversation_id')->constrained('caspa_ai_conversations')->cascadeOnDelete();
            $t->string('sender', 20); $t->longText('content'); $t->string('provider_response_id')->nullable(); $t->string('model')->nullable(); $t->unsignedInteger('input_tokens')->default(0); $t->unsignedInteger('output_tokens')->default(0); $t->timestamps();
        });
        Schema::create('caspa_work_items', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('school_id')->index(); $t->unsignedBigInteger('created_by')->index();
            $t->string('module', 50)->index(); $t->string('title'); $t->json('payload')->nullable(); $t->string('status', 30)->default('draft')->index();
            $t->unsignedBigInteger('assigned_to')->nullable()->index(); $t->text('remarks')->nullable(); $t->timestamp('due_at')->nullable(); $t->timestamps();
        });
        Schema::create('caspa_platform_records', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('school_id')->index(); $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->string('record_type', 60)->index(); $t->string('record_key')->nullable(); $t->json('payload')->nullable(); $t->timestamps();
            $t->index(['school_id','record_type']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('caspa_platform_records'); Schema::dropIfExists('caspa_work_items'); Schema::dropIfExists('caspa_ai_messages'); Schema::dropIfExists('caspa_ai_conversations');
    }
};
