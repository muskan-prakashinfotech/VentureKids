<?php

namespace Database\Seeders;

use App\Models\SchoolBatch;
use App\Models\Trainer;
use App\Models\TrainerAllocationNew;
use App\Models\User;
use App\Services\SchoolOnboardingService;
use App\Services\StudentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Creates 10 demo schools, each with 10 students and 5 trainers (allocated
 * to that school's default batch). Reuses the exact same services the admin
 * UI uses (SchoolOnboardingService, StudentService) so the resulting data is
 * indistinguishable from records created through the real onboarding forms.
 *
 * Requires StudentGradeSeeder (student_grade) and the restored reference
 * data (countrys, currencies, grades, trainerlavels) to already be present.
 */
class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schoolService = new SchoolOnboardingService();
        $studentService = new StudentService();

        $countryIds = DB::table('countrys')->pluck('id')->all();
        $currencyCodes = DB::table('currencies')->pluck('code')->all();
        $trainerLevelIds = DB::table('trainerlavels')->pluck('id')->all();

        if (empty($countryIds) || empty($currencyCodes)) {
            $this->command?->error('countrys/currencies are empty — restore reference data before running this seeder.');
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            $email = "school{$i}@venturekids.test";

            if (User::where('email', $email)->exists()) {
                continue; // already seeded, keep this seeder safe to re-run
            }

            $countryId = $countryIds[array_rand($countryIds)];
            $currencyCode = $currencyCodes[array_rand($currencyCodes)];

            $result = $schoolService->createSchool([
                'school_name' => "Demo School {$i}",
                'principle_name' => "Principal {$i}",
                'email' => $email,
                'contact_number' => '9000000' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'country' => $countryId,
                'city' => "Demo City {$i}",
                'currency_type' => strtolower($currencyCode),
                'fee_per_student' => 20,
                'number_of_student' => 50,
                'course_start_date' => now()->subMonths(6)->format('Y-m-d'),
                'course_end_date' => now()->addMonths(6)->format('Y-m-d'),
                'created_by' => 1,
                'created_type' => 'admin',
                'school_domain' => 'demoschool' . $i,
                'confirmed' => 1,
            ], 'secret', false);

            $school = $result['school'];
            $batch = SchoolBatch::where('school_id', $school->id)->first();

            // 10 students
            for ($s = 1; $s <= 10; $s++) {
                $studentService->createStudent($school, [
                    'name' => "Student {$i}-{$s}",
                    'email' => "school{$i}.student{$s}@venturekids.test",
                    'parent_name' => "Parent {$i}-{$s}",
                    'parent_email' => "school{$i}.parent{$s}@venturekids.test",
                    'address' => $school->city,
                    'student_grade' => 'Grade' . random_int(1, 10),
                ]);
            }

            // 5 trainers, allocated to this school's default batch
            for ($t = 1; $t <= 5; $t++) {
                $trainerEmail = "school{$i}.trainer{$t}@venturekids.test";

                $trainerUser = new User();
                $trainerUser->name = "Trainer {$i}-{$t}";
                $trainerUser->email = $trainerEmail;
                $trainerUser->password = Hash::make('secret');
                $trainerUser->group = 3;
                $trainerUser->country_id = $countryId;
                $trainerUser->status = 1;
                $trainerUser->avatar = 'img/default-avatar.jpg';
                $trainerUser->email_verified_at = now();
                $trainerUser->save();

                $trainerUser->syncRoles(['trainer']);
                $trainerUser->syncPermissions(['view_backend', 'trainer_edit']);

                $trainerUser->username = config('app.initial_username') + $trainerUser->id;
                $trainerUser->save();

                $trainer = new Trainer();
                $trainer->user_id = $trainerUser->id;
                $trainer->trainer_name = "Trainer {$i}-{$t}";
                $trainer->official_email_id = $trainerEmail;
                $trainer->grade_id = !empty($trainerLevelIds)
                    ? implode(',', array_slice($trainerLevelIds, 0, 3))
                    : null;
                $trainer->trainer_fee = 100;
                $trainer->currency = strtolower($currencyCode);
                $trainer->contact_no = '9111' . str_pad((string) ($i * 10 + $t), 6, '0', STR_PAD_LEFT);
                $trainer->city = $school->city;
                $trainer->join_date = now()->subMonths(3)->format('Y-m-d');
                $trainer->country_id = $countryId;
                $trainer->created_by = 1;
                $trainer->save();

                if ($batch) {
                    $allocation = new TrainerAllocationNew();
                    $allocation->school_id = $school->id;
                    $allocation->trainer_id = $trainer->id;
                    $allocation->school_batch_id = $batch->id;
                    $allocation->save();
                }
            }

            $this->command?->info("Seeded {$school->school_name}: 10 students, 5 trainers.");
        }
    }
}
