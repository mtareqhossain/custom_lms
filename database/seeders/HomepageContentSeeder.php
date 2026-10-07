<?php
namespace Database\Seeders;

use App\Models\HomepageContent;
use Illuminate\Database\Seeder;

/** Seeds the single homepage_content row with the same copy the homepage originally shipped with. */
class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        HomepageContent::query()->updateOrCreate(['id' => 1], [
            'hero_headline' => 'Cost-effective training that proves who finished.',
            'hero_subheadline' => 'Browse accredited-style courses across compliance, safety and business skills — or bring your whole team and manage everyone from one dashboard.',
            'hero_image' => null,

            'business_headline' => 'Train your whole team without the admin headache',
            'business_subheadline' => 'Upload a spreadsheet, we create the accounts and enroll everyone. Watch progress in real time. Export certificates the moment someone finishes.',
            'business_features' => [
                ['icon' => 'users', 'title' => 'Bulk enrollment', 'text' => 'One spreadsheet enrolls your whole team in minutes.'],
                ['icon' => 'grid', 'title' => 'Live team dashboard', 'text' => 'See who has started, who is stuck, who has finished.'],
                ['icon' => 'award', 'title' => 'Exportable certificates', 'text' => 'Download every completed certificate as one ZIP.'],
                ['icon' => 'book', 'title' => 'Access you control', 'text' => 'Set how long each course stays open for your team.'],
            ],

            'feature_strip' => [
                ['icon' => 'grid', 'title' => 'One dashboard for everyone', 'text' => 'Admins, instructors and business teams each get a dashboard built for what they actually need to do.'],
                ['icon' => 'award', 'title' => 'Proof that holds up', 'text' => 'Every completion issues a certificate with a public, shareable verification link.'],
                ['icon' => 'users', 'title' => 'Built for real teams', 'text' => 'From a single learner to hundreds of staff enrolled in one spreadsheet upload.'],
            ],

            'why_us_points' => [
                'Learn at your own pace, from anywhere',
                'Flexible plans for individuals, teams and corporate accounts',
                'Enroll in a click and start straight away',
                'Course content built by people who know the subject',
                'Certificates that are easy to verify and share',
            ],

            'instructor_cta_headline' => 'Become an instructor',
            'instructor_cta_text' => "Create and manage your own courses, track your students' progress, and get paid for what you teach — no admin access required.",

            'client_logos' => ['Acme Retail', 'Northwind Logistics', 'Bright Care Group', 'Summit Hospitality', 'Riverside Foods', 'Clearline Facilities'],
            'testimonials' => [
                ['quote' => 'This is a placeholder testimonial — replace with real feedback from a real learner before launch.', 'name' => 'Placeholder Name', 'role' => 'Placeholder Role'],
                ['quote' => 'Another placeholder quote. Do not publish invented feedback as if it came from a real person.', 'name' => 'Placeholder Name', 'role' => 'Placeholder Role'],
            ],
            'certifications' => ['CPD Certified', 'ISO 9001', 'IIRSM Approved', 'RoSPA Assured'],

            'final_cta_headline' => 'Ready to get started?',
            'final_cta_subheadline' => 'Start with a free course today, or get in touch to set up training for your team.',
        ]);
    }
}
