<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\GeneralSettings;
use Illuminate\Support\Facades\Config;

class GeneralSettingsSeed extends Seeder
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
            'title' => 'General Settings',
            'name' => 'general_settings_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'general_settings_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'general_settings_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'general_settings_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'general_settings_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'general_settings_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Sort',
                'name' => 'general_settings_sort',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('general_settings_manage');
        $role->givePermissionTo('general_settings_create');
        $role->givePermissionTo('general_settings_edit');
        $role->givePermissionTo('general_settings_active');
        $role->givePermissionTo('general_settings_inactive');
        $role->givePermissionTo('general_settings_destroy');
        $role->givePermissionTo('general_settings_sort');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('general_settings_manage');
//        $application_user->givePermissionTo('general_settings_create');
        $application_user->givePermissionTo('general_settings_edit');
//        $application_user->givePermissionTo('general_settings_active');
//        $application_user->givePermissionTo('general_settings_inactive');
//        $application_user->givePermissionTo('general_settings_destroy');
        $application_user->givePermissionTo('general_settings_sort');

        $global_general_settings = Config::get('setup.general_settings');

        $general_settings = [];
        $sort_number = 0;
        foreach($global_general_settings as $general_setting) {
            $general_settings[] = array(
                'name' => $general_setting['name'],
                'slug' => $general_setting['slug'],
                'data' => null,
                'sort_number'=> $sort_number++,
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            );
        }

        GeneralSettings::insert($general_settings);

    }
}
