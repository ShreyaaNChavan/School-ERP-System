
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()
                ->constrained('users')->cascadeOnDelete();

            $table->string('employee_code')->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('avatar_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('address_line')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnDelete();

            $table->string('status', 30)->default('active');
            $table->foreignId('invited_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('school_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnDelete();

            $table->string('status', 30)->default('active');
            $table->foreignId('invited_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('campus_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')
                ->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnDelete();

            $table->string('status', 30)->default('active');
            $table->foreignId('invited_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['campus_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('scope_type', 30)->default('school');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['scope_type', 'is_active']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('module', 100);
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['module', 'action']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')
                ->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')
                ->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_role_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')
                ->constrained('roles')->cascadeOnDelete();

            $table->foreignId('organization_id')->nullable()
                ->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()
                ->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()
                ->constrained('campuses')->cascadeOnDelete();

            $table->foreignId('assigned_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['role_id', 'is_active']);
            $table->index(['organization_id', 'school_id', 'campus_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('campus_memberships');
        Schema::dropIfExists('school_memberships');
        Schema::dropIfExists('organization_memberships');
        Schema::dropIfExists('user_profiles');
    }
};
