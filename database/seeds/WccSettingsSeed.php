<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\WccSettings;
use Illuminate\Support\Facades\Config;

class WccSettingsSeed extends Seeder
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
            'title' => 'Wcc Settings',
            'name' => 'Wcc_settings_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'Wcc_settings_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'Wcc_settings_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'Wcc_settings_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'Wcc_settings_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'Wcc_settings_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Sort',
                'name' => 'Wcc_settings_sort',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('Wcc_settings_manage');
        $role->givePermissionTo('Wcc_settings_create');
        $role->givePermissionTo('Wcc_settings_edit');
        $role->givePermissionTo('Wcc_settings_active');
        $role->givePermissionTo('Wcc_settings_inactive');
        $role->givePermissionTo('Wcc_settings_destroy');
        $role->givePermissionTo('Wcc_settings_sort');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('Wcc_settings_manage');
//        $application_user->givePermissionTo('Wcc_settings_create');
        $application_user->givePermissionTo('Wcc_settings_edit');
//        $application_user->givePermissionTo('Wcc_settings_active');
//        $application_user->givePermissionTo('Wcc_settings_inactive');
//        $application_user->givePermissionTo('Wcc_settings_destroy');
        $application_user->givePermissionTo('Wcc_settings_sort');

        $global_Wcc_settings = Config::get('setup.wcc_settings');

        $wcc_settings = [];
        $d1=[null,null,null,null,null,null,null,null,'COD','self','self','self','self','self',null];
        $sort_number = 0;
        foreach($global_Wcc_settings as $Wcc_setting) {
            $wcc_settings[] = array(
                'name' => $Wcc_setting['name'],
                'slug' => $Wcc_setting['slug'],
                'data' => $d1[$sort_number],
                'sort_number'=> $sort_number++,
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            );
        }



        $global_wcc_settings = Config::get('setup.wcc_settings');

        $wcc_settings1 = [];
        $sort_number = 0;
       
        foreach($global_wcc_settings as $wcc_setting) {
            $wcc_settings1[] = array(
                'name' => $wcc_setting['name'],
                'slug' => $wcc_setting['slug'],
                'data' => $d1[$sort_number],
                'sort_number'=> $sort_number++,
                'account_id' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            );
        }
        WccSettings::insert($wcc_settings);

    }
}
