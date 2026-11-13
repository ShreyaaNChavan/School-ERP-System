<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('caspa_workflow_runs')) {
            Schema::create('caspa_workflow_runs', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('school_id')->index(); $table->unsignedBigInteger('workflow_id')->index();
                $table->string('workflow_name',150); $table->string('status',40)->default('started'); $table->unsignedBigInteger('triggered_by')->nullable();
                $table->json('context')->nullable(); $table->timestamp('started_at')->nullable(); $table->timestamp('finished_at')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('caspa_integrations')) {
            Schema::create('caspa_integrations', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('school_id')->index(); $table->string('name',150); $table->string('provider',100);
                $table->string('category',80); $table->text('endpoint_url')->nullable(); $table->longText('credentials')->nullable();
                $table->text('description')->nullable(); $table->string('status',30)->default('disconnected');
                $table->unsignedBigInteger('created_by')->nullable(); $table->timestamps();
            });
        }
        if (!Schema::hasTable('caspa_integration_logs')) {
            Schema::create('caspa_integration_logs', function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('school_id')->index(); $table->unsignedBigInteger('integration_id')->index();
                $table->string('event',80); $table->text('message')->nullable(); $table->timestamp('created_at')->useCurrent();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('caspa_integration_logs');
        Schema::dropIfExists('caspa_integrations');
        Schema::dropIfExists('caspa_workflow_runs');
    }
};
