<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            1 => [
                1 => ['view', 'create', 'edit', 'permissions'],
                2 => ['view', 'create', 'edit', 'security'],
                3 => ['view'],
                4 => ['view', 'create_course', 'manage_course', 'approve_course', 'edit_course_e', 'grade_course', 'view_participants', 'cancel_enrollment', 'manage_attendance'],
                5 => ['view'],
                6 => ['view', 'assign'],
                7 => ['view'],
            ],
            4 => [
                1 => ['view', 'create', 'edit', 'permissions'],
                3 => ['view'],
                4 => ['view', 'create_course', 'manage_course', 'approve_course', 'edit_course_e', 'grade_course', 'view_participants', 'cancel_enrollment', 'manage_attendance'],
                6 => ['view', 'assign'],
                7 => ['view'],
            ],
            2 => [
                4 => ['view', 'edit_course', 'grade_course', 'accept_course', 'manage_attendance'],
                5 => ['view'],
            ],
            3 => [
                4 => ['view', 'enroll', 'mark_attendance'],
                5 => ['view'],
            ],
        ];

        foreach ($mapping as $profileId => $processMap) {
            $permissionIds = collect();
            foreach ($processMap as $processId => $slugs) {
                $ids = DB::table('security.permissions')
                    ->where('process_id', $processId)
                    ->whereIn('slug', $slugs)
                    ->pluck('id');
                $permissionIds = $permissionIds->merge($ids);
            }

            foreach ($permissionIds->unique() as $permId) {
                DB::table('security.profile_permissions')->updateOrInsert(
                    ['profile_id' => $profileId, 'permission_id' => $permId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
