<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

/**
 * Seeds the `grades` table (content/program levels) with a VentureKids-original
 * naming set. Deliberately distinct from kids_new's level names (Exploratory
 * Level, Sparkpreneur, Thinkpreneur, Createpreneur, Launchpreneur, Skillpreneur, ...)
 * so this data isn't a copy of the reference database.
 */
class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'grade' => 'Curiosity Spark',
                'description' => 'Foundational level introducing young learners to entrepreneurial curiosity, idea generation, and creative problem spotting.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies successful completion of the Curiosity Spark level, demonstrating foundational curiosity and idea-generation skills.',
                'cert_quote' => 'Every big venture starts with a small spark of curiosity.',
                'is_primary' => 1,
                'unique_code' => 'VK-CS1',
                'level_icon' => 'curiosity-spark.png',
                'display_order_id' => 1,
                'assessment_order' => 1,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Idea Forge',
                'description' => 'Learners shape raw ideas into simple concepts, practicing brainstorming, teamwork, and basic design thinking.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies successful completion of the Idea Forge level, demonstrating the ability to shape and refine original ideas.',
                'cert_quote' => 'Great ideas are forged, not found.',
                'is_primary' => 1,
                'unique_code' => 'VK-IF2',
                'level_icon' => 'idea-forge.png',
                'display_order_id' => 2,
                'assessment_order' => 2,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Venture Lab',
                'description' => 'Learners prototype and test their ideas in a hands-on lab setting, building early product and business-model skills.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies successful completion of the Venture Lab level, demonstrating hands-on prototyping and testing skills.',
                'cert_quote' => 'Build it, test it, learn from it.',
                'is_primary' => 1,
                'unique_code' => 'VK-VL3',
                'level_icon' => 'venture-lab.png',
                'display_order_id' => 3,
                'assessment_order' => 3,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Growth Studio',
                'description' => 'Learners focus on growing their venture: marketing, pricing, storytelling, and customer engagement.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies successful completion of the Growth Studio level, demonstrating growth, marketing, and communication skills.',
                'cert_quote' => 'Growth is what happens when ideas meet action.',
                'is_primary' => 1,
                'unique_code' => 'VK-GS4',
                'level_icon' => 'growth-studio.png',
                'display_order_id' => 4,
                'assessment_order' => 4,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Impact Summit',
                'description' => 'The capstone level, where learners pitch their venture, reflect on real-world impact, and build leadership skills.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies successful completion of the Impact Summit level, demonstrating leadership and real-world impact thinking.',
                'cert_quote' => 'True entrepreneurs measure success by the impact they leave behind.',
                'is_primary' => 1,
                'unique_code' => 'VK-IS5',
                'level_icon' => 'impact-summit.png',
                'display_order_id' => 5,
                'assessment_order' => 5,
                'is_publish' => 1,
            ],
            // --- Add-on levels (is_primary = 0): optional/supplementary
            // levels alongside the core Curiosity Spark -> Impact Summit
            // progression above, e.g. workshop-style topics a student can
            // take in addition to their primary level.
            [
                'grade' => 'Maker Lab Sessions',
                'description' => 'A hands-on tinkering add-on where learners build and test small physical prototypes.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies participation in the Maker Lab Sessions add-on level.',
                'cert_quote' => 'Hands that build, minds that grow.',
                'is_primary' => 0,
                'unique_code' => 'VK-ML6',
                'level_icon' => 'maker-lab-sessions.png',
                'display_order_id' => 6,
                'assessment_order' => 6,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Mindful Founders',
                'description' => 'A social-emotional learning add-on helping young founders manage emotions, empathy, and teamwork.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies participation in the Mindful Founders add-on level.',
                'cert_quote' => 'A calm mind builds a strong venture.',
                'is_primary' => 0,
                'unique_code' => 'VK-MF7',
                'level_icon' => 'mindful-founders.png',
                'display_order_id' => 7,
                'assessment_order' => 7,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Design Sprint Workshop',
                'description' => 'A short design-thinking workshop add-on focused on rapid ideation and user-centered design.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies participation in the Design Sprint Workshop add-on level.',
                'cert_quote' => 'Design fast, learn faster.',
                'is_primary' => 0,
                'unique_code' => 'VK-DS8',
                'level_icon' => 'design-sprint-workshop.png',
                'display_order_id' => 8,
                'assessment_order' => 8,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Culture & Community Explorers',
                'description' => 'An add-on level exploring cultural awareness and community-focused problem solving.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies participation in the Culture & Community Explorers add-on level.',
                'cert_quote' => 'Great ventures understand the communities they serve.',
                'is_primary' => 0,
                'unique_code' => 'VK-CC9',
                'level_icon' => 'culture-community-explorers.png',
                'display_order_id' => 9,
                'assessment_order' => 9,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Money Smarts Workshop',
                'description' => 'A financial literacy add-on covering budgeting, saving, and basic money management for young founders.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies participation in the Money Smarts Workshop add-on level.',
                'cert_quote' => 'Smart money habits start early.',
                'is_primary' => 0,
                'unique_code' => 'VK-MS10',
                'level_icon' => 'money-smarts-workshop.png',
                'display_order_id' => 10,
                'assessment_order' => 10,
                'is_publish' => 1,
            ],
            [
                'grade' => 'Tech Tinkerers',
                'description' => 'A technology add-on introducing basic coding, digital tools, and tech-driven problem solving.',
                'image' => 'default.jpg',
                'cert_description' => 'This certifies participation in the Tech Tinkerers add-on level.',
                'cert_quote' => 'Every technopreneur starts by tinkering.',
                'is_primary' => 0,
                'unique_code' => 'VK-TT11',
                'level_icon' => 'tech-tinkerers.png',
                'display_order_id' => 11,
                'assessment_order' => 11,
                'is_publish' => 1,
            ],
        ];

        foreach ($levels as $level) {
            $grade = Grade::firstOrCreate(['grade' => $level['grade']], $level);

            // GradeObserver::creating() overwrites unique_code with
            // 'L'.substr(sha1(time()), 0, 5) on insert, which collides across
            // rows created within the same second. Re-apply our intended code
            // with a plain save (fires 'updating', not 'creating', so the
            // observer doesn't clobber it again).
            if ($grade->unique_code !== $level['unique_code']) {
                $grade->unique_code = $level['unique_code'];
                $grade->save();
            }

            // Keep level_icon in sync on re-runs too (e.g. after fixing the
            // placeholder that used to point at a non-visible decorative
            // asset instead of a real level icon).
            if ($grade->level_icon !== $level['level_icon']) {
                $grade->level_icon = $level['level_icon'];
                $grade->save();
            }
        }
    }
}
