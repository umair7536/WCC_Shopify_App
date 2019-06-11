<?php
namespace App;

use App\Models\Appointments;
use App\Models\DoctorHasLocations;
use App\Models\Doctors;
use App\Models\RoleHasUsers;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Hash;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AuditTrails;
use Auth;
use Config;


/**
 * Class User
 *
 * @package App
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
*/
class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use SoftDeletes;
    static protected  $PATIENT_GROUP = 3;
    static protected  $DOCTOR_GROUP = 5;

    protected $fillable = ['name', 'email', 'password', 'phone', 'main_account', 'user_type_id', 'account_id', 'active', 'created_at', 'updated_at'];

    protected static $_fillable = ['name', 'email', 'password', 'phone', 'main_account', 'user_type_id', 'active', 'created_at', 'updated_at'];

    protected static $_table = 'users';

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the role has users.
     */
    public function role_has_users()
    {
        return $this->hasMany('App\Models\RoleHasUsers', 'user_id')->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * Get the Location name with City Name.
     */
    public function getFullNameAttribute($value)
    {
        return ucfirst($this->name) . ' - ' . strtolower($this->email);
    }

    /**
     * Hash password
     * @param $input
     */

    public function setPasswordAttribute($input)
    {
        if ($input)
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
    }
    
    
    public function role()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Get the Users.
     */
    public function doctorhaslocation(){

        return $this->hasMany('App\Models\DoctorHasLocations', 'user_id');
    }


    static public function getData($id) {

        return self::where([
            ['id','=',$id],
            ['account_id','=',session('account_id')]
        ])->first();
    }

    static public function getUsers() {

        return self::where('account_id','=',session('account_id'))->whereNull('resource_type_id')->pluck('name','id');
    }
    /*
     * Get the users with  name & id
     */
    static public function getUsersleadReport() {
        return self::where('account_id','=',session('account_id'))->whereIn('user_type_id',[Config::get('constants.administrator_id'),Config::get('constants.application_user_id'),Config::get('constants.practitioner_id')])->pluck('name','id');
    }

    static public function getUsersWithTypeReport() {
        $allUsersObject = self::where('account_id','=',session('account_id'))->whereIn('user_type_id',[Config::get('constants.administrator_id'),Config::get('constants.application_user_id'),Config::get('constants.practitioner_id')])->select('name','id','user_type_id')->get();
        $allUsersWithTypes = [];
        $allUsersWithTypes['doctor'][''] = 'Select Practitioner';
        $allUsersWithTypes['app_user'][''] = 'Select App user';
        foreach ($allUsersObject as $user)
        {
//-------------------------- Both Admin & Application User ----------------------------------------
            if($user->user_type_id == 1 || $user->user_type_id == 2)
            {
                $allUsersWithTypes['app_user'][$user->id] = $user->name;
            }
//-------------------------- Doctors / Practitioner User ----------------------------------------
            if($user->user_type_id == 5)
            {
                $allUsersWithTypes['doctor'][$user->id] = $user->name;
            }
        }
        return $allUsersWithTypes;
    }
    /**
     * Create Record
     *
     * @param data
     *
     * @return (mixed)
     */
    static public function createRecord($data){

        $record = self::create($data);

        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

        return $record;
    }
    /**
     * update Record
     *
     * @param data
     *
     * @return (mixed)
     */
    static public function updateRecord($data,$id){

        $old_data= (User::find($id))->toArray();

        $record = User::findOrFail($id);

        $record->update($data);

        AuditTrails::editEventLogger(self::$_table, 'Edit', $data, self::$_fillable,$old_data,$id);

        return $record;
    }
    /**
     * delete Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function deleteRecord($id){

        $user = User::getData($id);

        if($user==null)
        {
            return view('error_full');
        }
        else{

            $record = $user->delete();

            AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

            flash('Record has been deleted successfully.')->success()->important();

            return $record;
        }
    }
    /**
     * delete Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function deleteRecord1($id){

        $doctor = User::getData($id);

        if (!$doctor) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.doctors.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (User::isExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.doctors.index');
        }

        $record = $doctor->delete();

        //log request for delete for audit trail

        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;
    }
    /**
     * isExit
     *
     * @param id, account id
     *
     * @return (mixed)
     */
    static public function isExists($id, $account_id)
    {
        if (
            DoctorHasLocations::where(['user_id' => $id])->count() ||
            Appointments::where(['doctor_id' => $id])->count()
        ) {
            return true;
        }

        return false;
    }
    /**
     * Inactive Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function inactiveRecord($id){

        $user = User::getData($id);
        if ($user == null) {
            return view('error_full');
        } else {

            $record = $user->update(['active'=>0]);

            flash('Record has been inactivated successfully.')->success()->important();

            AuditTrails::InactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

            return $record;
        }
    }
    /**
     * Active Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function activeRecord($id){

        $user = User::getData($id);

        if ($user == null) {
            return view('error_full');
        } else {
            $record = $user->update(['active'=>1]);

            flash('Record has been activated successfully.')->success()->important();

            AuditTrails::activeEventLogger(self::$_table, 'active', self::$_fillable, $id);

            return $record;


        }

    }
    /**
     * Get patients
     * @return (mixed)
     */
    static public function getPatients(){

        return self::where([
            ['user_type_id','=',Config::get('constants.patient_id')],
            ['account_id','=',session('account_id')]
        ])->get();

    }

    /*
    * Get Bulk Data
    *
    * @param (int)|(array) $id
    *
    * @return (mixed)
    */
    static public function getBulkData($id) {
        if(!is_array($id)) {
            $id = array($id);
        }
        return self::where([
            ['account_id','=',session('account_id')]
        ])->whereIn('id', $id)
            ->get();
    }

    /*
     * Find user for patient profile and checkout account id
     *
     * @param id
     *
     * @return patient
     * */
    static public function finduser($id){
        return self::where([
            ['account_id','=',session('account_id')],
            ['id','=',$id]
        ])->first();
    }

    /**
     * Get All Records
     *
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getAllRecords($account_id)
    {
        return self::where(['account_id' => $account_id])->whereNotIn('user_type_id', [self::$PATIENT_GROUP])->get();
    }

    /**
     * Get All Active Records
     *
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getAllActiveRecords($account_id)
    {
        return self::where(['active' => 1, 'account_id' => $account_id])->whereNotIn('user_type_id', [self::$PATIENT_GROUP])->get();
    }

    /**
     * Get All Active Records
     *
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getAllSystemUsersActiveRecords($account_id)
    {
        return self::where(['active' => 1, 'account_id' => $account_id])->whereNotIn('user_type_id', [self::$PATIENT_GROUP, self::$DOCTOR_GROUP])->get();
    }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveOnly($locationId = false, $account_id = false, $user_id = false, $pluck_columns = true)
    {
        if($locationId && !is_array($locationId)) {
            $locationId = array($locationId);
        }
        if ($user_id && !is_array($user_id)) {
            $user_id = array($user_id);
        }

        if($locationId) {
            if($account_id) {
                if ($user_id) {
                    $query = self::join('user_has_locations', function ($join) use ($account_id) {
                        $join->on('users.id', '=', 'user_has_locations.user_id')
                            ->where('users.user_type_id', '=', config('constants.application_user_id'))
                            ->where('users.active', '=', 1)
                            ->where('users.account_id', '=', $account_id);
                    })
                        ->whereIn('user_has_locations.location_id', $locationId)
                        ->whereIn('users.id', $user_id)
                        ->get();
                    if($pluck_columns) {
                        $query = $query->pluck('name', 'user_id');
                    }
                    return $query;
                } else {
                    $query = self::join('user_has_locations', function ($join) use ($account_id) {
                        $join->on('users.id', '=', 'user_has_locations.user_id')
                            ->where('users.user_type_id', '=', config('constants.application_user_id'))
                            ->where('users.active', '=', 1)
                            ->where('users.account_id', '=', $account_id);
                    })
                        ->whereIn('user_has_locations.location_id', $locationId)
                        ->get();
                    if($pluck_columns) {
                        $query = $query->pluck('name', 'user_id');
                    }
                    return $query;
                }
            }

            if ($user_id) {
                $query = self::join('user_has_locations', function ($join) {
                    $join->on('users.id', '=', 'user_has_locations.user_id')
                        ->where('users.user_type_id', '=', config('constants.application_user_id'))
                        ->where('users.active', '=', 1);
                })
                    ->whereIn('users.id', $user_id)
                    ->whereIn('user_has_locations.location_id', $locationId)
                    ->get();
                if($pluck_columns) {
                    $query = $query->pluck('name', 'user_id');
                }
                return $query;
            } else {
                $query = self::join('user_has_locations', function ($join) {
                    $join->on('users.id', '=', 'user_has_locations.user_id')
                        ->where('users.user_type_id', '=', config('constants.application_user_id'))
                        ->where('users.active', '=', 1);
                })
                    ->whereIn('user_has_locations.location_id', $locationId)
                    ->get();
                if($pluck_columns) {
                    $query = $query->pluck('name', 'user_id');
                }
                return $query;
            }
//            $query = self::whereIn('location_id',$locationId)->get()->pluck('name','id');
        } else {
            if($account_id) {
                if ($user_id) {
                    $query = self::where('users.user_type_id', '=', config('constants.application_user_id'))
                        ->where('users.active', '=', 1)
                        ->where('users.account_id', '=', $account_id)
                        ->whereIn('users.id', $user_id)
                        ->get();
                    if($pluck_columns) {
                        $query = $query->pluck('name', 'id');
                    }
                    return $query;
                } else {
                    $query = self::where('users.user_type_id', '=', config('constants.application_user_id'))
                        ->where('users.active', '=', 1)
                        ->where('users.account_id', '=', $account_id)
                        ->get();
                    if($pluck_columns) {
                        $query = $query->pluck('name', 'id');
                    }
                    return $query;
                }
            }

            if ($user_id) {
                $query = self::where('users.user_type_id', '=', config('constants.application_user_id'))
                    ->where('users.active', '=', 1)
                    ->whereIn('users.id', $user_id)
                    ->get();
                if($pluck_columns) {
                    $query = $query->pluck('name', 'id');
                }
                return $query;
            } else {
                $query = self::where('users.user_type_id', '=', config('constants.application_user_id'))
                    ->where('users.active', '=', 1)->get();
                if($pluck_columns) {
                    $query = $query->pluck('name', 'id');
                }
                return $query;
            }
//            $query = self::get()->pluck('name','id');
        }
    }
}
