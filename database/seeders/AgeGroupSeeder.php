<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Stream;
use Illuminate\Database\Seeder;

class AgeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Grade::truncate();
        Stream::truncate();
        $array = collect([
            collect([
                'grade'         => 'Level 1',
                'description'   => 'Beginner',
                'child'         => [
                    [
                        'title'         => 'Introduction Entrepreneurship',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Entrepreneurial Mindset',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Problem Identification & Opportunity Analysis',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Creative Thinking, Imagination & Critical Thinking',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Effective Communication',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Dream Big & Perseverance',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Building Self Esteem & Confidence',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Time Management',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Integrity',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Collaboration',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Curiosity & Adaptability',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ],
                ],
            ]), collect([
                'grade'         => 'Level 2',
                'description'   => 'Intermediate',
                'child'         => [
                    [
                        'title'         => 'Find Your Passion',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Design Thinking',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Pricing & Negotiation',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Art of Storytelling',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Sales & Marketing',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Digital Literacy',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Social Media with Purpose',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Leadership & Team Work',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Brand Building',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Brainstorming & Ideation',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Business Model & Business Planning',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ],
                    [
                        'title'         => 'Financial Literacy',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ],
                ],
            ]), collect([
                'grade'         => 'Level 3',
                'description'   => 'Advance',
                'child'         => [
                    [
                        'title'         => 'Build a Personal Brand',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Professional Communication',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Tools to Grow Business',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Understand Real-Life Problems',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Product/Service Creation',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Customer Validation',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Setting up a Business',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Raising Funds & Exit Strategy',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Industry Exposure - Part 1',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Industry Exposure - Part 2',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ], [
                        'title'         => 'Industry Exposure - Part 3',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ],
                    [
                        'title'         => 'Resourcefulness',
                        'creator_id'    => 1,
                        'creator'       => 'Super Admin',
                    ],
                ],
            ]),
        ]);

        foreach ($array as $ar) {
            $ar->except(['child'])->toArray();
            $agegroup = Grade::create($ar->except(['child'])->toArray());
            $agegroup->streams()->createMany($ar['child']);
        }
    }
}
