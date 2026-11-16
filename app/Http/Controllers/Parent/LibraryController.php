<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Support\Facades\DB;

class LibraryController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();

        $records = DB::table('library_loans as ll')
            ->join('student_profiles as sp', 'sp.id', '=', 'll.student_id')
            ->join('library_copies as lc', 'lc.id', '=', 'll.library_copy_id')
            ->join('library_books as lb', 'lb.id', '=', 'lc.library_book_id')
            ->whereIn('ll.student_id', $context['childIds'] ?: [0])
            ->select(
                'sp.first_name',
                'sp.last_name',
                'lb.title',
                'lb.author',
                'lb.isbn',
                'lb.category',
                'lc.accession_number',
                'lc.shelf_location',
                'll.issued_at',
                'll.due_at',
                'll.returned_at',
                'll.fine_amount',
                'll.status'
            )
            ->orderByDesc('ll.issued_at')
            ->get();

        return $this->parentView('library', 'Children Library Records', $context, $records);
    }
}