<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleBuilderController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    private function audit(string $action, string $type, int $id, array $old = [], array $new = []): void
    {
        if (!DB::getSchemaBuilder()->hasTable('audit_logs')) return;

        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'school_id' => $this->schoolId(),
            'action' => $action,
            'entity_type' => $type,
            'entity_id' => $id,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1000),
            'request_id' => request()->attributes->get('request_id'),
            'created_at' => now(),
        ]);
    }

    public function dashboard()
    {
        $roles = DB::table('roles');
        $totalRoles = (clone $roles)->count();
        $activeRoles = (clone $roles)->where('is_active', 1)->count();
        $systemRoles = (clone $roles)->where('is_system', 1)->count();
        $customRoles = (clone $roles)->where('is_system', 0)->count();
        $assignedUsers = DB::table('user_role_assignments')->where('is_active', 1)->distinct('user_id')->count('user_id');

        $recentRoles = DB::table('roles')->orderByDesc('updated_at')->limit(6)->get();
        $roleUsage = DB::table('roles as r')
            ->leftJoin('user_role_assignments as ura', function ($join) {
                $join->on('ura.role_id', '=', 'r.id')->where('ura.is_active', '=', 1);
            })
            ->select('r.id', 'r.name', 'r.code', 'r.scope_type', DB::raw('COUNT(DISTINCT ura.user_id) as assigned_count'))
            ->groupBy('r.id', 'r.name', 'r.code', 'r.scope_type')
            ->orderByDesc('assigned_count')->limit(6)->get();

        return view('role-builder.dashboard', compact(
            'totalRoles', 'activeRoles', 'systemRoles', 'customRoles', 'assignedUsers', 'recentRoles', 'roleUsage'
        ));
    }

    public function index(Request $request)
    {
        $query = DB::table('roles');
        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%");
            });
        }
        if ($request->filled('scope')) $query->where('scope_type', $request->input('scope'));
        if ($request->filled('status')) $query->where('is_active', (int) ($request->input('status') === 'active'));
        $roles = $query->orderBy('name')->paginate(15)->withQueryString();
        $roleCounts = DB::table('role_permissions')->select('role_id', DB::raw('COUNT(*) as total'))->groupBy('role_id')->pluck('total', 'role_id');
        $userCounts = DB::table('user_role_assignments')->where('is_active', 1)->select('role_id', DB::raw('COUNT(DISTINCT user_id) as total'))->groupBy('role_id')->pluck('total', 'role_id');

        return view('role-builder.index', compact('roles', 'roleCounts', 'userCounts'));
    }

    public function create()
    {
        return view('role-builder.create', ['role' => null, 'permissions' => $this->permissionGroups()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[A-Z][A-Z0-9_.-]*$/', Rule::unique('roles', 'code')],
            'scope_type' => ['required', Rule::in(['organization', 'school', 'campus'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $id = DB::transaction(function () use ($data) {
            $id = DB::table('roles')->insertGetId([
                'name' => $data['name'], 'code' => $data['code'], 'scope_type' => $data['scope_type'],
                'description' => $data['description'] ?? null, 'is_system' => 0, 'is_active' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->syncPermissions($id, $data['permissions'] ?? []);
            $this->audit('role.created', 'role', $id, [], ['name' => $data['name'], 'code' => $data['code']]);
            return $id;
        });

        return redirect()->route('role-builder.show', $id)->with('success', 'Role created successfully.');
    }

    public function show(int $id)
    {
        $role = DB::table('roles')->where('id', $id)->firstOrFail();
        $permissions = DB::table('permissions as p')
            ->join('role_permissions as rp', 'rp.permission_id', '=', 'p.id')
            ->where('rp.role_id', $id)->orderBy('p.module')->orderBy('p.action')->get();
        $assignments = DB::table('user_role_assignments as ura')
            ->join('users as u', 'u.id', '=', 'ura.user_id')
            ->where('ura.role_id', $id)
            ->select('ura.*', 'u.name as user_name', 'u.email')
            ->orderByDesc('ura.created_at')->limit(50)->get();
        $permissionCount = $permissions->count();
        $activeAssignments = $assignments->where('is_active', 1)->count();

        return view('role-builder.show', compact('role', 'permissions', 'assignments', 'permissionCount', 'activeAssignments'));
    }

    public function edit(int $id)
    {
        $role = DB::table('roles')->where('id', $id)->firstOrFail();
        abort_if((bool) $role->is_system, 403, 'System roles cannot be edited here.');
        $selected = DB::table('role_permissions')->where('role_id', $id)->pluck('permission_id')->all();
        return view('role-builder.create', ['role' => $role, 'permissions' => $this->permissionGroups(), 'selected' => $selected]);
    }

    public function update(Request $request, int $id)
    {
        $role = DB::table('roles')->where('id', $id)->firstOrFail();
        abort_if((bool) $role->is_system, 403, 'System roles cannot be edited here.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[A-Z][A-Z0-9_.-]*$/', Rule::unique('roles', 'code')->ignore($id)],
            'scope_type' => ['required', Rule::in(['organization', 'school', 'campus'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::transaction(function () use ($data, $id, $role) {
            DB::table('roles')->where('id', $id)->update([
                'name' => $data['name'], 'code' => $data['code'], 'scope_type' => $data['scope_type'],
                'description' => $data['description'] ?? null, 'updated_at' => now(),
            ]);
            $this->syncPermissions($id, $data['permissions'] ?? []);
            $this->audit('role.updated', 'role', $id, (array) $role, $data);
        });

        return redirect()->route('role-builder.show', $id)->with('success', 'Role updated successfully.');
    }

    public function toggle(int $id)
    {
        $role = DB::table('roles')->where('id', $id)->firstOrFail();
        abort_if((bool) $role->is_system, 403, 'System roles cannot be deactivated here.');
        $next = !(bool) $role->is_active;
        DB::table('roles')->where('id', $id)->update(['is_active' => $next, 'updated_at' => now()]);
        $this->audit($next ? 'role.activated' : 'role.deactivated', 'role', $id, ['is_active' => (bool) $role->is_active], ['is_active' => $next]);
        return back()->with('success', 'Role status updated.');
    }

    public function assign(Request $request, int $id)
    {
        $role = DB::table('roles')->where('id', $id)->where('is_active', 1)->firstOrFail();
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'organization_id' => ['nullable', 'integer'],
            'school_id' => ['nullable', 'integer'],
            'campus_id' => ['nullable', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $scopeColumn = $role->scope_type === 'organization' ? 'organization_id' : ($role->scope_type === 'campus' ? 'campus_id' : 'school_id');
        if (empty($data[$scopeColumn])) {
            return back()->withErrors([$scopeColumn => 'A scope ID is required for this role type.'])->withInput();
        }

        DB::table('user_role_assignments')->insert([
            'user_id' => $data['user_id'], 'role_id' => $id,
            'organization_id' => $data['organization_id'] ?? null,
            'school_id' => $data['school_id'] ?? null, 'campus_id' => $data['campus_id'] ?? null,
            'assigned_by' => auth()->id(), 'starts_at' => $data['starts_at'] ?? now(),
            'expires_at' => $data['expires_at'] ?? null, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->audit('role.assigned', 'role', $id, [], ['user_id' => $data['user_id'], 'scope' => $scopeColumn]);
        return back()->with('success', 'Role assigned successfully.');
    }

    public function removeAssignment(int $id, int $assignment)
    {
        $row = DB::table('user_role_assignments')->where('id', $assignment)->where('role_id', $id)->firstOrFail();
        DB::table('user_role_assignments')->where('id', $assignment)->update(['is_active' => 0, 'updated_at' => now()]);
        $this->audit('role.assignment_removed', 'role', $id, ['assignment_id' => $assignment], ['user_id' => $row->user_id]);
        return back()->with('success', 'Role assignment deactivated.');
    }

    public function compare(Request $request)
    {
        $roles = DB::table('roles')->where('is_active', 1)->orderBy('name')->get();
        $comparison = collect();
        $selectedIds = array_filter(array_map('intval', (array) $request->input('roles', [])));
        if (count($selectedIds) >= 2 && count($selectedIds) <= 4) {
            $comparison = DB::table('permissions as p')
                ->leftJoin('role_permissions as rp', function ($join) use ($selectedIds) {
                    $join->on('rp.permission_id', '=', 'p.id')->whereIn('rp.role_id', $selectedIds);
                })
                ->select('p.id', 'p.module', 'p.name', 'p.code', 'p.action', 'rp.role_id')
                ->orderBy('p.module')->orderBy('p.name')->get()
                ->groupBy('id');
        }
        return view('role-builder.compare', compact('roles', 'comparison', 'selectedIds'));
    }

    private function permissionGroups()
    {
        return DB::table('permissions')->orderBy('module')->orderBy('name')->get()->groupBy(fn ($p) => $p->module ?: 'General');
    }

    private function syncPermissions(int $roleId, array $permissionIds): void
    {
        DB::table('role_permissions')->where('role_id', $roleId)->delete();
        $rows = collect($permissionIds)->unique()->map(fn ($permissionId) => [
            'role_id' => $roleId, 'permission_id' => $permissionId,
            'created_at' => now(), 'updated_at' => now(),
        ])->all();
        if ($rows) DB::table('role_permissions')->insert($rows);
    }
}
