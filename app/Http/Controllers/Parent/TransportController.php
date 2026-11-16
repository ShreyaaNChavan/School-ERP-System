<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class TransportController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();

        $records = DB::table('student_transport_assignments as sta')
            ->join('student_profiles as sp', 'sp.id', '=', 'sta.student_id')
            ->join('transport_routes as tr', 'tr.id', '=', 'sta.transport_route_id')
            ->leftJoin('transport_stops as ts', 'ts.id', '=', 'sta.transport_stop_id')
            ->leftJoin('transport_vehicles as tv', 'tv.id', '=', 'tr.transport_vehicle_id')
            ->leftJoin('transport_driver_assignments as tda', function ($join) {
                $join->on('tda.transport_vehicle_id', '=', 'tv.id')
                    ->where(function ($query) {
                        $query->whereNull('tda.end_date')
                            ->orWhereDate('tda.end_date', '>=', now()->toDateString());
                    })
                    ->whereDate('tda.start_date', '<=', now()->toDateString());
            })
            ->leftJoin('staff_profiles as driver', 'driver.id', '=', 'tda.staff_profile_id')
            ->whereIn('sta.student_id', $context['childIds'] ?: [0])
            ->where('sta.status', 'active')
            ->select(
                'sp.first_name',
                'sp.last_name',
                'tr.name as route_name',
                'tr.code as route_code',
                'ts.name as stop_name',
                'ts.address as stop_address',
                'ts.pickup_time',
                'ts.drop_time',
                'tv.registration_number',
                'tv.make_model',
                'driver.first_name as driver_first_name',
                'driver.last_name as driver_last_name',
                'driver.phone as driver_phone',
                'sta.trip_type',
                'sta.fee_amount',
                'sta.start_date',
                'sta.end_date'
            )
            ->orderBy('sp.first_name')
            ->get();

        return $this->parentView('transport', 'Children Transport Details', $context, $records);
    }
}