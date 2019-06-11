<?php

namespace App\Http\Controllers\Admin;

use App\Models\RoleHasUsers;
use App\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB;
use Session;
use Validator;
use Config;
use Auth;
use Carbon\Carbon;

class UsersController extends Controller
{
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('users_manage')) {
            return abort(401);
        }

        $roles = Role::get()->pluck('name', 'id');
        $roles->prepend('All', '');

        return view('admin.users.index', compact('roles'));
    }

    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $records = array();
        $records["data"] = array();

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $Users = User::whereIn('id', $request->get('id'));
            if ($Users) {
                $Users->delete();
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        $where = array();

        $orderBy = 'users.created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        if ($request->get('name')) {
            $where[] = array(
                'users.name',
                'like',
                '%' . $request->get('name') . '%'
            );
        }
        if ($request->get('email')) {
            $where[] = array(
                'users.email',
                'like',
                '%' . $request->get('email') . '%'
            );
        }
        if ($request->get('phone') && $request->get('phone') != '') {
            $where[] = array(
                'users.phone',
                'like',
                '%' . $request->get('phone') . '%'
            );
        }
        if ($request->get('created_from') && $request->get('created_from') != '') {
            $where[] = array(
                'users.created_at',
                '>=',
                $request->get('created_from') . ' 00:00:00'
            );
        }
        if ($request->get('created_to') && $request->get('created_to') != '') {
            $where[] = array(
                'users.created_at',
                '<=',
                $request->get('created_to') . ' 23:59:59'
            );
        }

        if(Auth::User()->account_id == 1) {
            if (count($where)) {
                $iTotalRecords = count(DB::table('users')
                    ->select('users.id')
                    ->where($where)->get());
            } else {
                $iTotalRecords = count(DB::table('users')
                    ->select('users.id')
                    ->get());
            }
        } else {
            if (count($where)) {
                $iTotalRecords = count(DB::table('users')
                    ->select('users.id')
                    ->where([
                        [$where],
                        ['account_id', '=', session('account_id')]
                    ])->get());
            } else {
                $iTotalRecords = count(DB::table('users')
                    ->select('users.id')
                    ->where([
                        ['account_id', '=', session('account_id')]
                    ])->get());
            }
        }

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        if(Auth::User()->account_id == 1) {
            if (count($where)) {
                $Users = User::where([
                    [$where],
                ])->limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
            } else {
                $Users = User::limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
            }
        } else {
            if (count($where)) {
                $Users = User::where([
                    [$where],
                    ['account_id', '=', session('account_id')],
                ])->limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
            } else {
                $Users = User::where([
                    ['account_id', '=', session('account_id')],
                ])->limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
            }
        }
        if ($Users) {
            $index = 0;
            foreach ($Users as $user) {
                $roles = '';
                foreach ($user->roles()->pluck('name') as $role) {
                    $roles .= '<span class="label label-sm label-info">' . $role . '</span>&nbsp;';
                }
                $records["data"][$index] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' . $user->id . '"/><span></span></label>',
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'commission' => $user->commission . '%',
                    'gender' => view('admin.users.genderselection', compact('user'))->render(),
                    'roles' => $roles,
                    'created_at' => Carbon::parse($user->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.users.actions', compact('user'))->render(),
                );
                $index++;
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('users_create')) {
            return abort(401);
        }
        $user = new \stdClass();
        $user->phone = null;

        $roles = Role::where('id', '!=', '1')->get();
        $roles_commissions = Role::all();

        return view('admin.users.create', compact('roles', 'roles_commissions', 'user'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('users_create')) {
            return abort(401);
        }

        $validator = $this->verifyCreateFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $data = $request->all();

        $data['account_id'] = session('account_id');
        $data['main_account'] = '0';
        $data['user_type_id'] = Config::get('constants.application_user_id');

        if ($user = User::createRecord($data)) {

            $new_roles = [];
            $roles = $request->input('roles') ? $request->input('roles') : [];
            if(count($roles)) {
                foreach($roles as $role) {
                    if($role == 1) {
                        continue;
                    }
                }
                $roles = $new_roles;
            }
            $user->assignRole($roles);

            // Check if role exist and are set then assign role to users
            if ($request->get('roles') && is_array($request->get('roles'))) {
                $roles = $request->get('roles');
                $role_has_users = array();
                foreach ($roles as $role) {
                    $roleid = DB::table('roles')->select('id')->where('id', '=', $role)->first();
                    $role_has_users = array(
                        'role_id' => $roleid->id,
                        'user_id' => $user->id,
                    );
                    // Insert assigned role to users
                    RoleHasUsers::createRecord($role_has_users, $user);
                }
            }
        }
        flash('Record has been created successfully.')->success()->important();

        return response()->json(array(
            'status' => 1,
            'message' => 'Record has been created successfully.',
        ));
    }

    /**
     * Validate create form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyCreateFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required',
            'roles' => 'required',
        ]);
    }

    /**
     * Show the form for editing User.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function changePassword($id)
    {
        if (!Gate::allows('users_change_password')) {
            return abort(401);
        }

        $user = User::getData($id);
        if ($user == null) {
            return view('error');
        } else {
            return view('admin.users.change_password', compact('user'));
        }


    }

    /**
     * Update User Password in storage.
     *
     * @param  \App\Http\Requests\Admin\UpdateUsersRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function savePassword(Request $request)
    {
        if (!Gate::allows('users_change_password')) {
            return abort(401);
        }

        $validator = $this->verifyPasswordFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        try {
            $id = decrypt($request->get('id'));
        } catch (DecryptException $e) {
            flash('Are you mad? what were you trying to do? :@.')->error()->important();
            return redirect()->back();
        }

        $user = User::findOrFail($id);
        $user->update(array('password' => bcrypt($request->get('password'))));

        flash('Password has been changed successfully.')->success()->important();

        return response()->json(array(
            'status' => 1,
            'message' => 'Password has been changed successfully.',
        ));
    }

    /**
     * Validate create form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyPasswordFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:8',
        ]);
    }

    /**
     * Show the form for editing User.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('users_edit')) {
            return abort(401);
        }
        $roles = Role::where('id', '!=', '1')->get()->pluck('name', 'id');
        $roles_commissions = Role::all();

        $user = User::getData($id);

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update User in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Gate::allows('users_edit')) {
            return abort(401);
        }

        $validator = $this->verifyUpdateFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }

        $data = $request->all();

        if ($user = User::updateRecord($data, $id)) {

            $roles = $request->input('roles') ? $request->input('roles') : [];
            $user->syncRoles($roles);

            // Check if locations exist and are set then assign centres to User
            if ($request->get('roles') && is_array($request->get('roles'))) {
                // Destroy if user has locations
                $user->role_has_users()->forceDelete();

                $new_roles = [];
                $roles = $request->input('roles') ? $request->input('roles') : [];
                if(count($roles)) {
                    foreach($roles as $role) {
                        if($role == 1) {
                            continue;
                        }
                    }
                    $roles = $new_roles;
                }

                $role_has_users = array();
                foreach ($roles as $role) {
                    $roleid = DB::table('roles')->select('id')->where('id', '=', $role)->first();
                    $role_has_users = array(
                        'role_id' => $roleid->id,
                        'user_id' => $user->id,
                    );
                    // Insert assigned centres to User
                    RoleHasUsers::updateRecord($role_has_users, $user);
                }

            }
        }
        flash('Record has been updated successfully.')->success()->important();

        return response()->json(array(
            'status' => 1,
            'message' => 'Record has been updated successfully.',
        ));
    }

    /**
     * Validate create form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyUpdateFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'email' => 'required|email|unique:users,email,' . $request->route('user'),
            'roles' => 'required',
            'phone' => 'required',
        ]);
    }

    /**
     * Remove User from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('users_destroy')) {
            return abort(401);
        }

        User::deleteRecord($id);

        return redirect()->route('admin.users.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function inactive($id)
    {
        if (!Gate::allows('users_inactive')) {
            return abort(401);
        }
        $user = User::findOrFail($id);
        $user->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.users.index');
    }

    /**
     * Inactive Record from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function active($id)
    {
        if (!Gate::allows('users_active')) {
            return abort(401);
        }
        $user = User::findOrFail($id);
        $user->update(['active' => 1]);

        flash('Record has been inactivated successfully.')->success()->important();

        return redirect()->route('admin.users.index');
    }

}
