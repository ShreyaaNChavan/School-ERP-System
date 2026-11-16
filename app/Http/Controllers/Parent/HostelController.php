<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class HostelController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();

        $allocations = DB::table('hostel_allocations as ha')
            ->join('student_profiles as sp', 'sp.id', '=', 'ha.student_id')
            ->join('hostel_beds as hb', 'hb.id', '=', 'ha.hostel_bed_id')
            ->join('hostel_rooms as hr', 'hr.id', '=', 'hb.hostel_room_id')
            ->join('hostels as h', 'h.id', '=', 'hr.hostel_id')
            ->whereIn('ha.student_id', $context['childIds'] ?: [0])
            ->select(
                'sp.first_name',
                'sp.last_name',
                'h.name as hostel_name',
                'h.hostel_type',
                'h.warden_name',
                'h.warden_phone',
                'hr.room_number',
                'hr.room_type',
                'hb.bed_number',
                'ha.start_date',
                'ha.end_date',
                'ha.status'
            )
            ->orderByDesc('ha.start_date')
            ->get();

        $attendance = DB::table('hostel_attendance as hat')
            ->join('student_profiles as sp', 'sp.id', '=', 'hat.student_id')
            ->whereIn('hat.student_id', $context['childIds'] ?: [0])
            ->select(
                'sp.first_name',
                'sp.last_name',
                'hat.attendance_date',
                'hat.check_in_time',
                'hat.check_out_time',
                'hat.status'
            )
            ->orderByDesc('hat.attendance_date')
            ->limit(100)
            ->get();

        return view('parent.services.index', array_merge($context, [
            'module' => 'hostel',
            'title' => 'Children Hostel Details',
            'records' => $allocations,
            'secondaryRecords' => $attendance,
            'secondaryTitle' => 'Hostel Attendance History',
        ]));
    }
}