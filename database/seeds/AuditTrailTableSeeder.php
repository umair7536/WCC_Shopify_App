<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\AuditTrailTables;

class AuditTrailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $audit_trail_tables = [
            1 => array(
                'id' => 1,
                'name' => 'settings',
                'screen' => 'Setting',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            2 => array(
                'id' => 2,
                'name' => 'user_types',
                'screen' => 'User Types',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            3 => array(
                'id' => 3,
                'name' => 'users',
                'screen' => 'Users',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            4 => array(
                'id' => 4,
                'name' => 'role_has_users',
                'screen' => 'Role Has User',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            5 => array(
                'id' => 5,
                'name' => 'shopify_webhooks',
                'screen' => 'Shopify Webhooks',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            17 => array(
                'id' => 17,
                'name' => 'shopify_tags',
                'screen' => 'Tags',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            18 => array(
                'id' => 18,
                'name' => 'shopify_custom_collections',
                'screen' => 'Custom Collections',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            19 => array(
                'id' => 19,
                'name' => 'shopify_products',
                'screen' => 'Products',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            20 => array(
                'id' => 20,
                'name' => 'ticket_statuses',
                'screen' => 'Ticket Statuses',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            21 => array(
                'id' => 21,
                'name' => 'shopify_plans',
                'screen' => 'Shopify Plans',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            22 => array(
                'id' => 22,
                'name' => 'shopify_billings',
                'screen' => 'Shopify Billings',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            23 => array(
                'id' => 23,
                'name' => 'shopify_orders',
                'screen' => 'Shopify Orders',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            24 => array(
                'id' => 24,
                'name' => 'general_settings',
                'screen' => 'General Settings',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            25 => array(
                'id' => 25,
                'name' => 'leopards_settings',
                'screen' => 'Leopards Settings',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            26 => array(
                'id' => 26,
                'name' => 'leopards_cities',
                'screen' => 'Leopards Cities',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            26 => array(
                'id' => 26,
                'name' => 'shippers',
                'screen' => 'Shippers',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
            27 => array(
                'id' => 27,
                'name' => 'consignees',
                'screen' => 'Consignees',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
        ];

        if(count($audit_trail_tables)) {
            foreach ($audit_trail_tables as $audit_trail_table) {
                AuditTrailTables::updateOrCreate(
                    [
                        'id' => $audit_trail_table['id']
                    ],
                    $audit_trail_table
                );
            }
        }
    }
}
