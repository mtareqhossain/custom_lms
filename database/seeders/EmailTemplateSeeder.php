<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'slug' => 'welcome_student',
                'name' => 'Welcome — Self-Registered Student',
                'subject' => 'Welcome to {{app_name}}!',
                'body' => '<p>Hi {{student_name}},</p>'
                    . '<p>Welcome to {{app_name}}! Your account is ready and you can start learning right away.</p>'
                    . '<p><a href="{{login_url}}">Log in to your dashboard</a></p>'
                    . '<p>See you inside,<br>The {{app_name}} Team</p>',
                'description' => 'Sent automatically right after a student self-registers.',
                'available_placeholders' => ['student_name', 'app_name', 'login_url'],
                'is_system' => true,
            ],
            [
                'slug' => 'welcome_business_import',
                'name' => 'Welcome — Business Bulk Import',
                'subject' => 'You\'ve been enrolled on {{app_name}} by {{organization_name}}',
                'body' => '<p>Hi {{student_name}},</p>'
                    . '<p>{{organization_name}} has set up an account for you on {{app_name}}.</p>'
                    . '<p>Click below to set your password and get started:</p>'
                    . '<p><a href="{{set_password_url}}">Set your password</a></p>'
                    . '<p>This link will expire for security reasons, so set your password soon.</p>',
                'description' => 'Sent when a business customer bulk-imports students via Excel. The signed password-setup link itself is generated securely in code — do not remove {{set_password_url}}.',
                'available_placeholders' => ['student_name', 'app_name', 'organization_name', 'set_password_url'],
                'is_system' => true,
            ],
            [
                'slug' => 'course_completed_student',
                'name' => 'Course Completed — Student Congratulations',
                'subject' => 'Congratulations on completing {{course_title}}!',
                'body' => '<p>Hi {{student_name}},</p>'
                    . '<p>Congratulations — you\'ve completed <strong>{{course_title}}</strong>!</p>'
                    . '<p><a href="{{certificate_url}}">Download your certificate</a></p>'
                    . '<p>Great work. Keep learning!<br>The {{app_name}} Team</p>',
                'description' => 'Sent automatically the moment a student reaches 100% progress on a course.',
                'available_placeholders' => ['student_name', 'course_title', 'certificate_url', 'app_name'],
                'is_system' => true,
            ],
            [
                'slug' => 'course_completed_instructor',
                'name' => 'Course Completed — Instructor Notice',
                'subject' => '{{student_name}} completed {{course_title}}',
                'body' => '<p>Hi {{instructor_name}},</p>'
                    . '<p>{{student_name}} just completed your course <strong>{{course_title}}</strong>.</p>'
                    . '<p><a href="{{certificate_url}}">View their certificate</a></p>',
                'description' => 'Sent automatically to the course instructor when a student completes their course.',
                'available_placeholders' => ['student_name', 'instructor_name', 'course_title', 'certificate_url'],
                'is_system' => true,
            ],
            [
                'slug' => 'course_completed_business',
                'name' => 'Course Completed — Organization Notice',
                'subject' => '{{student_name}} from {{organization_name}} completed {{course_title}}',
                'body' => '<p>Hi {{organization_name}} team,</p>'
                    . '<p>{{student_name}} has completed <strong>{{course_title}}</strong>.</p>'
                    . '<p><a href="{{certificate_url}}">View their certificate</a></p>'
                    . '<p>Log in to your business dashboard to see full progress across your team.</p>',
                'description' => 'Sent automatically to an organization\'s contact email when one of their enrolled students completes a course.',
                'available_placeholders' => ['student_name', 'organization_name', 'course_title', 'certificate_url'],
                'is_system' => true,
            ],
            [
                'slug' => 'campaign_blank',
                'name' => 'Campaign — Blank Starter',
                'subject' => 'A message from {{app_name}}',
                'body' => '<p>Hi {{student_name}},</p><p>Write your campaign message here.</p>',
                'description' => 'A starting point for one-off announcements sent via the Email Campaigns tool. Duplicate or edit freely — this one is not used automatically by any system event.',
                'available_placeholders' => ['student_name', 'app_name'],
                'is_system' => false,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(['slug' => $template['slug']], $template);
        }
    }
}
