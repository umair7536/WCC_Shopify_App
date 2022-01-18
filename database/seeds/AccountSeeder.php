<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Accounts;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Accounts::insert([
            1 => array(
//                'id' => 1,
                'name' => 'Lead Apparel',
                'email'=>' hello@leadapparel.com',
                'contact'=>'+1000000000',
                'resource_person'=>'Lead Apparel',
                'suspended'=>'0',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),
        ]);

    }
}
