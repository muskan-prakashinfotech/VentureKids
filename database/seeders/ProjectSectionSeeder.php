<?php

namespace Database\Seeders;

use App\Models\ProjectSection;
use Illuminate\Database\Seeder;

/**
 * Seeds the `project_sections` table (the student project-builder's
 * step-by-step sections) with an original VentureKids flow, structured
 * differently from kids_new's 8-section flow (Basic Details, Project Idea &
 * Purpose, Research & Understanding, Creation Process, Skills & Learning,
 * Presentation/Design & Communication, Entrepreneurship, Impact &
 * Reflection) — same spirit of a guided project journey, own section names
 * and grouping.
 */
class ProjectSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'Spark & Purpose',
            'Explore & Research',
            'Build & Create',
            'Skills & Growth',
            'Share & Present',
            'Venture Thinking',
            'Reflect & Next Steps',
        ];

        foreach ($sections as $index => $title) {
            ProjectSection::firstOrCreate(
                ['section_title' => $title],
                ['display_order' => $index + 1, 'status' => 1]
            );
        }
    }
}
