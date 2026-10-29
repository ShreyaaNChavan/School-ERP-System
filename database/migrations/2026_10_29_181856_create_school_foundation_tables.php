
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Organizations
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('slug', 150)->unique();
            $table->string('legal_name')->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('primary_email')->nullable();
            $table->string('primary_phone', 30)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('timezone', 64)->default('Asia/Kolkata');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['status', 'name']);
        });

        // 2. Schools
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();

            $table->string('name', 200);
            $table->string('code', 50);
            $table->string('slug', 150);
            $table->string('school_type', 40)->nullable();
            $table->string('affiliation_number', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('timezone', 64)->default('Asia/Kolkata');
            $table->char('default_currency', 3)->default('INR');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(
                ['organization_id', 'code'],
                'schools_org_code_unique'
            );

            $table->unique(
                ['organization_id', 'slug'],
                'schools_org_slug_unique'
            );

            $table->index(
                ['organization_id', 'status'],
                'schools_org_status_index'
            );
        });

        // 3. Campuses
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->restrictOnDelete();

            $table->string('name', 200);
            $table->string('code', 50);
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('timezone', 64)->default('Asia/Kolkata');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(
                ['school_id', 'code'],
                'campuses_school_code_unique'
            );

            $table->index(
                ['school_id', 'status'],
                'campuses_school_status_index'
            );
        });

        // 4. Academic years
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->restrictOnDelete();

            $table->string('name', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('planned');
            $table->timestamps();

            $table->unique(
                ['school_id', 'name'],
                'academic_years_school_name_unique'
            );

            $table->index(
                ['school_id', 'status'],
                'academic_years_school_status_index'
            );
        });

        // 5. Academic terms
        Schema::create('terms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->restrictOnDelete();

            $table->string('name', 100);
            $table->string('code', 30);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('sequence_number');
            $table->string('status', 20)->default('planned');
            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'code'],
                'terms_year_code_unique'
            );

            $table->unique(
                ['academic_year_id', 'sequence_number'],
                'terms_year_sequence_unique'
            );

            $table->index(['start_date', 'end_date']);
        });

        // 6. School settings
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->string('setting_key', 150);
            $table->json('setting_value')->nullable();
            $table->timestamps();

            $table->unique(
                ['school_id', 'setting_key'],
                'school_settings_key_unique'
            );
        });

        // 7. Campus settings
        Schema::create('campus_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campus_id')
                ->constrained('campuses')
                ->cascadeOnDelete();

            $table->string('setting_key', 150);
            $table->json('setting_value')->nullable();
            $table->timestamps();

            $table->unique(
                ['campus_id', 'setting_key'],
                'campus_settings_key_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_settings');
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('campuses');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('organizations');
    }
};
