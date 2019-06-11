<?php

namespace App\Http\Controllers\Admin;

use App\Models\RoleHasUsers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Validator;
use Auth;
use DB;
use App\Models\Cities;

class RolesController extends Controller
{
    /**
     * Display a listing of Role.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('roles_manage')) {
            return abort(401);
        }

        return view('admin.roles.index');
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
            $Roles = Role::whereIn('id', $request->get('id'))->get();
            $any_deleted = false;
            foreach ($Roles as $role){
                if(!self::isChildExists($role->id, Auth::User()->account_id)) {
                    $any_deleted = true;
                    $role->delete();
              }
            }
            if($any_deleted){
                $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
                $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
            } else {
                $records["customActionStatus"] = "NO"; // pass custom message(useful for getting status of group actions)
                $records["customActionMessage"] = "One or more records are not deleted!"; // pass custom message(useful for getting status of group actions)
            }
        }

        $where = array();

        $orderBy = 'name';
        $order = 'asc';

        if($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        if($request->get('name')) {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
        }

        if ($request->get('commission') != '') {
            $where[] = array(
                'commission',
                '=',
                $request->get('commission')
            );
        }

        if(count($where)) {
            $iTotalRecords = Role::where($where)->count();
        } else {
            $iTotalRecords = Role::count();
        }

        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        if(count($where)) {
            $Roles = Role::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy, $order)->get();
        } else {
            $Roles = Role::limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy, $order)->get();
        }

        if($Roles) {
            $index = 0;
            foreach($Roles as $role) {
                $permissions = '';
                foreach($role->permissions()->pluck('name') as $permission) {
                    $permissions .= '<span class="label label-sm label-info">' . $permission . '</span>&nbsp;';
                }
                $records["data"][$index] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$role->id.'"/><span></span></label>',
                    'name' => $role->name,
                    'commission' => $role->commission . '%',
                    'permissions' => $permissions,
                    'actions' => view('admin.roles.actions', compact('role'))->render(),
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
     * Show the form for creating new Role.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('roles_create')) {
            return abort(401);
        }

        // Get list of all allowed permissions for current role.
        $AllowedPermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->get()->pluck('name', 'id');
        if(!$AllowedPermissions) {
            $AllowedPermissions = [];
        }

        $mapping = $this->getAllPermissionsMapping();

        $Permissions = $mapping['Permissions'];

        $permissionsMapping = $mapping['permissionsMapping'];

        return view('admin.roles.create', compact(
            'Permissions', 'permissionsMapping',
            'AllowedPermissions'
        ));
    }

    /**
     * Store a newly created Role in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('roles_create')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }
        unset($request['DataTables_Table_0_length']);
        $role = Role::create($request->except('permission'));
        $permissions = $request->input('permission') ? $request->input('permission') : [];
        $role->givePermissionTo($permissions);

        flash('Record has been created successfully.')->success()->important();

        return response()->json(array(
            'status' => 1,
            'message' => 'Record has been created successfully.',
        ));
    }

    /**
     * Prepare Permissions to display in table
     *
     * @param  (void)
     * @return (array) $array
     */
    protected function preparePermissionsMapping() {
        /*
         * Note: Mapping will go like below examples
         * permissions_create
         * permissions_edit
         * permissions_active
         * permissions_inactive
         * permissions_destroy
         * users_change_password
         */
        return array(
            'create' => 'Create',
            'edit' => 'Edit',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'destroy' => 'Delete',
            'sort' => 'Sort',
            'change_password' => 'Change Password',
        );
    }

    /**
     * Prepare Dashboard Permissions to display in table
     *
     * @param  (void)
     * @return (array) $array
     */
    protected function prepareDashboardPermissionsMapping() {
        /*
         * Note: Mapping will go like below examples
         * permissions_create
         * permissions_edit
         * permissions_active
         * permissions_inactive
         * permissions_destroy
         * users_change_password
         */
        return array(
            'revenue_by_centre' => 'Revenue by Centre',
            'revenue_by_service' => 'Revenue by Service',
            'appointment_by_status' => 'Appointment by Status',
            'appointment_by_type' => 'Appointment by Type',
            'my_revenue_by_centre' => 'My Revenue by Centre',
            'my_revenue_by_service' => 'My Revenue by Service',
            'my_appointment_by_status' => 'My Appointment by Status',
            'my_appointment_by_type' => 'My Appointment by Type',
        );
    }



    /**
     * Prepare Reports Permissions to display in table
     *
     * @param  (void)
     * @return (array) $array
     */
    protected function prepareReportsPermissionsMapping() {
        /*
         * Note: Mapping will go like below examples
         * permissions_create
         * permissions_edit
         * permissions_active
         * permissions_inactive
         * permissions_destroy
         * users_change_password
         */
        return array(
            'general_report' => 'General Report',
            'summary_report_by_lead_status' => 'Summary Report By Lead Status',
            'lead_status_percentage' => 'Lead Status Percentage',
            'staff_appointment' => 'Staff Wise Appointment Report',
            'referred_by_staff_appointment' => 'Staff Wise (Referred By) Appointment Report',
            'empolyee_summary' => 'Appointment Summary Report',
            'summary_by_service' => 'Appointments Summary by Service',
            'summary_by_appointment_status' => 'Appointments Summary by Status',
            'clients_by_appointment_status' => 'Patient by Appointment Status (Date Wise)',
            'operations_company_health' => 'Company Health Report',
            'Highest_paying_clients' => 'Highest Paying Clients',
            'List_of_refunds_for_a_certain_period_date_based' => 'List of refunds for a certain period (date based)',
            'List_of_services_that_CAN_be_offered_Complimentary' => 'List of services that CAN be offered Complimentary',
            'List_of_services_that_CAN_not_be_offered_Complimentary' => 'List of services that CAN NOT be offered Complimentary',
            'conversion_report_consultancy' => 'Conversion Report For Consultancy',
            'conversion_report_treatment' => 'Conversion Report For Treatment',
            'client_with_Completed_treatment' => 'Clients with completed treatments',
            'dar_report' => 'DAR Report',
            'complimentory_report' => 'Complimentory Treatment',
            'dtr_report' => 'DTR Report',
            'client_with_not_Completed_treatment' => 'Clients with not completed treatments',
            'clients_took_treatments_particular_month' => 'Clients with treatments in a particular month',
            'clients_with_birthday_days' => 'Clients with birthday + x days',
            'reports_for_calculating_incentives' => 'Reports For Calculating Incentives',
            'reports_for_calculating_incentives_detail' => 'Reports For Calculating Incentives Detail',
            'revenue_generated_by_operators_application_user' => 'Revenue Generated By Operators (Application User)',
            'revenue_generated_by_consultants_practitioner' => 'Revenue Generated By Consultants (Practitioner)',
            'center_performance_stats_by_revenue' => 'Center performance stats by Revenue',
            'center_performance_stats_by_service_type' => 'Center performance stats by Service Type',
            'account_sales_report' => 'Account Sales Report',
            'daily_employee_stats_summary' => 'Sale Summary Service Wise',
            'daily_employee_stats' => 'Sale Summary Doctors Wise',
            'discount_report' => 'Discount Report',
            'Customer_payment_ledger_all_entries' => 'Customer Payment Ledger',
            'customer_treatment_package_ledger' => 'Customer Treatment Package Ledger',
            'list_of_advances_as_of_today' => 'List of Advances as of Today',
            'list_of_outstanding_as_of_today' => 'List of Outstanding as of Today',
            'Summarized_data_of_Discounts_given_to_the_customer' => 'Summarized Data of Discounts given to the Customer',
            'List_of_Clients_who_claimed_refunds' => 'List of Clients Who Claimed Refunds',
            'region_wise_staff_list' => 'Region Wise Staff List',
            'centre_wise_staff_list' => 'Centre Wise Staff List',
            'center_performance_stats_by_revenue' => 'Staff Revenue Centre Wise',
            'center_performance_stats_by_service_type' => 'Staff Revenue by Service Type',
        );
    }

    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
    }


    /**
     * Show the form for editing Role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('roles_edit')) {
            return abort(401);
        }

        $role = Role::findOrFail($id);

        // Get list of all allowed permissions for current role.
        $AllowedPermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where(['role_has_permissions.role_id' => $role->id])
            ->get()->pluck('name', 'id');
        if(!$AllowedPermissions) {
            $AllowedPermissions = [];
        }

        $mapping = $this->getAllPermissionsMapping();

        $Permissions = $mapping['Permissions'];
        $DashboardPermissions = $mapping['DashboardPermissions'];
        $ReportsPermissions = $mapping['ReportsPermissions'];

        $permissionsMapping = $mapping['permissionsMapping'];
        $dashboardPermissionsMapping = $mapping['dashboardPermissionsMapping'];
        $reportsPermissionsMapping = $mapping['reportsPermissionsMapping'];

        return view('admin.roles.edit', compact(
            'role', 'AllowedPermissions',
            'dashboardPermissionsMapping', 'DashboardPermissions',
            'Permissions', 'permissionsMapping',
            "reportsPermissionsMapping", 'ReportsPermissions'
        ));
    }

    /**
     * Get All Permissions Mappings
     *
     * @param  (void)
     * @return (array) $array
     */
    protected function getAllPermissionsMapping()
    {
        /*
         * General Permissions
         */
        $notInArray = array(
            'dashboard_manage', 'leads_reports_manage', 'appointment_reports_manage', 'operations_reports_manage', 'centers_reports_manage', 'Hr_reports_manage','finance_general_revenue_reports_manage','finance_revenue_breakup_reports_manage','finance_ledger_reports_manage','staff_listing_reports_manage','staff_revenue_reports_manage','marketing_reports_manage'
        );
        $GroupPermissions = Permission::
        where(['main_group' => 1, 'status' => 1])
            ->whereNotIn('name', $notInArray)
            ->get();
        $SubPermissions = Permission::whereIn('parent_id', Permission::where(['main_group' => 1, 'status' => 1])->whereNotIn('name', $notInArray)->pluck('id', 'name'))->get()->keyBy('id');

        $Permissions = array();
        if($GroupPermissions) {
            foreach($GroupPermissions as $groupPermission) {
                $Permissions[$groupPermission->id] = array(
                    'id' => $groupPermission->id,
                    'title' => $groupPermission->title,
                    'name' => $groupPermission->name,
                    'parent_id' => $groupPermission->parent_id,
                    'children' => array(),
                    'key' => str_replace_last('manage', '', $groupPermission->name),
                );

                if($SubPermissions) {
                    foreach($SubPermissions as $SubPermission) {
                        if(array_key_exists($SubPermission->parent_id, $Permissions)) {
                            $Permissions[$SubPermission->parent_id]['children'][$SubPermission->name] = array(
                                'id' => $SubPermission->id,
                                'title' => $SubPermission->title,
                                'name' => $SubPermission->name,
                                'parent_id' => $SubPermission->parent_id,
                            );
                        }
                    }
                }
            }
        }

        /*
         * Dashboard Permissions
         */
        $whereIn = array(
            'dashboard_manage'
        );
        $DashboardGroupPermissions = Permission::
        where(['main_group' => 1, 'status' => 1])->
        whereIn('name', $whereIn)
            ->get();
        $DashboardSubPermissions = Permission::whereIn('parent_id', Permission::where(['main_group' => 1, 'status' => 1])->whereIn('name', $whereIn)->pluck('id', 'name'))->get()->keyBy('id');

        $DashboardPermissions = array();
        if($DashboardGroupPermissions) {
            foreach($DashboardGroupPermissions as $groupPermission) {
                $DashboardPermissions[$groupPermission->id] = array(
                    'id' => $groupPermission->id,
                    'title' => $groupPermission->title,
                    'name' => $groupPermission->name,
                    'parent_id' => $groupPermission->parent_id,
                    'children' => array(),
                    'key' => str_replace_last('manage', '', $groupPermission->name),
                );

                if($DashboardSubPermissions) {
                    foreach($DashboardSubPermissions as $SubPermission) {
                        if(array_key_exists($SubPermission->parent_id, $DashboardPermissions)) {
                            $DashboardPermissions[$SubPermission->parent_id]['children'][$SubPermission->name] = array(
                                'id' => $SubPermission->id,
                                'title' => $SubPermission->title,
                                'name' => $SubPermission->name,
                                'parent_id' => $SubPermission->parent_id,
                            );
                        }
                    }
                }
            }
        }

        /*
         * Reports Permissions
         */
        $whereIn = array(
            'leads_reports_manage', 'appointment_reports_manage', 'operations_reports_manage', 'centers_reports_manage', 'Hr_reports_manage','finance_general_revenue_reports_manage','finance_revenue_breakup_reports_manage','finance_ledger_reports_manage','staff_listing_reports_manage','staff_revenue_reports_manage','marketing_reports_manage'
        );
        $ReportsGroupPermissions = Permission::
        where(['main_group' => 1, 'status' => 1])->
        whereIn('name', $whereIn)
            ->get();
        $ReportSubPermissions = Permission::whereIn('parent_id', Permission::where(['main_group' => 1, 'status' => 1])->whereIn('name', $whereIn)->pluck('id', 'name'))->get()->keyBy('id');

        $ReportsPermissions = array();
        if($ReportsGroupPermissions) {
            foreach($ReportsGroupPermissions as $groupPermission) {
                $ReportsPermissions[$groupPermission->id] = array(
                    'id' => $groupPermission->id,
                    'title' => $groupPermission->title,
                    'name' => $groupPermission->name,
                    'parent_id' => $groupPermission->parent_id,
                    'children' => array(),
                    'key' => str_replace_last('manage', '', $groupPermission->name),
                );

                if($ReportSubPermissions) {
                    foreach($ReportSubPermissions as $SubPermission) {
                        if(array_key_exists($SubPermission->parent_id, $ReportsPermissions)) {
                            $ReportsPermissions[$SubPermission->parent_id]['children'][$SubPermission->name] = array(
                                'id' => $SubPermission->id,
                                'title' => $SubPermission->title,
                                'name' => $SubPermission->name,
                                'parent_id' => $SubPermission->parent_id,
                            );
                        }
                    }
                }
            }
        }

        $permissionsMapping = $this->preparePermissionsMapping();
        $dashboardPermissionsMapping = $this->prepareDashboardPermissionsMapping();
        $reportsPermissionsMapping = $this->prepareReportsPermissionsMapping();

        return array(
            'Permissions' => $Permissions,
            'DashboardPermissions' => $DashboardPermissions,
            'ReportsPermissions' => $ReportsPermissions,
            'permissionsMapping' => $permissionsMapping,
            'dashboardPermissionsMapping' => $dashboardPermissionsMapping,
            'reportsPermissionsMapping' => $reportsPermissionsMapping,
        );
    }

    /**
     * Update Role in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('roles_edit')) {
            return abort(401);
        }

        $validator = $this->verifyFields($request);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => 0,
                'message' => $validator->messages()->all(),
            ));
        }
        unset($request['DataTables_Table_0_length']);
        $role = Role::findOrFail($id);
        $role->update($request->except('permission'));
        $permissions = $request->input('permission') ? $request->input('permission') : [];
        $role->syncPermissions($permissions);

        flash('Record has been updated successfully.')->success()->important();

        return response()->json(array(
            'status' => 1,
            'message' => 'Record has been updated successfully.',
        ));
    }


    /**
     * Remove Role from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('roles_destroy')) {
            return abort(401);
        }

        $role = Role::findOrFail($id);

        if (!$role) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.roles.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (self::isChildExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.roles.index');
        }

        $role->delete();

        flash('Record has been deleted successfully.')->success()->important();

        return redirect()->route('admin.roles.index');
    }

    /**
     * Check if child records exist
     *
     * @param (int) $id
     * @param
     *
     * @return (boolean)
     */
    static public function isChildExists($id, $account_id)
    {
        if (DB::table('role_has_users')->where('role_id','=',$id)->count()) {
            return true;
        }
        return false;
    }


}
