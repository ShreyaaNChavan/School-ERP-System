<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SchoolNexaDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $schoolId = 1;
            $campusId = 1;
            $academicYearId = 1;
            $now = now();

            // Verify the existing master records.
            if (
                !DB::table('schools')->where('id', $schoolId)->exists() ||
                !DB::table('campuses')->where('id', $campusId)->exists() ||
                !DB::table('academic_years')
                    ->where('id', $academicYearId)
                    ->exists()
            ) {
                throw new RuntimeException(
                    'Required school, campus or academic year was not found.'
                );
            }

            $sections = DB::table('sections')
                ->join(
                    'school_classes',
                    'sections.school_class_id',
                    '=',
                    'school_classes.id'
                )
                ->whereIn('school_classes.grade_level', [8, 9, 10])
                ->where('sections.campus_id', $campusId)
                ->where('sections.is_active', 1)
                ->select(
                    'sections.id as section_id',
                    'sections.name as section_name',
                    'sections.school_class_id',
                    'school_classes.grade_level'
                )
                ->orderBy('school_classes.grade_level')
                ->orderBy('sections.name')
                ->get();

            if ($sections->count() !== 6) {
                throw new RuntimeException(
                    'Expected six active sections for Classes 8, 9 and 10.'
                );
            }

            // Helper: insert a demo record only if its identifying fields
            // do not already exist. This makes the seeder rerunnable.
            $findOrCreate = function ($table, $conditions, $values) use ($now) {
                $record = DB::table($table)
                    ->where($conditions)
                    ->first();

                if ($record) {
                    return $record->id;
                }

                return DB::table($table)->insertGetId(
                    array_merge($conditions, $values, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                );
            };

            // -------------------------------------------------
            // 1. EMPLOYEES: 10 TEACHERS + 5 NON-TEACHING STAFF
            // -------------------------------------------------

            $employees = [
                ['Aarav', 'Sharma', 'Mathematics Teacher', 'Mathematics', 'teacher'],
                ['Priya', 'Patil', 'Science Teacher', 'Science', 'teacher'],
                ['Rahul', 'Deshmukh', 'English Teacher', 'Languages', 'teacher'],
                ['Sneha', 'Kulkarni', 'Social Science Teacher', 'Social Science', 'teacher'],
                ['Rohan', 'Jadhav', 'Marathi Teacher', 'Languages', 'teacher'],
                ['Neha', 'Joshi', 'Hindi Teacher', 'Languages', 'teacher'],
                ['Amit', 'Shinde', 'Computer Teacher', 'Computer Science', 'teacher'],
                ['Pooja', 'More', 'Mathematics Teacher', 'Mathematics', 'teacher'],
                ['Kunal', 'Pawar', 'Science Teacher', 'Science', 'teacher'],
                ['Riya', 'Kadam', 'English Teacher', 'Languages', 'teacher'],
                ['Vikram', 'Chavan', 'Accountant', 'Accounts', 'non-teaching'],
                ['Meena', 'Gaikwad', 'Office Administrator', 'Administration', 'non-teaching'],
                ['Sagar', 'Bhosale', 'Librarian', 'Library', 'non-teaching'],
                ['Kavita', 'Mane', 'Receptionist', 'Administration', 'non-teaching'],
                ['Nilesh', 'Wagh', 'Lab Assistant', 'Laboratory', 'non-teaching'],
            ];

            $teacherIds = [];

            foreach ($employees as $index => $employee) {
                [$first, $last, $designation, $department, $type] = $employee;

                $employeeNumber = sprintf('DEMO-EMP-%03d', $index + 1);

                $id = $findOrCreate(
                    'staff_profiles',
                    [
                        'school_id' => $schoolId,
                        'employee_number' => $employeeNumber,
                    ],
                    [
                        'campus_id' => $campusId,
                        'user_id' => null,
                        'first_name' => $first,
                        'middle_name' => null,
                        'last_name' => $last,
                        'designation' => $designation,
                        'department' => $department,
                        'employment_type' => 'full-time',
                        'joining_date' => now()
                            ->subMonths(6 + $index)
                            ->toDateString(),
                        'phone' => '900000' . str_pad(
                            (string) ($index + 1),
                            4,
                            '0',
                            STR_PAD_LEFT
                        ),
                        'status' => 'active',
                    ]
                );

                if ($type === 'teacher') {
                    $teacherIds[] = $id;
                }
            }

            // -------------------------------------------------
            // 2. SUBJECTS
            // -------------------------------------------------

            $subjectDefinitions = [
                ['English', 'ENG', 5],
                ['Mathematics', 'MATH', 6],
                ['Science', 'SCI', 5],
                ['Social Science', 'SST', 4],
                ['Marathi', 'MAR', 4],
                ['Hindi', 'HIN', 3],
                ['Computer Science', 'COMP', 2],
            ];

            $subjectIds = [];

            foreach ($subjectDefinitions as $subject) {
                [$name, $code, $weeklyPeriods] = $subject;

                $subjectId = $findOrCreate(
                    'subjects',
                    [
                        'school_id' => $schoolId,
                        'name' => $name,
                    ],
                    [
                        'code' => $code,
                        'subject_type' => 'theory',
                        'description' => 'Demo academic subject',
                        'is_active' => 1,
                    ]
                );

                $subjectIds[] = [
                    'id' => $subjectId,
                    'weekly_periods' => $weeklyPeriods,
                ];
            }

            // -------------------------------------------------
            // 3. CLASS-SUBJECT MAPPINGS
            // 3. CLASS-SUBJECT MAPPINGS
            $classSubjectIds = [];

            foreach ($sections->unique('school_class_id') as $section) {
                $classId = $section->school_class_id;

                foreach ($subjectIds as $subject) {
                    $classSubjectId = $findOrCreate(
                        'class_subjects',
                        [
                            'school_class_id' => $classId,
                            'subject_id' => $subject['id'],
                        ],
                        [
                            'weekly_periods' => $subject['weekly_periods'],
                            'is_optional' => 0,
                        ]
                    );

                    $classSubjectIds[$classId][] = [
                        'id' => $classSubjectId,
                    ];
                }
            }

            // 4. TEACHING ASSIGNMENTS
            foreach ($sections as $section) {
                foreach ($classSubjectIds[$section->school_class_id] as $index => $classSubject) {
                    $teacherId = $teacherIds[$index % count($teacherIds)];

                    $findOrCreate(
                        'teaching_assignments',
                        [
                            'staff_profile_id' => $teacherId,
                            'class_subject_id' => $classSubject['id'],
                            'section_id' => $section->section_id,
                            'academic_year_id' => $academicYearId,
                        ],
                        []
                    );
                }
            }

            // -------------------------------------------------
            // 5. STUDENTS AND THEIR GUARDIANS
            // Six students per section = 36 students.
            // -------------------------------------------------

            $students = [
                ['Aarav', 'Vikram', 'Sharma'],
                ['Ananya', 'Rajesh', 'Patil'],
                ['Aditya', 'Suresh', 'Jadhav'],
                ['Aditi', 'Mahesh', 'Kulkarni'],
                ['Ishaan', 'Prakash', 'Deshmukh'],
                ['Saanvi', 'Nitin', 'More'],
                ['Vihaan', 'Sanjay', 'Shinde'],
                ['Diya', 'Ajay', 'Joshi'],
                ['Arjun', 'Ramesh', 'Pawar'],
                ['Myra', 'Sunil', 'Chavan'],
                ['Reyansh', 'Anil', 'Gaikwad'],
                ['Anika', 'Vijay', 'Mane'],
                ['Kabir', 'Dinesh', 'Bhosale'],
                ['Ira', 'Pravin', 'Wagh'],
                ['Atharv', 'Milind', 'Kadam'],
                ['Sara', 'Sandeep', 'Gite'],
                ['Om', 'Vinay', 'Raut'],
                ['Riya', 'Santosh', 'Salunkhe'],
                ['Yash', 'Deepak', 'Kale'],
                ['Avni', 'Manoj', 'Pawar'],
                ['Krish', 'Ganesh', 'Shah'],
                ['Pari', 'Amol', 'Desai'],
                ['Dhruv', 'Rahul', 'Mhatre'],
                ['Ishita', 'Kiran', 'Thakur'],
                ['Vedant', 'Nilesh', 'Patil'],
                ['Mira', 'Sachin', 'Shinde'],
                ['Rudra', 'Suresh', 'More'],
                ['Tanvi', 'Rajendra', 'Joshi'],
                ['Samar', 'Prakash', 'Jadhav'],
                ['Aarya', 'Vikas', 'Kulkarni'],
                ['Neil', 'Akash', 'Chavan'],
                ['Kiara', 'Madhav', 'Gaikwad'],
                ['Parth', 'Nitin', 'Mane'],
                ['Navya', 'Ashok', 'Bhosale'],
                ['Dev', 'Mahendra', 'Kadam'],
                ['Samaira', 'Vijay', 'Raut'],
            ];

            $studentIndex = 0;

            foreach ($sections as $section) {
                for ($seat = 1; $seat <= 6; $seat++) {
                    $student = $students[$studentIndex];
                    [$first, $middle, $last] = $student;

                    $admissionNumber = sprintf(
                        'DEMO-STU-%03d',
                        $studentIndex + 1
                    );

                    $studentId = $findOrCreate(
                        'student_profiles',
                        [
                            'school_id' => $schoolId,
                            'admission_number' => $admissionNumber,
                        ],
                        [
                            'campus_id' => $campusId,
                            'user_id' => null,
                            'first_name' => $first,
                            'middle_name' => $middle,
                            'last_name' => $last,
                            'date_of_birth' => now()
                                ->subYears(12 + ($section->grade_level - 8))
                                ->subDays($studentIndex * 7)
                                ->toDateString(),
                            'gender' => $studentIndex % 2 === 0 ? 'male' : 'female',
                            'blood_group' => null,
                            'photo_path' => null,
                            'admission_date' => '2026-06-01',
                            'status' => 'active',
                            'metadata' => null,
                        ]
                    );

                    // Student enrollment.
                    $findOrCreate(
                        'student_enrollments',
                        [
                            'student_id' => $studentId,
                            'academic_year_id' => $academicYearId,
                        ],
                        [
                            'school_class_id' => $section->school_class_id,
                            'section_id' => $section->section_id,
                            'enrollment_date' => '2026-06-01',
                            'withdrawal_date' => null,
                            'status' => 'active',
                        ]
                    );

                    // One guardian per demo student.
                    $guardianName = 'Parent of ' . $first . ' ' . $last;
                    $guardianPhone = '91' . str_pad(
                        (string) (8000000000 + $studentIndex),
                        10,
                        '0',
                        STR_PAD_LEFT
                    );

                    $guardianId = $findOrCreate(
                        'guardians',
                        [
                            'full_name' => $guardianName,
                            'phone' => $guardianPhone,
                        ],
                        [
                            'user_id' => null,
                            'relationship' => 'Parent',
                            'alternate_phone' => null,
                            'email' => 'demo.parent.' . ($studentIndex + 1) . '@example.com',
                            'address' => 'Lohegaon, Pune, Maharashtra',
                            'occupation' => 'Service',
                        ]
                    );

                    // Link guardian to student.
                    DB::table('student_guardians')->updateOrInsert(
                        [
                            'student_id' => $studentId,
                            'guardian_id' => $guardianId,
                        ],
                        [
                            'is_primary' => 1,
                            'is_emergency_contact' => 1,
                            'can_pick_up_student' => 1,
                            'receives_communications' => 1,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );

                    $studentIndex++;
                }
            }

            $this->command->info('SchoolNexa demo data created successfully!');
            $this->command->info('15 employees (including 10 teachers).');
            $this->command->info('36 students and 36 guardians.');
            $this->command->info('7 subjects and class-subject mappings.');
            $this->command->info('Teaching assignments created for all six sections.');
        });
    }
}
