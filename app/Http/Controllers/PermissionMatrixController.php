<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionMatrixController extends Controller
{
    public function index(Request $request)
    {
        $roles = DB::table('roles')->orderBy('name')->get();
        $permissions = DB::table('permissions')->orderBy('module')->orderBy('name')->get();
        $selectedRoleId = $request->integer('role_id') ?: ($roles->first()->id ?? null);
        $selectedRole = $selectedRoleId ? $roles->firstWhere('id', $selectedRoleId) : null;

        $assigned = $selectedRole
            ? DB::table('role_permissions')->where('role_id', $selectedRole->id)->pluck('permission_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $groups = $permissions->groupBy(fn ($permission) => $permission->module ?: 'General');
        $matrixRoles = collect();
        $matrix = [];
        if ($request->boolean('compare')) {
            $matrixRoles = $roles->whereIn('id', array_filter(array_map('intval', (array) $request->input('compare_roles', []))))->take(4)->values();
            if ($matrixRoles->count()) {
                $matrixRows = DB::table('role_permissions')->whereIn('role_id', $matrixRoles->pluck('id'))->get();
                foreach ($matrixRows as $row) $matrix[$row->role_id][$row->permission_id] = true;
            }
        }

        return view('permission-matrix.index', compact('roles', 'groups', 'selectedRole', 'selectedRoleId', 'assigned', 'matrixRoles', 'matrix'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = DB::table('roles')->where('id', $data['role_id'])->firstOrFail();
        if ($role->is_system) {
            return back()->withErrors(['role_id' => 'System role permissions are protected from editing here.'])->withInput();
        }

        $permissionIds = collect($data['permissions'] ?? [])->unique()->values();
        DB::transaction(function () use ($role, $permissionIds) {
            DB::table('role_permissions')->where('role_id', $role->id)->delete();
            if ($permissionIds->isNotEmpty()) {
                $now = now();
                $rows = $permissionIds->map(fn ($permissionId) => [
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();
                DB::table('role_permissions')->insert($rows);
            }

            if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'user_id' => auth()->id(),
                    'school_id' => session('school_id', 1),
                    'action' => 'permission_matrix.updated',
                    'entity_type' => 'role',
                    'entity_id' => $role->id,
                    'old_values' => null,
                    'new_values' => json_encode(['permission_ids' => $permissionIds->all()]),
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 1000),
                    'request_id' => request()->attributes->get('request_id'),
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('permission-matrix.index', ['role_id' => $role->id])
            ->with('success', 'Permission matrix updated for '.$role->name.'.');
    }
}
