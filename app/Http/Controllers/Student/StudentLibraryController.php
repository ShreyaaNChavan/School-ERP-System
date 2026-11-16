<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StudentLibraryController extends Controller
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

        $loans = DB::table('library_loans as ll')
            ->join('library_copies as lc', 'lc.id', '=', 'll.library_copy_id')
            ->join('library_books as lb', 'lb.id', '=', 'lc.library_book_id')
            ->where('ll.student_id', $student->id)
            ->where('lb.school_id', $student->school_id)
            ->select([
                'll.id',
                'll.status',
                'll.fine_amount',
                'll.issued_at',
                'll.due_at',
                'll.returned_at',
                'lb.title as book_title',
                'lb.category',
                'lb.author',
                'lc.accession_number',
            ])
            ->orderByDesc('ll.issued_at')
            ->get();

        return view('library.student', compact('student', 'loans'));
    }
}
