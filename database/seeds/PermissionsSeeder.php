<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        $models = ['course', 'class', 'assignment', 'page', 'submission', 'user'];
        $verbs = ['create', 'edit', 'delete', 'view'];

        $permissions = [];

        foreach ($models as $model) {
            foreach ($verbs as $verb) {
                $permission_name = $verb . ' ' . $model;
                $permissions[$model][] = $permission_name;

                if (Permission::where('name', $permission_name)->get()->count() === 0) {
                    Permission::create(['name' => $permission_name]);
                }
            }
        }

        $roles = ['super-admin', 'admin', 'instructor', 'student', 'student-grader'];
        $role_refs = [];

        foreach ($roles as $role) {
            $role_query = Role::where('name', $role)->get();

            if ($role_query->count() === 0) {
                $role_refs[$role] = Role::create(['name' => $role]);
            } else {
                $role_refs[$role] = $role_query->first();
            }
        }

        /// Role Assignment. Assume super admin has permission to everything.

        // Admin Permissions.
        foreach ($models as $model) {
            $role_refs['admin']->syncPermissions($permissions[$model]);
        }

        // Instructors.
        $role_refs['instructor']->syncPermissions($permissions['assignment']);
        $role_refs['instructor']->syncPermissions($permissions['page']);
        $role_refs['instructor']->syncPermissions($permissions['submission']);

        $role_refs['student']->syncPermissions($permissions['submission']);
        $role_refs['student']->syncPermissions([
            'view course',
            'view class',
            'view page',
            'view user',
            'view assignment',
        ]);

        // Extension to regular student.
        // Might just implicitly say student-graders are allowed to grade submission.
        // $role_refs['student-grader']->syncPermissions(['edit submission']);
    }
}
