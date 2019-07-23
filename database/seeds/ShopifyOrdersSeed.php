<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ShopifyShopifyOrders;

class ShopifyOrdersSeed extends Seeder
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
            'title' => 'Shopify Orders',
            'name' => 'shopify_orders_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'shopify_orders_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'shopify_orders_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'shopify_orders_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'shopify_orders_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'shopify_orders_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Sort',
                'name' => 'shopify_orders_sort',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('shopify_orders_manage');
        $role->givePermissionTo('shopify_orders_create');
//        $role->givePermissionTo('shopify_orders_edit');
//        $role->givePermissionTo('shopify_orders_active');
//        $role->givePermissionTo('shopify_orders_inactive');
//        $role->givePermissionTo('shopify_orders_destroy');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('shopify_orders_manage');
        $application_user->givePermissionTo('shopify_orders_create');
//        $application_user->givePermissionTo('shopify_orders_edit');
//        $application_user->givePermissionTo('shopify_orders_active');
//        $application_user->givePermissionTo('shopify_orders_inactive');
//        $application_user->givePermissionTo('shopify_orders_destroy');
    }
}
