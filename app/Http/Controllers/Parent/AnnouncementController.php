<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class AnnouncementController extends ParentController
{
    private function visibleQuery(array $context)
    {
        $schoolIds = $context['children']->pluck('school_id')->unique()->values()->all() ?: [0];
        $campusIds = $context['children']->pluck('campus_id')->filter()->unique()->values()->all() ?: [0];

        return DB::table('announcements as a')
            ->whereIn('a.school_id', $schoolIds)
            ->where('a.status', 'published')
            ->where(function ($q) {
                $q->whereNull('a.published_at')->orWhere('a.published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('a.expires_at')->orWhere('a.expires_at', '>=', now());
            })
            ->whereIn('a.audience_type', ['all_school', 'parents', 'campus'])
            ->where(function ($q) use ($campusIds) {
                $q->whereNull('a.campus_id')->orWhereIn('a.campus_id', $campusIds);
            });
    }

    public function index()
    {
        $context = $this->parentContext();
        $items = $this->visibleQuery($context)
            ->leftJoin('announcement_reads as ar', function ($join) {
                $join->on('ar.announcement_id', '=', 'a.id')
                    ->where('ar.user_id', '=', auth()->id());
            })
            ->select('a.*', 'ar.read_at')
            ->orderByDesc('a.published_at')
            ->paginate(15);

        return view('parent.announcements.index', compact('items'));
    }

    public function show(int $id)
    {
        $context = $this->parentContext();
        $item = $this->visibleQuery($context)->where('a.id', $id)->first();
        abort_unless($item, 404);

        DB::table('announcement_reads')->updateOrInsert(
            ['announcement_id' => $id, 'user_id' => auth()->id()],
            ['read_at' => now(), 'created_at' => now(), 'updated_at' => now()]
        );

        return view('parent.announcements.show', compact('item'));
    }
}
