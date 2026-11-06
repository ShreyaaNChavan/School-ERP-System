<?php
namespace App\Http\Controllers\Transport;
use App\Http\Controllers\Controller;
use App\Models\{TransportVehicle, TransportRoute, TransportStop, TransportDriverAssignment, StaffProfile};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class TransportController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }
    private function vehicleQuery()
    {
        return TransportVehicle::where('school_id', $this->schoolId());
    }
    private function routeQuery()
    {
        return TransportRoute::where('school_id', $this->schoolId());
    }
    public function dashboard()
    {
        $s = $this->schoolId();
        $vehicles = $this->vehicleQuery()->count();
        $active = $this->vehicleQuery()->where('status', 'active')->count();
        return view('transport.dashboard', ['vehicles' => $vehicles, 'active' => $active, 'routes' => $this->routeQuery()->where('is_active', true)->count(), 'drivers' => TransportDriverAssignment::whereNull('end_date')->whereHas('vehicle', fn($q) => $q->where('school_id', $s))->count(), 'fleet' => $this->vehicleQuery()->with(['driverAssignments' => fn($q) => $q->whereNull('end_date')->with('staff')])->latest()->take(8)->get()]);
    }
    public function vehicles()
    {
        $vehicles = $this->vehicleQuery()->latest()->paginate(12);
        return view('transport.vehicles', compact('vehicles'));
    }
    public function storeVehicle(Request $r)
    {
        $d = $r->validate(['registration_number' => 'required|string|max:255|unique:transport_vehicles,registration_number', 'vehicle_type' => 'nullable|string|max:50', 'make_model' => 'nullable|string|max:255', 'capacity' => 'nullable|integer|min:1|max:300', 'insurance_expiry' => 'nullable|date', 'permit_expiry' => 'nullable|date', 'status' => [Rule::in(['active', 'inactive', 'maintenance'])]]);
        $d['school_id'] = $this->schoolId();
        TransportVehicle::create($d);
        return back()->with('success', 'Vehicle added.');
    }
    public function vehicle(TransportVehicle $vehicle)
    {
        abort_unless((int) $vehicle->school_id === $this->schoolId(), 404);
        $vehicle->load(['driverAssignments' => fn($q) => $q->latest()->with('staff')]);
        $staff = StaffProfile::where('school_id', $this->schoolId())->where('status', 'active')->orderBy('first_name')->get();
        return view('transport.vehicle', compact('vehicle', 'staff'));
    }
    public function updateVehicle(Request $r, TransportVehicle $vehicle)
    {
        abort_unless((int) $vehicle->school_id === $this->schoolId(), 404);
        $d = $r->validate(['vehicle_type' => 'nullable|string|max:50', 'make_model' => 'nullable|string|max:255', 'capacity' => 'nullable|integer|min:1|max:300', 'insurance_expiry' => 'nullable|date', 'permit_expiry' => 'nullable|date', 'status' => [Rule::in(['active', 'inactive', 'maintenance'])]]);
        $vehicle->update($d);
        return back()->with('success', 'Vehicle updated.');
    }
    public function assignDriver(Request $r, TransportVehicle $vehicle)
    {
        abort_unless((int) $vehicle->school_id === $this->schoolId(), 404);
        $d = $r->validate(['staff_profile_id' => 'required|integer', 'start_date' => 'required|date']);
        abort_unless(StaffProfile::where('school_id', $this->schoolId())->whereKey($d['staff_profile_id'])->exists(), 404);
        TransportDriverAssignment::where('transport_vehicle_id', $vehicle->id)->whereNull('end_date')->update(['end_date' => now()->toDateString()]);
        TransportDriverAssignment::create(['transport_vehicle_id' => $vehicle->id] + $d);
        return back()->with('success', 'Driver assigned.');
    }
    public function routes()
    {
        $routes = $this->routeQuery()->withCount('stops')->latest()->paginate(12);
        return view('transport.routes', compact('routes'));
    }
    public function storeRoute(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:255', 'code' => 'nullable|string|max:255', 'distance_km' => 'nullable|numeric|min:0', 'default_fee' => 'nullable|numeric|min:0']);
        $d['school_id'] = $this->schoolId();
        TransportRoute::create($d);
        return back()->with('success', 'Route created.');
    }
    public function routeShow(TransportRoute $route)
    {
        abort_unless((int) $route->school_id === $this->schoolId(), 404);
        $route->load('stops');
        return view('transport.route', compact('route'));
    }
    public function storeStop(Request $r, TransportRoute $route)
    {
        abort_unless((int) $route->school_id === $this->schoolId(), 404);
        $d = $r->validate(['name' => 'required|string|max:255', 'address' => 'nullable|string|max:255', 'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180', 'pickup_time' => 'nullable|date_format:H:i', 'drop_time' => 'nullable|date_format:H:i']);
        $d['sequence'] = (int) $route->stops()->max('sequence') + 1;
        $route->stops()->create($d);
        return back()->with('success', 'Stop added.');
    }
    public function deleteStop(TransportStop $stop)
    {
        abort_unless((int) $stop->route->school_id === $this->schoolId(), 404);
        $stop->delete();
        return back()->with('success', 'Stop removed.');
    }
    public function tracking()
    {
        $vehicles = $this->vehicleQuery()->with(['driverAssignments' => fn($q) => $q->whereNull('end_date')->with('staff')])->get();
        $routes = $this->routeQuery()->with('stops')->get();
        return view('transport.tracking', compact('vehicles', 'routes'));
    }
}
