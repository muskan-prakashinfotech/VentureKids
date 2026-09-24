<?php

namespace Database\Seeders;

use App\Models\StudentGrade;
use Illuminate\Database\Seeder;

/**
 * Seeds the `student_grade` table — a student's academic class level
 * (Grade1..Grade10). This is deliberately separate from:
 *  - `grades` (content/program levels, e.g. Thinkpreneur, Createpreneur)
 *  - `trainerlavels` (the levels a trainer is qualified to teach)
 * All three are distinct catalogs used by different parts of the app.
 */
class StudentGradeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            StudentGrade::firstOrCreate(['name' => 'Grade' . $i]);
        }
    }
}
