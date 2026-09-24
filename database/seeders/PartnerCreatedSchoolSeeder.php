<?php

namespace Database\Seeders;

use App\Models\PendingSchool;
use App\Models\School;
use App\Models\SchoolBatch;
use App\Models\User;
use App\Services\SchoolOnboardingService;
use App\Services\StudentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds one school genuinely attributed to a partner — not the admin-created
 * demo schools from SchoolSeeder. Mirrors the real flow exactly:
 *  1. A partner submits a school request -> `PendingSchool` row
 *     (Backend\SchoolController::storePendingSchool()).
 *  2. An admin approves it -> SchoolOnboardingService::createSchool() is
 *     called with created_by = the partner's user id and
 *     created_type = 'partner' (Backend\PendingSchoolController::approve()).
 *  3. 3 students are added to the school's default batch
 *     (StudentService::createStudent(), same service SchoolSeeder uses).
 *
 * School and student names are real-sounding (not "Demo School"/"Student
 * X-Y" placeholders), since this represents a specific, named partner
 * partnership rather than generic bulk demo data. Requires PartnerSeeder,
 * StudentGradeSeeder and GradeSeeder to have already run.
 */
class PartnerCreatedSchoolSeeder extends Seeder
{
    public function run(): void
    {
        $partner = User::where('email', 'partner1@venturekids.test')->first();
        $countryId = DB::table('countrys')->orderBy('id')->value('id');
        $currencyCode = DB::table('currencies')->orderBy('id')->value('code');

        if (!$partner || !$countryId || !$currencyCode) {
            $this->command?->error('Partner/country/currency not found — run PartnerSeeder first.');
            return;
        }

        $schoolEmail = 'greenwood.international@venturekids.test';

        if (User::where('email', $schoolEmail)->exists()) {
            $this->command?->info('Partner-created school already seeded.');
            return;
        }

        $formData = [
            'school_name' => 'Greenwood International School',
            'principle_name' => 'Meera Iyer',
            'email' => $schoolEmail,
            'contact_number' => '9000001234',
            'country' => $countryId,
            'city' => 'Bengaluru',
            'currency_type' => strtolower($currencyCode),
            'fee_per_student' => 20,
            'number_of_student' => 50,
            'course_start_date' => now()->subMonths(2)->format('Y-m-d'),
            'course_end_date' => now()->addMonths(10)->format('Y-m-d'),
            'school_domain' => 'greenwoodintl',
        ];

        // Step 1: the partner's original submission — kept for an accurate
        // audit trail, not just the resulting school.
        $pendingSchool = PendingSchool::create([
            'submitted_by' => $partner->id,
            'form_data' => $formData,
            'status' => 'approved',
            'reviewed_by' => 1, // super admin
            'reviewed_at' => now()->subDays(3),
        ]);

        // Step 2: admin approval creates the real school, exactly as
        // PendingSchoolController::approve() does.
        $schoolService = new SchoolOnboardingService();
        $result = $schoolService->createSchool(array_merge($formData, [
            'created_by' => $partner->id,
            'created_type' => 'partner',
            'confirmed' => 1,
        ]), 'secret', false);

        $school = $result['school'];
        $batch = SchoolBatch::where('school_id', $school->id)->first();

        // Step 3: a small, realistically-named student cohort.
        $studentService = new StudentService();
        $students = [
            ['name' => 'Ishaan Verma', 'parent_name' => 'Rakesh Verma'],
            ['name' => 'Diya Kapoor', 'parent_name' => 'Sunita Kapoor'],
            ['name' => 'Arjun Nair', 'parent_name' => 'Vivek Nair'],
        ];

        foreach ($students as $index => $student) {
            $slug = strtolower(str_replace(' ', '.', $student['name']));

            $studentService->createStudent($school, [
                'name' => $student['name'],
                'email' => "{$slug}@venturekids.test",
                'parent_name' => $student['parent_name'],
                'parent_email' => str_replace('.', '.parent.', $slug) . '@venturekids.test',
                'address' => $school->city,
                'student_grade' => 'Grade' . ($index + 1),
            ]);
        }

        // Reset the passwords StudentService randomizes with no override
        // hook (see StudentProjectSeeder/AdditionalStudentSeeder for the
        // same fix) so these accounts are actually usable for testing.
        DB::table('users')
            ->where('group', 4)
            ->whereIn('email', collect($students)->map(fn ($s) => strtolower(str_replace(' ', '.', $s['name'])) . '@venturekids.test'))
            ->update(['password' => \Illuminate\Support\Facades\Hash::make('secret')]);

        $this->command?->info("Seeded partner-created school '{$school->school_name}' (submitted by {$partner->name}) with 3 students.");
    }
}
