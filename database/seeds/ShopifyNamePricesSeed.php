<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ShopifyNamePrices;
use Illuminate\Support\Facades\Config;

class ShopifyNamePricesSeed extends Seeder
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
            'title' => 'Custom Name Prices',
            'name' => 'shopify_name_prices_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'shopify_name_prices_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'shopify_name_prices_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'shopify_name_prices_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'shopify_name_prices_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'shopify_name_prices_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Sort',
                'name' => 'shopify_name_prices_sort',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('shopify_name_prices_manage');
        $role->givePermissionTo('shopify_name_prices_create');
        $role->givePermissionTo('shopify_name_prices_edit');
        $role->givePermissionTo('shopify_name_prices_active');
        $role->givePermissionTo('shopify_name_prices_inactive');
        $role->givePermissionTo('shopify_name_prices_destroy');
        $role->givePermissionTo('shopify_name_prices_sort');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('shopify_name_prices_manage');
        $application_user->givePermissionTo('shopify_name_prices_edit');

        $global_shopify_name_prices = Config::get('setup.shopify_name_prices');

        $shopify_name_prices = [];
        foreach($global_shopify_name_prices as $shopify_name_price) {
            $shopify_name_prices[] = array(
                'name' => $shopify_name_price['name'],
                'amount' => $shopify_name_price['amount'],
                'slug' => $shopify_name_price['slug'],
                'account_id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            );
        }

        ShopifyNamePrices::insert($shopify_name_prices);

    }
}
