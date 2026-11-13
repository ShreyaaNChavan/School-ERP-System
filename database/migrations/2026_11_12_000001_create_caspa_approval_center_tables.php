<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('caspa_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->string('request_number', 40)->unique();
            $table->string('title', 190);
            $table->string('request_type', 80)->index();
            $table->string('department', 100)->nullable();
            $table->longText('description');
            $table->string('priority', 20)->default('medium')->index();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedBigInteger('submitted_by')->index();
            $table->string('submitted_name', 150);
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->dateTime('due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('caspa_approval_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_request_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('action', 30);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('caspa_approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->string('name', 150);
            $table->longText('definition');
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('caspa_approval_delegations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('delegate_user_id')->index();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('reason', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caspa_approval_delegations');
        Schema::dropIfExists('caspa_approval_workflows');
        Schema::dropIfExists('caspa_approval_actions');
        Schema::dropIfExists('caspa_approval_requests');
    }
};
