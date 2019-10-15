<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BookedPacketsSeed extends Seeder
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
            'title' => 'Booked Packets',
            'name' => 'booked_packets_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'booked_packets_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'booked_packets_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'booked_packets_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'booked_packets_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'booked_packets_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('booked_packets_manage');
        $role->givePermissionTo('booked_packets_create');
        $role->givePermissionTo('booked_packets_edit');
        $role->givePermissionTo('booked_packets_active');
        $role->givePermissionTo('booked_packets_inactive');
        $role->givePermissionTo('booked_packets_destroy');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('booked_packets_manage');
        $application_user->givePermissionTo('booked_packets_create');
        $application_user->givePermissionTo('booked_packets_edit');
//        $application_user->givePermissionTo('booked_packets_active');
//        $application_user->givePermissionTo('booked_packets_inactive');
        $application_user->givePermissionTo('booked_packets_destroy');

    }
}
