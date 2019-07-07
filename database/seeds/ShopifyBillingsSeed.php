<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ShopifyBillings;
use Illuminate\Support\Facades\Config;

class ShopifyBillingsSeed extends Seeder
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
            'title' => 'Shopify Billings',
            'name' => 'shopify_billings_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('shopify_billings_manage');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('shopify_billings_manage');

    }
}
