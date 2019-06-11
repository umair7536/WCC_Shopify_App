<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ShopifyWebhooks;
use Illuminate\Support\Facades\Config;

class ShopifyWebhooksSeed extends Seeder
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
            'title' => 'Shopify Webhooks',
            'name' => 'shopify_webhooks_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'shopify_webhooks_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'shopify_webhooks_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'shopify_webhooks_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('shopify_webhooks_manage');
        $role->givePermissionTo('shopify_webhooks_create');
        $role->givePermissionTo('shopify_webhooks_edit');
        $role->givePermissionTo('shopify_webhooks_destroy');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('shopify_webhooks_manage');
        $application_user->givePermissionTo('shopify_webhooks_create');
        $application_user->givePermissionTo('shopify_webhooks_edit');
        $application_user->givePermissionTo('shopify_webhooks_destroy');

    }
}
