<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\LeopardsSettings;
use Illuminate\Support\Facades\Config;

class LeopardsSettingsSeed extends Seeder
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
            'title' => 'Leopards Settings',
            'name' => 'leopards_settings_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'leopards_settings_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'leopards_settings_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'leopards_settings_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'leopards_settings_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'leopards_settings_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Sort',
                'name' => 'leopards_settings_sort',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('leopards_settings_manage');
        $role->givePermissionTo('leopards_settings_create');
        $role->givePermissionTo('leopards_settings_edit');
        $role->givePermissionTo('leopards_settings_active');
        $role->givePermissionTo('leopards_settings_inactive');
        $role->givePermissionTo('leopards_settings_destroy');
        $role->givePermissionTo('leopards_settings_sort');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('leopards_settings_manage');
//        $application_user->givePermissionTo('leopards_settings_create');
        $application_user->givePermissionTo('leopards_settings_edit');
//        $application_user->givePermissionTo('leopards_settings_active');
//        $application_user->givePermissionTo('leopards_settings_inactive');
//        $application_user->givePermissionTo('leopards_settings_destroy');
        $application_user->givePermissionTo('leopards_settings_sort');

        $global_leopards_settings = Config::get('setup.leopards_settings');

        $leopards_settings = [];
        $sort_number = 0;
        foreach($global_leopards_settings as $leopards_setting) {
            $leopards_settings[] = array(
                'name' => $leopards_setting['name'],
                'slug' => $leopards_setting['slug'],
                'data' => null,
                'sort_number'=> $sort_number++,
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            );
        }

        LeopardsSettings::insert($leopards_settings);

    }
}
