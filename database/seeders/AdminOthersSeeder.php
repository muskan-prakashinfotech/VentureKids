<?php

namespace Database\Seeders;

use App\Models\AdminOthers;
use Illuminate\Database\Seeder;

/**
 * Seeds the `admin_others` table — a generic key/value settings store used
 * for the Terms of Use / Privacy Policy text shown on each portal, plus the
 * trainer guide and resources pages. This table was completely empty
 * (never seeded), so every page reading from it via
 * `AdminOthers::where('setting_name', ...)->first()->toArray()` crashed
 * with "Call to a member function toArray() on null" — e.g.
 * School\ManageschoolController::privacyPolice() reading 'school'.
 *
 * Backend\AdminsettingController reads two parallel key sets for the same
 * kind of content: the short keys ('school'/'student'/'teacher', read by
 * the live portal-facing pages) and the long keys
 * ('school'/'student'/'trainer'_terms_and_privacy_policy, read by the admin
 * edit forms) — both are seeded with the same original VentureKids terms
 * text per portal so neither crashes.
 */
class AdminOthersSeeder extends Seeder
{
    private const SCHOOL_TERMS = <<<'HTML'
<p style="font-size:18px;font-weight:700">Terms of Use</p>
<p>By registering your school with VentureKids, you agree to use the platform to support student entrepreneurship learning in good faith, and to ensure student accounts are used only for their intended educational purpose.</p>
<p>Your school is responsible for keeping login credentials secure and for promptly reporting any suspected unauthorized access to a school, trainer, or student account.</p>
<p style="font-size:18px;font-weight:700">Privacy Policy</p>
<p><span style="font-weight:bold">1. Information We Collect</span><br>We collect school contact details, trainer and student account information, and activity data needed to deliver the VentureKids curriculum and track learning progress.</p>
<p><span style="font-weight:bold">2. How We Use It</span><br>Information is used to operate the platform, generate progress and assessment reports, and communicate important updates to your school.</p>
<p><span style="font-weight:bold">3. Data Protection</span><br>We do not sell school or student data to third parties. Data is retained only as long as needed to provide the service to your school.</p>
HTML;

    private const STUDENT_TERMS = <<<'HTML'
<p style="font-size:18px;font-weight:700">Terms of Use</p>
<p>Welcome to VentureKids! By using this platform, you agree to complete your own work honestly, be respectful to trainers and classmates, and use your account only for learning activities.</p>
<p style="font-size:18px;font-weight:700">Privacy Policy</p>
<p><span style="font-weight:bold">1. Information We Collect</span><br>We collect the information your school provides when creating your account (such as your name and grade level) and your activity on the platform, like completed sessions, assignments, and assessment results.</p>
<p><span style="font-weight:bold">2. How We Use It</span><br>This information helps your trainers and school track your learning progress and helps us improve the VentureKids experience for you.</p>
<p><span style="font-weight:bold">3. Your Privacy</span><br>Your information is only shared with your school, your trainers, and VentureKids staff who support the platform — never sold to advertisers.</p>
HTML;

    private const TEACHER_TERMS = <<<'HTML'
<p style="font-size:18px;font-weight:700">Terms of Use</p>
<p>As a VentureKids trainer, you agree to deliver sessions, review student submissions, and provide feedback in a timely, professional, and supportive manner, in line with your school's agreement with VentureKids.</p>
<p style="font-size:18px;font-weight:700">Privacy Policy</p>
<p><span style="font-weight:bold">1. Information We Collect</span><br>We collect your contact details and activity on the platform, including sessions delivered, observations logged, and feedback you provide to students.</p>
<p><span style="font-weight:bold">2. How We Use It</span><br>This information is used to coordinate scheduling, generate student progress reports, and support your work as a trainer on the platform.</p>
<p><span style="font-weight:bold">3. Confidentiality</span><br>Student information you access as a trainer should only be used for delivering your sessions and providing feedback, and should not be shared outside the platform.</p>
HTML;

    private const TRAINER_GUIDE = <<<'HTML'
<p style="font-size:18px;font-weight:700">Trainer Guide</p>
<p>This guide helps new VentureKids trainers get started: reviewing your assigned schools and batches, delivering sessions from the content library, logging student observations, and reviewing assignment submissions.</p>
<p>For any questions, reach out to your VentureKids program coordinator.</p>
HTML;

    private const RESOURCES = <<<'HTML'
<p style="font-size:18px;font-weight:700">Resources</p>
<p>Find session guides, printable worksheets, and facilitation tips here to support your VentureKids sessions.</p>
HTML;

    public function run(): void
    {
        $entries = [
            'school' => self::SCHOOL_TERMS,
            'student' => self::STUDENT_TERMS,
            'teacher' => self::TEACHER_TERMS,
            'school_terms_and_privacy_policy' => self::SCHOOL_TERMS,
            'student_terms_and_privacy_policy' => self::STUDENT_TERMS,
            'trainer_terms_and_privacy_policy' => self::TEACHER_TERMS,
            'trainer_guide' => self::TRAINER_GUIDE,
            'resources' => self::RESOURCES,
        ];

        foreach ($entries as $settingName => $value) {
            AdminOthers::firstOrCreate(
                ['setting_name' => $settingName],
                ['setting_value' => $value]
            );
        }

        $this->command?->info('Seeded ' . count($entries) . ' admin_others settings (terms/privacy/guide/resources).');
    }
}
