<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
    'courses.view', 'courses.create', 'courses.edit', 'courses.delete', 'courses.publish',
    'quizzes.manage',
    'students.view', 'students.import',
    'business.dashboard.view', 'business.export', 'business.assignments.manage',
    'certificates.view', 'certificates.download',
    'chatbot.use', 'content.index.manage',
    'campaigns.manage', 'email_templates.manage',
    'categories.manage', 'certificate_fonts.manage',
    'organizations.manage', 'enrollments.manage',
    'settings.site.manage', 'users.manage', 'roles.manage',
];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::findByName('admin')->syncPermissions(Permission::all());

        Role::findByName('instructor')->syncPermissions([
            'courses.view', 'courses.create', 'courses.edit',
            'quizzes.manage', 'students.view', 'certificates.view',
        ]); // no courses.delete, no courses.publish

        Role::findByName('business')->syncPermissions([
    'business.dashboard.view', 'business.export', 'business.assignments.manage', 'students.manage',
    'students.view', 'students.import',
    'certificates.view', 'certificates.download',
]);

Role::findOrCreate('manager')->syncPermissions([
    'business.dashboard.view', 'business.assignments.manage', 'students.manage',
    'students.view', 'certificates.view', 'certificates.download',
]);

        Role::findByName('student')->syncPermissions([
            'chatbot.use', 'certificates.view', 'certificates.download',
        ]);

    }
}
