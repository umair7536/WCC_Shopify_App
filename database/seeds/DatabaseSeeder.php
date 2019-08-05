<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->call(PermissionSeed::class);
        $this->call(RoleSeed::class);
        //Account Seeder
        $this->call(AccountSeeder::class);
        //UserType Seeder
        $this->call(UserTypesSeeder::class);
        //User Seeder
        $this->call(UserSeed::class);
        // Settings Seeder
        $this->call(SettingsSeed::class);
        //Audit action seeder
        $this->call(AuditTrailActionSeeder::class);
        //Audit table seeder
        $this->call(AuditTrailTableSeeder::class);
        //Audit trail seeder
        $this->call(AuditTrailSeeder::class);

        // Shopify Webhooks Seeder
        $this->call(ShopifyWebhooksSeed::class);

        // Tags Seeder
        $this->call(ShopifyTagsSeed::class);

        // Custom Collections Seeder
        $this->call(ShopifyCustomCollectionsSeed::class);

        // Custom Collections Seeder
        $this->call(ShopifyProductsSeed::class);

        // Shopify Customers Seeder
        $this->call(ShopifyCustomersSeed::class);

        // Ticket Statuses Seeder
        $this->call(TicketStatusesSeed::class);

        // Tickets Seeder
        $this->call(TicketsSeed::class);

        // Tickets Seeder
        $this->call(ShopifyPlansSeed::class);

        // Tickets Seeder
        $this->call(ShopifyBillingsSeed::class);

        // General Settings Seeder
        $this->call(GeneralSettingsSeed::class);

        // Shopify Orders Seeder
        $this->call(ShopifyOrdersSeed::class);

        // Leopards Cities Seeder
        $this->call(leopardsCitiesSeed::class);

        // Leopards Settings Seeder
        $this->call(LeopardsSettingsSeed::class);
    }
}
