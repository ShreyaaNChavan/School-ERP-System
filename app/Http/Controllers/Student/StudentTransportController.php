<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StudentTransportController extends Controller
{
    private function getStudent()
    {
        $user = auth()->user();

        $isStudent = $user->roleAssignments()
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('code', 'STUDENT')
                    ->where('is_active', true);
            })
            ->exists();

        abort_unless($isStudent, 403, 'Student access only.');

        $student = $user->studentProfile;

        abort_unless($student, 403, 'Student profile not found.');

        return $student;
    }

    public function index()
    {
        $student = $this->getStudent();

        $assignment = DB::table('student_transport_assignments as sta')
            ->join('transport_routes as tr', 'tr.id', '=', 'sta.transport_route_id')

            ->leftJoin('transport_stops as ts', function ($join) {
                $join->on('ts.id', '=', 'sta.transport_stop_id')
                    ->on('ts.transport_route_id', '=', 'tr.id');
            })

            ->leftJoin('transport_vehicles as tv', 'tv.id', '=', 'tr.transport_vehicle_id')

            ->leftJoin('transport_driver_assignments as tda', function ($join) {
                $join->on('tda.transport_vehicle_id', '=', 'tv.id')
                    ->whereNull('tda.end_date');
            })

            ->leftJoin('staff_profiles as sp', 'sp.id', '=', 'tda.staff_profile_id')

            // Retrieve the latest GPS record for the assigned bus.
            ->leftJoin('vehicle_gps_locations as gps', function ($join) {
                $join->on('gps.transport_vehicle_id', '=', 'tv.id')
                    ->whereRaw(
                        'gps.id = (
                            SELECT MAX(gps2.id)
                            FROM vehicle_gps_locations gps2
                            WHERE gps2.transport_vehicle_id = tv.id
                        )'
                    );
            })

            ->where('sta.student_id', $student->id)
            ->where('tr.school_id', $student->school_id)
            ->where('sta.status', 'active')

            ->where(function ($query) {
                $query->whereNull('sta.start_date')
                    ->orWhereDate('sta.start_date', '<=', today());
            })

            ->where(function ($query) {
                $query->whereNull('sta.end_date')
                    ->orWhereDate('sta.end_date', '>=', today());
            })

            ->select([
                'sta.id',
                'sta.status',
                'sta.trip_type',
                'sta.fee_amount',
                'sta.start_date',

                'tr.name as route_name',
                'tr.code as route_code',
                'tr.distance_km',

                'ts.name as stop_name',
                'ts.address as stop_address',
                'ts.pickup_time',
                'ts.drop_time',

                'tv.registration_number',
                'tv.vehicle_type',
                'tv.make_model',

                'sp.first_name as driver_first_name',
                'sp.middle_name as driver_middle_name',
                'sp.last_name as driver_last_name',
                'sp.phone as driver_phone',

                'gps.latitude as gps_latitude',
                'gps.longitude as gps_longitude',
                'gps.speed_kmph as gps_speed',
                'gps.recorded_at as gps_recorded_at',
                'gps.is_demo as gps_is_demo',
                'gps.source as gps_source',
            ])

            ->latest('sta.id')
            ->first();

        if ($assignment) {
            $assignment->driver_name = trim(
                implode(' ', array_filter([
                    $assignment->driver_first_name,
                    $assignment->driver_middle_name,
                    $assignment->driver_last_name,
                ]))
            );

            $assignment->gps_available =
                $assignment->gps_latitude !== null
                && $assignment->gps_longitude !== null;
        }

        return view('transport.student', compact(
            'student',
            'assignment'
        ));
    }
}
