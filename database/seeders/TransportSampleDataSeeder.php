<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransportSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        if (
            !Schema::hasTable('schools') || !Schema::hasTable('transport_vehicles')
            || !Schema::hasTable('transport_routes') || !Schema::hasTable('transport_stops')
        ) {
            $this->command?->warn('Transport tables or schools table are missing. Run migrations first.');
            return;
        }

        $school = DB::table('schools')->orderBy('id')->first();
        if (!$school) {
            $this->command?->warn('Create a school before running the transport seeder.');
            return;
        }

        $schoolId = (int) $school->id;
        $campusId = Schema::hasTable('campuses')
            ? DB::table('campuses')->where('school_id', $schoolId)->value('id')
            : null;

        $vehicles = [
            [
                'registration_number' => 'MH12AB4821',
                'vehicle_type' => 'Bus',
                'make_model' => 'Tata Starbus 32 Seater',
                'capacity' => 32,
                'insurance_expiry' => now()->addMonths(9)->toDateString(),
                'permit_expiry' => now()->addMonths(7)->toDateString(),
                'status' => 'active',
            ],
            [
                'registration_number' => 'MH12CD7394',
                'vehicle_type' => 'Bus',
                'make_model' => 'Ashok Leyland MiTR 25 Seater',
                'capacity' => 25,
                'insurance_expiry' => now()->addMonths(11)->toDateString(),
                'permit_expiry' => now()->addMonths(8)->toDateString(),
                'status' => 'active',
            ],
            [
                'registration_number' => 'MH12EF6158',
                'vehicle_type' => 'Van',
                'make_model' => 'Force Traveller 17 Seater',
                'capacity' => 17,
                'insurance_expiry' => now()->addMonths(6)->toDateString(),
                'permit_expiry' => now()->addMonths(5)->toDateString(),
                'status' => 'maintenance',
            ],
        ];

        $vehicleIds = [];
        foreach ($vehicles as $vehicle) {
            $existing = DB::table('transport_vehicles')
                ->where('registration_number', $vehicle['registration_number'])
                ->first();

            $payload = $vehicle + [
                'school_id' => $schoolId,
                'campus_id' => $campusId,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('transport_vehicles')->where('id', $existing->id)->update($payload);
                $vehicleIds[$vehicle['registration_number']] = (int) $existing->id;
            } else {
                $payload['created_at'] = now();
                $vehicleIds[$vehicle['registration_number']] = (int) DB::table('transport_vehicles')->insertGetId($payload);
            }
        }

        $routes = [
            [
                'name' => 'Kothrud - School Campus',
                'code' => 'TR-01',
                'distance_km' => 12.5,
                'default_fee' => 1800,
                'stops' => [
                    ['name' => 'Kothrud Depot', 'address' => 'Kothrud Depot, Pune', 'latitude' => 18.5074, 'longitude' => 73.8077, 'pickup_time' => '07:05:00', 'drop_time' => '14:20:00'],
                    ['name' => 'Karve Statue', 'address' => 'Karve Statue, Pune', 'latitude' => 18.5018, 'longitude' => 73.8120, 'pickup_time' => '07:15:00', 'drop_time' => '14:10:00'],
                    ['name' => 'Warje Bridge', 'address' => 'Warje Bridge, Pune', 'latitude' => 18.4806, 'longitude' => 73.8071, 'pickup_time' => '07:28:00', 'drop_time' => '13:58:00'],
                ],
            ],
            [
                'name' => 'Hadapsar - School Campus',
                'code' => 'TR-02',
                'distance_km' => 16.8,
                'default_fee' => 2200,
                'stops' => [
                    ['name' => 'Hadapsar Gadital', 'address' => 'Hadapsar Gadital, Pune', 'latitude' => 18.5018, 'longitude' => 73.9260, 'pickup_time' => '06:55:00', 'drop_time' => '14:35:00'],
                    ['name' => 'Magarpatta Gate', 'address' => 'Magarpatta City Gate, Pune', 'latitude' => 18.5158, 'longitude' => 73.9270, 'pickup_time' => '07:08:00', 'drop_time' => '14:22:00'],
                    ['name' => 'Fatima Nagar', 'address' => 'Fatima Nagar, Pune', 'latitude' => 18.4967, 'longitude' => 73.9005, 'pickup_time' => '07:20:00', 'drop_time' => '14:10:00'],
                ],
            ],
            [
                'name' => 'Pimpri - School Campus',
                'code' => 'TR-03',
                'distance_km' => 19.2,
                'default_fee' => 2500,
                'stops' => [
                    ['name' => 'Pimpri Market', 'address' => 'Pimpri Market, Pune', 'latitude' => 18.6270, 'longitude' => 73.8009, 'pickup_time' => '06:45:00', 'drop_time' => '14:45:00'],
                    ['name' => 'Nashik Phata', 'address' => 'Nashik Phata, Pune', 'latitude' => 18.5933, 'longitude' => 73.8212, 'pickup_time' => '07:00:00', 'drop_time' => '14:30:00'],
                    ['name' => 'Kasarwadi', 'address' => 'Kasarwadi, Pune', 'latitude' => 18.5822, 'longitude' => 73.8217, 'pickup_time' => '07:10:00', 'drop_time' => '14:20:00'],
                ],
            ],
        ];

        $routeIds = [];
        foreach ($routes as $routeData) {
            $route = DB::table('transport_routes')
                ->where('school_id', $schoolId)
                ->where('name', $routeData['name'])
                ->first();

            $routePayload = [
                'school_id' => $schoolId,
                'name' => $routeData['name'],
                'code' => $routeData['code'],
                'distance_km' => $routeData['distance_km'],
                'default_fee' => $routeData['default_fee'],
                'is_active' => true,
                'updated_at' => now(),
            ];

            if ($route) {
                DB::table('transport_routes')->where('id', $route->id)->update($routePayload);
                $routeId = (int) $route->id;
            } else {
                $routePayload['created_at'] = now();
                $routeId = (int) DB::table('transport_routes')->insertGetId($routePayload);
            }

            $routeIds[] = $routeId;
            foreach ($routeData['stops'] as $index => $stop) {
                $stopPayload = $stop + [
                    'transport_route_id' => $routeId,
                    'sequence' => $index + 1,
                    'updated_at' => now(),
                ];

                $existingStop = DB::table('transport_stops')
                    ->where('transport_route_id', $routeId)
                    ->where('sequence', $index + 1)
                    ->first();

                if ($existingStop) {
                    DB::table('transport_stops')->where('id', $existingStop->id)->update($stopPayload);
                } else {
                    $stopPayload['created_at'] = now();
                    DB::table('transport_stops')->insert($stopPayload);
                }
            }
        }

        if (Schema::hasTable('staff_profiles') && Schema::hasTable('transport_driver_assignments')) {
            $drivers = DB::table('staff_profiles')
                ->where('school_id', $schoolId)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->limit(2)
                ->pluck('id')
                ->all();

            foreach (array_slice($drivers, 0, 2) as $index => $driverId) {
                $vehicleId = $index === 0
                    ? ($vehicleIds['MH12AB4821'] ?? null)
                    : ($vehicleIds['MH12CD7394'] ?? null);

                if (!$vehicleId) {
                    continue;
                }

                $alreadyAssigned = DB::table('transport_driver_assignments')
                    ->where('transport_vehicle_id', $vehicleId)
                    ->where('staff_profile_id', $driverId)
                    ->whereNull('end_date')
                    ->exists();

                if (!$alreadyAssigned) {
                    DB::table('transport_driver_assignments')->insert([
                        'transport_vehicle_id' => $vehicleId,
                        'staff_profile_id' => $driverId,
                        'start_date' => now()->toDateString(),
                        'end_date' => null,
                    ]);
                }
            }
        }

        if (Schema::hasTable('vehicle_maintenance')) {
            $vehicleId = $vehicleIds['MH12EF6158'] ?? null;
            if ($vehicleId) {
                $exists = DB::table('vehicle_maintenance')
                    ->where('transport_vehicle_id', $vehicleId)
                    ->where('service_type', 'Routine inspection')
                    ->exists();

                if (!$exists) {
                    DB::table('vehicle_maintenance')->insert([
                        'transport_vehicle_id' => $vehicleId,
                        'service_date' => now()->subDays(4)->toDateString(),
                        'next_service_date' => now()->addDays(26)->toDateString(),
                        'service_type' => 'Routine inspection',
                        'description' => 'Brake inspection and general vehicle servicing',
                        'cost' => 4500,
                        'vendor_name' => 'Pune Fleet Care',
                        'status' => 'scheduled',
                    ]);
                }
            }
        }

        $this->command?->info('Transport sample records created successfully.');
        $this->command?->line('Vehicles: 3 | Routes: 3 | Stops: 9 | Driver assignments depend on available staff profiles.');
        $this->call(TransportSampleDataSeeder::class);
    }
}
