
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One vehicle is assigned to each route.
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->foreignId('transport_vehicle_id')
                ->nullable()
                ->after('school_id')
                ->constrained('transport_vehicles')
                ->nullOnDelete();

            $table->unique(
                'transport_vehicle_id',
                'transport_routes_vehicle_unique'
            );
        });

        // GPS history: every device update can create a new record.
        Schema::create('vehicle_gps_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transport_vehicle_id')
                ->constrained('transport_vehicles')
                ->cascadeOnDelete();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed_kmph', 7, 2)->nullable();
            $table->string('source', 30)->default('device');
            $table->boolean('is_demo')->default(false);
            $table->timestamp('recorded_at');

            $table->timestamps();

            $table->index(
                ['transport_vehicle_id', 'recorded_at'],
                'vehicle_gps_vehicle_recorded_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_gps_locations');

        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropUnique('transport_routes_vehicle_unique');
            $table->dropConstrainedForeignId('transport_vehicle_id');
        });
    }
};
