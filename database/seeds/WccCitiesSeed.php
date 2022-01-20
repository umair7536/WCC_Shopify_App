<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\WccCities;
use Illuminate\Support\Facades\Config;

class WccCitiesSeed extends Seeder
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
            'title' => 'Wcc Cities',
            'name' => 'Wcc_cities_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);

        Permission::insert([
            [
                'title' => 'Create',
                'name' => 'Wcc_cities_create',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Edit',
                'name' => 'Wcc_cities_edit',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Activate',
                'name' => 'Wcc_cities_active',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Inactivate',
                'name' => 'Wcc_cities_inactive',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Delete',
                'name' => 'Wcc_cities_destroy',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
            [
                'title' => 'Sort',
                'name' => 'Wcc_cities_sort',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('Wcc_cities_manage');
        $role->givePermissionTo('Wcc_cities_create');
        $role->givePermissionTo('Wcc_cities_edit');
        $role->givePermissionTo('Wcc_cities_active');
        $role->givePermissionTo('Wcc_cities_inactive');
        $role->givePermissionTo('Wcc_cities_destroy');

        $application_user = Role::findOrFail(2);
        $application_user->givePermissionTo('Wcc_cities_manage');
        $application_user->givePermissionTo('Wcc_cities_create');
//        $application_user->givePermissionTo('Wcc_cities_edit');
//        $application_user->givePermissionTo('Wcc_cities_active');
//        $application_user->givePermissionTo('Wcc_cities_inactive');
//        $application_user->givePermissionTo('Wcc_cities_destroy');
        
        
        $username=env('WCC_USERNAME');
        $password=env('WCC_PASSWORD');

        $req=file_get_contents('http://web.api.wcc.com.pk:3001/api/General/GetCityList?username='.$username.'&password='.$password);
        $j_data=json_decode($req);
        if($j_data=='1' || $j_data==true || $j_data==1)
        {
            $wcc_cities = [];
            foreach($j_data->Data as $data)
            {  
                $wcc_cities[] = array(
                    'city_id' => $data->CityCode,
                    'name' => $data->CityName,
                    'account_id' => 1,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                );

            }

            WccCities::insert($wcc_cities);
        }


    }
}
