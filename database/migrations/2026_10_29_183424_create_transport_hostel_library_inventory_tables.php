<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // TRANSPORT
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('registration_number')->unique();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('make_model')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('permit_expiry')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('default_fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('transport_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_route_id')
                ->constrained('transport_routes')->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->time('pickup_time')->nullable();
            $table->time('drop_time')->nullable();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->timestamps();

            $table->unique(['transport_route_id', 'sequence']);
        });

        Schema::create('transport_driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_vehicle_id')
                ->constrained('transport_vehicles')->cascadeOnDelete();
            $table->foreignId('staff_profile_id')
                ->constrained('staff_profiles')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            //$table->index(['transport_vehicle_id', 'start_date']);
            $table->index(
                ['transport_vehicle_id', 'start_date'],
                'td_assign_vehicle_start_idx'
            );
            $table->index(['staff_profile_id', 'end_date']);
        });

        Schema::create('student_transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('transport_route_id')
                ->constrained('transport_routes')->restrictOnDelete();
            $table->foreignId('transport_stop_id')->nullable()
                ->constrained('transport_stops')->nullOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->string('trip_type', 20)->default('both');
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['student_id', 'academic_year_id']);
            $table->index(['transport_route_id', 'status']);
        });

        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_vehicle_id')
                ->constrained('transport_vehicles')->cascadeOnDelete();
            $table->date('service_date');
            $table->date('next_service_date')->nullable();
            $table->string('service_type', 100);
            $table->text('description')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('vendor_name')->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->timestamps();

            $table->index(['transport_vehicle_id', 'service_date']);
        });

        // HOSTEL
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('name');
            $table->string('hostel_type', 30)->nullable();
            $table->string('warden_name')->nullable();
            $table->string('warden_phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->string('room_number');
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->string('room_type', 50)->nullable();
            $table->decimal('fee_per_term', 12, 2)->default(0);
            $table->string('status', 30)->default('available');
            $table->timestamps();

            $table->unique(['hostel_id', 'room_number']);
        });

        Schema::create('hostel_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->string('bed_number');
            $table->string('status', 30)->default('available');
            $table->timestamps();

            $table->unique(['hostel_room_id', 'bed_number']);
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('hostel_bed_id')
                ->constrained('hostel_beds')->restrictOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['hostel_bed_id', 'status']);
        });

        Schema::create('hostel_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('student_profiles')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('status', 30)->default('present');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date']);
        });

        // LIBRARY
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('isbn', 30)->nullable();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('edition', 50)->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'title']);
            $table->index('isbn');
        });

        Schema::create('library_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_book_id')->constrained('library_books')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('accession_number')->unique();
            $table->string('shelf_location')->nullable();
            $table->string('status', 30)->default('available');
            $table->timestamps();

            $table->index(['library_book_id', 'status']);
        });

        Schema::create('library_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_copy_id')->constrained('library_copies')->restrictOnDelete();
            $table->foreignId('student_id')->nullable()
                ->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('staff_profile_id')->nullable()
                ->constrained('staff_profiles')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issued_at');
            $table->date('due_at');
            $table->date('returned_at')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->string('status', 30)->default('issued');
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['staff_profile_id', 'status']);
            $table->index(['due_at', 'status']);
        });

        // INVENTORY AND ASSETS
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_identifier', 50)->nullable();
            $table->timestamps();

            $table->index(['school_id', 'name']);
        });

        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('inventory_category_id')->nullable()
                ->constrained('inventory_categories')->nullOnDelete();
            $table->string('name');
            $table->string('item_code');
            $table->string('unit', 30)->default('piece');
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'item_code']);
            $table->index(['school_id', 'name']);
        });

        Schema::create('asset_register', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('inventory_category_id')->nullable()
                ->constrained('inventory_categories')->nullOnDelete();
            $table->string('asset_tag')->unique();
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->string('condition', 30)->default('good');
            $table->string('location')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_cost', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('asset_register');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_categories');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('library_loans');
        Schema::dropIfExists('library_copies');
        Schema::dropIfExists('library_books');
        Schema::dropIfExists('hostel_attendance');
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_beds');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostels');
        Schema::dropIfExists('vehicle_maintenance');
        Schema::dropIfExists('student_transport_assignments');
        Schema::dropIfExists('transport_driver_assignments');
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('transport_vehicles');
    }
};
