<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends ParentController
{
    public function index(Request $request)
    {
        $context = $this->parentContext();
        $schoolIds = $context['children']->pluck('school_id')->unique()->values()->all() ?: [0];
        $campusIds = $context['children']->pluck('campus_id')->filter()->unique()->values()->all() ?: [0];

        $events = DB::table('events as e')
            ->leftJoin('campuses as c', 'c.id', '=', 'e.campus_id')
            ->whereIn('e.school_id', $schoolIds)
            ->where(function ($q) use ($campusIds) {
                $q->whereNull('e.campus_id')->orWhereIn('e.campus_id', $campusIds);
            })
            ->where('e.status', '!=', 'cancelled')
            ->when($request->filled('type'), fn ($q) => $q->where('e.event_type', $request->type))
            ->select('e.*', 'c.name as campus_name')
            ->orderBy('e.starts_at')
            ->paginate(15)->withQueryString();

        $types = DB::table('events')->whereIn('school_id', $schoolIds)
            ->whereNotNull('event_type')->distinct()->orderBy('event_type')->pluck('event_type');

        return view('parent.events.index', compact('events', 'types'));
    }
}
