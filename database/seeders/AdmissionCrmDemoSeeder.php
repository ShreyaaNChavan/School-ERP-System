<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdmissionLead;
use App\Models\AdmissionFollowup;
use Illuminate\Support\Facades\DB;

class AdmissionCrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = 1;
        $campusId = 1;
        $assignedUserId = DB::table('users')->where('id', 2)->value('id');

        $students = [
            ['Aarav Deshmukh', 'Rajesh Deshmukh', 'Class 1', 'Website'],
            ['Ananya Kulkarni', 'Priya Kulkarni', 'Class 5', 'Walk-in'],
            ['Vihaan Patil', 'Sanjay Patil', 'Class 3', 'Referral'],
            ['Ira Joshi', 'Neha Joshi', 'Class 7', 'Social Media'],
            ['Aditya Pawar', 'Mahesh Pawar', 'Class 8', 'Advertisement'],
            ['Saanvi Shinde', 'Pooja Shinde', 'Class 2', 'Phone Call'],
            ['Arjun More', 'Vijay More', 'Class 6', 'Website'],
            ['Myra Shah', 'Amit Shah', 'Class 4', 'School Event'],
            ['Reyansh Jadhav', 'Nitin Jadhav', 'Class 9', 'Walk-in'],
            ['Sara Khan', 'Imran Khan', 'Class 1', 'Referral'],
            ['Kabir Chavan', 'Rahul Chavan', 'Class 10', 'Advertisement'],
            ['Aadhya Bhosale', 'Sneha Bhosale', 'Class 3', 'Social Media'],
            ['Ishaan Gaikwad', 'Prakash Gaikwad', 'Class 5', 'Phone Call'],
            ['Kiara Mehta', 'Rohan Mehta', 'Class 8', 'Website'],
            ['Atharv Pawar', 'Sunil Pawar', 'Class 2', 'Walk-in'],
            ['Riya Salunkhe', 'Manoj Salunkhe', 'Class 6', 'Referral'],
            ['Dhruv Desai', 'Kunal Desai', 'Class 9', 'Advertisement'],
            ['Pari Wagh', 'Aarti Wagh', 'Class 4', 'School Event'],
            ['Omkar Mane', 'Suresh Mane', 'Class 7', 'Phone Call'],
            ['Tara Sane', 'Meenal Sane', 'Class 1', 'Website'],
            ['Rudra Nair', 'Vivek Nair', 'Class 10', 'Walk-in'],
            ['Avni Kulkarni', 'Rashmi Kulkarni', 'Class 5', 'Social Media'],
            ['Yash Thakur', 'Deepak Thakur', 'Class 3', 'Referral'],
            ['Mira Gokhale', 'Swati Gokhale', 'Class 8', 'Advertisement'],
        ];

        $statuses = [
            'new',
            'new',
            'new',
            'contacted',
            'contacted',
            'contacted',
            'interested',
            'interested',
            'interested',
            'follow_up',
            'follow_up',
            'follow_up',
            'application_started',
            'application_started',
            'application_started',
            'documents_pending',
            'documents_pending',
            'documents_pending',
            'converted',
            'converted',
            'converted',
            'lost',
            'lost',
            'lost',
        ];

        DB::transaction(function () use (
            $students,
            $statuses,
            $schoolId,
            $campusId,
            $assignedUserId
        ) {
            foreach ($students as $index => $student) {
                $number = $index + 1;

                $email = 'crm.sample' . str_pad($number, 2, '0', STR_PAD_LEFT)
                    . '@example.test';

                $lead = AdmissionLead::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'email' => $email,
                    ],
                    [
                        'campus_id' => $campusId,
                        'student_name' => $student[0],
                        'parent_name' => $student[1],
                        'phone' => '900000' . str_pad((string)(1000 + $number), 4, '0', STR_PAD_LEFT),
                        'prospective_grade' => $student[2],
                        'source' => $student[3],
                        'status' => $statuses[$index],
                        'assigned_to' => ($number % 4 === 0) ? null : $assignedUserId,
                        'notes' => 'Sample CRM enquiry for admission pipeline demonstration.',
                    ]
                );

                // Avoid duplicating follow-ups when the seeder runs again.
                if ($lead->followups()->exists()) {
                    continue;
                }

                $isCompleted = in_array($lead->status, [
                    'contacted',
                    'interested',
                    'converted',
                    'lost',
                ], true);

                $isDue = in_array($number, [1, 2, 4, 10, 11, 16], true);

                if ($isCompleted) {
                    $scheduledAt = now()->subDays(($number % 5) + 1);
                    $completedAt = $scheduledAt->copy()->addHours(2);
                    $followupStatus = 'completed';
                } elseif ($isDue) {
                    $scheduledAt = now()->subDays(1);
                    $completedAt = null;
                    $followupStatus = 'pending';
                } else {
                    $scheduledAt = now()->addDays(($number % 5) + 1);
                    $completedAt = null;
                    $followupStatus = 'pending';
                }

                AdmissionFollowup::create([
                    'admission_lead_id' => $lead->id,
                    'assigned_to' => $assignedUserId,
                    'followup_type' => match ($number % 4) {
                        0 => 'meeting',
                        1 => 'call',
                        2 => 'email',
                        default => 'message',
                    },
                    'notes' => $isCompleted
                        ? 'Sample follow-up completed. Parent discussion recorded.'
                        : 'Sample follow-up: contact parent and discuss admission requirements.',
                    'scheduled_at' => $scheduledAt,
                    'completed_at' => $completedAt,
                    'status' => $followupStatus,
                ]);
            }
        });

        $this->command->info('Admission CRM sample records created or updated successfully.');
    }
}
