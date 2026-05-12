<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class PermissionsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter_role = $request->input('role_id');
        $filter_status = $request->input('status');
        $filter_tier = $request->input('tier');
        $sort = $request->input('sort', 'newest');

        $query = User::with('role');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
                
                if (is_numeric($search)) {
                    $q->orWhere('user_id', $search);
                } else {
                    $q->orWhere('user_id', 'like', "%$search%");
                }
            });
        }

        if ($filter_role) $query->where('role_id', $filter_role);
        if ($filter_status) $query->where('status', $filter_status);
        if ($filter_tier) $query->where('member_tier', $filter_tier);

        switch ($sort) {
            case 'oldest':  $query->orderBy('user_id', 'ASC'); break;
            case 'name_az': $query->orderBy('full_name', 'ASC'); break;
            case 'name_za': $query->orderBy('full_name', 'DESC'); break;
            case 'id_asc':  $query->orderBy('user_id', 'ASC'); break;
            case 'id_desc': $query->orderBy('user_id', 'DESC'); break;
            default:        $query->orderBy('user_id', 'DESC'); break;
        }

        $users = $query->paginate(15)->withQueryString();
        $total_users = User::count();

        if ($request->input('export') == 'csv') {
            return $this->exportCsv($query);
        }

        if ($request->isMethod('post')) {
            $action = $request->input('action');
            
            if ($action == 'bulk_delete') {
                $ids = array_filter(explode(',', $request->input('ids')), fn($id) => $id != 1);
                if (!empty($ids)) { User::whereIn('user_id', $ids)->delete(); }
                return redirect()->route('admin.users.index', ['message' => 'bulk_deleted']);
            }

            if ($action == 'delete') {
                $del_id = $request->input('user_id');
                if ($del_id != 1) User::destroy($del_id);
                return redirect()->route('admin.users.index', ['message' => 'deleted']);
            }

            if (in_array($action, ['add','edit'])) {
                $data = [
                    'full_name'    => $request->input('full_name'),
                    'email'        => $request->input('email'),
                    'phone_number' => $request->input('phone_number'),
                    'role_id'      => $request->input('role_id'),
                    'status'       => $request->input('status'),
                    'member_tier'  => $request->input('member_tier'),
                ];
                if ($request->input('password')) $data['password_hash'] = Hash::make($request->input('password'));
                
                if ($action == 'add') {
                    User::create($data);
                    return redirect()->route('admin.users.index', ['message' => 'added']);
                } else {
                    User::where('user_id', $request->input('user_id'))->update($data);
                    return redirect()->route('admin.users.index', ['message' => 'updated']);
                }
            }

            if ($action == 'add_role') {
                Role::create(['name' => $request->input('name'), 'description' => $request->input('description'), 'color' => $request->input('color','indigo')]);
                return redirect()->route('admin.users.index', ['tab' => 'roles', 'message' => 'role_added']);
            }

            if ($action == 'edit_role') {
                Role::where('role_id', $request->input('role_id'))->update(['name' => $request->input('name'), 'description' => $request->input('description'), 'color' => $request->input('color','indigo')]);
                return redirect()->route('admin.users.index', ['tab' => 'roles', 'message' => 'role_updated']);
            }

            if ($action == 'delete_role' && !in_array($request->input('role_id'), [1,2,3])) {
                Role::destroy($request->input('role_id'));
                return redirect()->route('admin.users.index', ['tab' => 'roles', 'message' => 'role_deleted']);
            }
        }

        $roles      = Role::all();
        $active_tab = $request->input('tab', 'users');
        $msg_map    = [
            'deleted'      => ['Đã xóa tài khoản thành công!', 'success'],
            'bulk_deleted' => ['Đã xóa hàng loạt thành công!', 'success'],
            'added'        => ['Đã thêm tài khoản mới!', 'success'],
            'updated'      => ['Cập nhật tài khoản thành công!', 'success'],
            'role_added'   => ['Đã thêm vai trò mới!', 'success'],
            'role_updated' => ['Cập nhật vai trò thành công!', 'success'],
            'role_deleted' => ['Đã xóa vai trò!', 'danger'],
        ];
        [$message, $msg_type] = $msg_map[$request->input('message')] ?? ['', 'success'];

        $role_stats  = [];
        $status_stats = ['Active' => 0, 'Banned' => 0, 'Inactive' => 0];
        foreach ($roles as $r) {
            $role_stats[$r->role_id] = ['name' => $r->name, 'count' => User::where('role_id', $r->role_id)->count()];
        }
        foreach (['Active','Banned','Inactive'] as $s) {
            $status_stats[$s] = User::where('status', $s)->count();
        }
        $tier_stats = ['Vang' => User::where('member_tier','Vang')->count(), 'Bac' => User::where('member_tier','Bac')->count(), 'Dong' => User::where('member_tier','Dong')->count()];

        return view('admin.permissions.index', compact(
            'users', 'total_users', 'roles', 'active_tab', 'message', 'msg_type', 'role_stats', 'status_stats', 'tier_stats', 'search',
            'filter_role', 'filter_status', 'filter_tier', 'sort'
        ));
    }

    private function exportCsv($query)
    {
        $all = $query->get();
        $filename = "users_export_" . date('Ymd_His') . ".csv";
        
        $callback = function() use ($all) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['ID','Họ tên','Email','SĐT','Vai trò','Hạng','Trạng thái','Ngày tạo']);
            foreach ($all as $u) {
                fputcsv($output, [$u->user_id, $u->full_name, $u->email, $u->phone_number, $u->role->name ?? '', $u->member_tier, $u->status, $u->created_at]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ]);
    }
}
