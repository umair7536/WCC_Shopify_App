<?php

use Illuminate\Database\Seeder;
use App\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Permissions has been added
        $MainPermission = Permission::create([
            'title' => 'Users',
            'name' => 'users_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);
        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'users_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'users_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Change Password',
                'name' => 'users_change_password',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'users_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ]
        ]);

        $role = Role::findOrFail(1);
        $application_user = Role::findOrFail(2);
        $team_player = Role::findOrFail(3);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('users_manage');
        $role->givePermissionTo('users_create');
        $role->givePermissionTo('users_edit');
        $role->givePermissionTo('users_change_password');
        $role->givePermissionTo('users_destroy');

        $application_user->givePermissionTo('users_manage');
        $application_user->givePermissionTo('users_create');
        $application_user->givePermissionTo('users_edit');
        $application_user->givePermissionTo('users_change_password');
        $application_user->givePermissionTo('users_destroy');

        $team_player->givePermissionTo('users_manage');
        $team_player->givePermissionTo('users_create');

        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'operations@developify.net',
            'phone' => '+924235441050',
            'user_type_id'=>'1',
            'account_id'=>'1',
            'main_account'=>'1',
            'password' => bcrypt('password')
        ]);
        $user->assignRole('administrator');

    }
}
