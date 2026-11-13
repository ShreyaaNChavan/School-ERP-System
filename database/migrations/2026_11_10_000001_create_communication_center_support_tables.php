<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('conversation_messages')) {
            Schema::create('conversation_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id')->index();
                $table->unsignedBigInteger('sender_id')->index();
                $table->longText('body');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('communication_templates')) {
            Schema::create('communication_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('name', 150);
                $table->string('category', 80);
                $table->string('subject')->nullable();
                $table->longText('body');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('communication_deliveries')) {
            Schema::create('communication_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id')->index();
                $table->string('channel', 30)->default('in_app');
                $table->string('status', 30)->default('queued');
                $table->unsignedInteger('recipient_count')->default(0);
                $table->unsignedInteger('delivered_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->text('failure_reason')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_deliveries');
        Schema::dropIfExists('communication_templates');
        Schema::dropIfExists('conversation_messages');
    }
};
