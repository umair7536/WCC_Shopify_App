<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShopifyCustomersSeed extends Seeder
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
            'title' => 'Customers',
            'name' => 'shopify_customers_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'shopify_customers_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'shopify_customers_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
//            [
//                'title' => 'Activate',
//                'name' => 'shopify_customers_active',
//                'guard_name' => 'web',
//                'main_group' => 0,
//                'created_at' => \Carbon\Carbon::now(),
//                'updated_at' => \Carbon\Carbon::now(),
//                'parent_id' => $MainPermission->id,
//            ],
//            [
//                'title' => 'Inactivate',
//                'name' => 'shopify_customers_inactive',
//                'guard_name' => 'web',
//                'main_group' => 0,
//                'created_at' => \Carbon\Carbon::now(),
//                'updated_at' => \Carbon\Carbon::now(),
//                'parent_id' => $MainPermission->id,
//            ],
            [
                'title' => 'Delete',
                'name' => 'shopify_customers_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ]
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('shopify_customers_manage');
        $role->givePermissionTo('shopify_customers_create');
        $role->givePermissionTo('shopify_customers_edit');
//        $role->givePermissionTo('shopify_customers_active');
//        $role->givePermissionTo('shopify_customers_inactive');
        $role->givePermissionTo('shopify_customers_destroy');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('shopify_customers_manage');
        $application_user->givePermissionTo('shopify_customers_create');
        $application_user->givePermissionTo('shopify_customers_edit');
//        $application_user->givePermissionTo('shopify_customers_active');
//        $application_user->givePermissionTo('shopify_customers_inactive');
        $application_user->givePermissionTo('shopify_customers_destroy');
    }
}
