<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\LoadSheets;
use Illuminate\Support\Facades\Config;

class LoadSheetsSeed extends Seeder
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
            'title' => 'LoadSheets',
            'name' => 'load_sheets_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);
        Permission::insert([
            [
                'title' => 'Edit',
                'name' => 'load_sheets_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ]
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('load_sheets_manage');
        $role->givePermissionTo('load_sheets_edit');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('load_sheets_manage');
        $application_user->givePermissionTo('load_sheets_edit');
    }
}
