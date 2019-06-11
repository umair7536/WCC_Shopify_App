<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\TicketStatuses;
use Illuminate\Support\Facades\Config;

class TicketStatusesSeed extends Seeder
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
            'title' => 'Ticket Statuses',
            'name' => 'ticket_statuses_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'ticket_statuses_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'ticket_statuses_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'ticket_statuses_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'ticket_statuses_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'ticket_statuses_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Sort',
                'name' => 'ticket_statuses_sort',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('ticket_statuses_manage');
        $role->givePermissionTo('ticket_statuses_create');
        $role->givePermissionTo('ticket_statuses_edit');
        $role->givePermissionTo('ticket_statuses_active');
        $role->givePermissionTo('ticket_statuses_inactive');
        $role->givePermissionTo('ticket_statuses_destroy');
        $role->givePermissionTo('ticket_statuses_sort');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('ticket_statuses_manage');
        $application_user->givePermissionTo('ticket_statuses_create');
        $application_user->givePermissionTo('ticket_statuses_edit');
        $application_user->givePermissionTo('ticket_statuses_active');
        $application_user->givePermissionTo('ticket_statuses_inactive');
        $application_user->givePermissionTo('ticket_statuses_destroy');
        $application_user->givePermissionTo('ticket_statuses_sort');

        $global_ticket_statuses = Config::get('setup.ticket_statuses');

        $ticket_statuses = [];
        $sort_number = 0;
        foreach($global_ticket_statuses as $ticket_statuse) {
            $ticket_statuses[] = array(
                'name' => $ticket_statuse['name'],
                'slug' => $ticket_statuse['slug'],
                'sort_number'=> $sort_number++,
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            );
        }

        TicketStatuses::insert($ticket_statuses);

    }
}
