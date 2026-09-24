<?php

namespace Database\Seeders;

use App\Models\StudentBoard;
use Illuminate\Database\Seeder;

/**
 * Seeds the `student_board` table (education boards) — a prerequisite for
 * RealQ Assessment topics/questions/school assignments, which all reference
 * board_id. Real, standard education board names (not kids_new-specific
 * content).
 */
class StudentBoardSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['CBSE', 'ICSE', 'State Board', 'IB (International Baccalaureate)'] as $name) {
            StudentBoard::firstOrCreate(['name' => $name], ['is_other' => 0]);
        }
    }
}
