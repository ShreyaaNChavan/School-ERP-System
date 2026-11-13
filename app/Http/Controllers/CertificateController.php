<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class CertificateController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    private function certificateTypes(): array
    {
        return [
            'Bonafide Certificate',
            'Character Certificate',
            'Leaving Certificate',
            'Transfer Certificate',
            'Study Certificate',
            'Date of Birth Certificate',
            'Conduct Certificate',
            'Achievement Certificate',
            'Other',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Certificate Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        $school = $this->schoolId();

        $total = DB::table('certificates')
            ->where('school_id', $school)
            ->count();

        $thisMonth = DB::table('certificates')
            ->where('school_id', $school)
            ->whereMonth('issued_date', now()->month)
            ->whereYear('issued_date', now()->year)
            ->count();

        $today = DB::table('certificates')
            ->where('school_id', $school)
            ->whereDate('issued_date', today())
            ->count();

        $recent = DB::table('certificates as c')
            ->leftJoin('student_profiles as s', 's.id', '=', 'c.student_id')
            ->where('c.school_id', $school)
            ->select(
                'c.*',
                's.first_name',
                's.middle_name',
                's.last_name',
                's.admission_number'
            )
            ->latest('c.issued_date')
            ->take(8)
            ->get();

        return view('certificates.dashboard', compact(
            'total',
            'thisMonth',
            'today',
            'recent'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Certificate History
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $certificates = DB::table('certificates as c')
            ->leftJoin('student_profiles as s', 's.id', '=', 'c.student_id')
            ->where('c.school_id', $this->schoolId())

            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('c.certificate_number', 'like', "%{$search}%")
                        ->orWhere('s.first_name', 'like', "%{$search}%")
                        ->orWhere('s.middle_name', 'like', "%{$search}%")
                        ->orWhere('s.last_name', 'like', "%{$search}%")
                        ->orWhere('s.admission_number', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('c.certificate_type', $request->type);
            })

            ->select(
                'c.*',
                's.first_name',
                's.middle_name',
                's.last_name',
                's.admission_number'
            )
            ->latest('c.issued_date')
            ->paginate(15)
            ->withQueryString();

        $types = $this->certificateTypes();

        return view('certificates.index', compact(
            'certificates',
            'types'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Certificate Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $students = DB::table('student_profiles')
            ->where('school_id', $this->schoolId())
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get();

        $types = $this->certificateTypes();

        return view('certificates.create', compact(
            'students',
            'types'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Store and Generate Certificate PDF
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|integer',
            'certificate_type' => 'required|string|max:100',
            'issued_date' => 'required|date|before_or_equal:today',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $schoolId = $this->schoolId();

        $student = DB::table('student_profiles')
            ->where('school_id', $schoolId)
            ->whereNull('deleted_at')
            ->where('id', $data['student_id'])
            ->first();

        abort_unless($student, 404, 'Student not found.');

        abort_unless(
            in_array($data['certificate_type'], $this->certificateTypes(), true),
            422,
            'Invalid certificate type.'
        );

        $number = 'CASPA-' . now()->format('Y') . '-' .
            strtoupper(Str::random(8));

        while (
            DB::table('certificates')
                ->where('certificate_number', $number)
                ->exists()
        ) {
            $number = 'CASPA-' . now()->format('Y') . '-' .
                strtoupper(Str::random(8));
        }

        $path = 'certificates/' . $number . '.pdf';

        try {
            DB::transaction(function () use (
                $data,
                $student,
                $schoolId,
                $number,
                $path
            ) {
                $certificateId = DB::table('certificates')->insertGetId([
                    'school_id' => $schoolId,
                    'student_id' => $student->id,
                    'certificate_type' => $data['certificate_type'],
                    'certificate_number' => $number,
                    'issued_date' => $data['issued_date'],
                    'issued_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $certificate = DB::table('certificates')
                    ->where('id', $certificateId)
                    ->first();

                $school = DB::table('schools')
                    ->where('id', $schoolId)
                    ->first();

                $pdf = Pdf::loadView('certificates.pdf', [
                    'certificate' => $certificate,
                    'student' => $student,
                    'school' => $school,
                    'remarks' => $data['remarks'] ?? null,
                ])->setPaper('a4', 'landscape');

                $saved = Storage::disk('public')->put(
                    $path,
                    $pdf->output()
                );

                if (!$saved) {
                    throw new \RuntimeException(
                        'Unable to save certificate PDF.'
                    );
                }

                DB::table('certificates')
                    ->where('id', $certificateId)
                    ->update([
                        'file_path' => $path,
                        'updated_at' => now(),
                    ]);
            });
        } catch (Throwable $e) {
            report($e);

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'generation' => 'Certificate generation failed. Please check the PDF configuration and try again.'
                ]);
        }

        $certificateId = DB::table('certificates')
            ->where('certificate_number', $number)
            ->where('school_id', $schoolId)
            ->value('id');

        return redirect()
            ->route('certificates.show', $certificateId)
            ->with('success', 'Certificate generated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Certificate Details
    |--------------------------------------------------------------------------
    */

    public function show(int $certificate)
    {
        $record = $this->findCertificate($certificate);

        $student = $record->student_id
            ? DB::table('student_profiles')
                ->where('id', $record->student_id)
                ->where('school_id', $this->schoolId())
                ->whereNull('deleted_at')
                ->first()
            : null;

        return view('certificates.show', compact(
            'record',
            'student'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Download Certificate PDF
    |--------------------------------------------------------------------------
    */

    public function download(int $certificate)
    {
        $record = $this->findCertificate($certificate);

        abort_unless(
            $record->file_path &&
            Storage::disk('public')->exists($record->file_path),
            404,
            'Certificate PDF not found.'
        );

        return Storage::disk('public')->download(
            $record->file_path,
            $record->certificate_number . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Public Certificate Verification Form
    |--------------------------------------------------------------------------
    */

    public function verifyForm(Request $request)
    {
        $certificateNumber = trim(
            $request->query('certificate_number', '')
        );

        // Show the empty verification page on initial visit.
        if ($certificateNumber === '') {
            return view('certificates.verify');
        }

        // Verify the submitted GET query parameter.
        return $this->checkCertificate($certificateNumber);
    }

    /*
    |--------------------------------------------------------------------------
    | Public Verification Using Certificate Number in URL
    |--------------------------------------------------------------------------
    */

    public function verify(string $certificateNumber)
    {
        return $this->checkCertificate($certificateNumber);
    }

    /*
    |--------------------------------------------------------------------------
    | Certificate Verification Logic
    |--------------------------------------------------------------------------
    */

    private function checkCertificate(string $certificateNumber)
    {
        $certificateNumber = strtoupper(trim($certificateNumber));

        $record = DB::table('certificates')
            ->whereRaw(
                'UPPER(certificate_number) = ?',
                [$certificateNumber]
            )
            ->first();

        // No matching certificate found.
        if (!$record) {
            return view('certificates.verify', [
                'searched' => true,
                'valid' => false,
                'certificateNumber' => $certificateNumber,
            ]);
        }

        // Fetch only the associated active student.
        $student = $record->student_id
            ? DB::table('student_profiles')
                ->where('id', $record->student_id)
                ->where('school_id', $record->school_id)
                ->whereNull('deleted_at')
                ->first()
            : null;

        return view('certificates.verify', [
            'searched' => true,
            'valid' => true,
            'record' => $record,
            'student' => $student,
            'certificateNumber' => $certificateNumber,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Find Certificate Within Current School
    |--------------------------------------------------------------------------
    */

    private function findCertificate(int $id): object
    {
        $record = DB::table('certificates')
            ->where('school_id', $this->schoolId())
            ->where('id', $id)
            ->first();

        abort_unless($record, 404, 'Certificate not found.');

        return $record;
    }
}
