<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Adds 5 more students to every school created by SchoolSeeder (which already
 * seeded 10 students each), using the same StudentService the real "Add
 * Student" admin form uses. Kept as its own seeder rather than folded into
 * SchoolSeeder so it can be re-run independently.
 */
class AdditionalStudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentService = new StudentService();
        $schools = School::where('official_email_id', 'like', 'school%@venturekids.test')->get();

        foreach ($schools as $school) {
            for ($s = 11; $s <= 15; $s++) {
                $email = str_replace('@venturekids.test', '', $school->official_email_id) . ".student{$s}@venturekids.test";

                if (User::where('email', $email)->exists()) {
                    continue; // already seeded, keep this seeder safe to re-run
                }

                $studentService->createStudent($school, [
                    'name' => "Student {$school->id}-{$s}",
                    'email' => $email,
                    'parent_name' => "Parent {$school->id}-{$s}",
                    'parent_email' => str_replace('@venturekids.test', '', $school->official_email_id) . ".parent{$s}@venturekids.test",
                    'address' => $school->city,
                    'student_grade' => 'Grade' . random_int(1, 10),
                ]);
            }

            $this->command?->info("Added 5 students to {$school->school_name}.");
        }

        // StudentService::createStudent() always assigns a random, unrecoverable
        // password with no override hook. Reset every seeded student (this
        // batch and the earlier 10-per-school batch) to a known password so
        // the accounts are actually usable for testing/demo logins.
        DB::table('users')
            ->where('group', 4)
            ->where('email', 'like', '%@venturekids.test')
            ->update(['password' => Hash::make('secret')]);
    }
}
