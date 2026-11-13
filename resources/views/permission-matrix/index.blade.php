@extends('layouts.schoolnexa')

@section('title', 'Permission Matrix')

@section('content')

@php
    $isSystem   = $selectedRole ? (bool) $selectedRole->is_system : false;
    $totalPerms = (int) collect($groups)->sum(fn ($p) => $p->count());
    $moduleCnt  = (int) collect($groups)->count();
    $assignedCt = count($assigned ?? []);

    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'roles'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'assigned' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'perms'    => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'modules'  => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'manage'   => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3M14 9l2 2"/>',
        'compare'  => '<path d="M7 4v16M17 4v16"/><path d="m3 8 4-4 4 4M13 16l4 4 4-4"/>',
        'save'     => '<path d="M5 3h11l3 3v15H5Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/>',
        'back'     => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['roles']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'roles'    => 'group',
        'assigned' => 'checkmark',
        'perms'    => 'lock',
        'modules'  => 'layers',
        'manage'   => 'key',
        'compare'  => 'scales',
        'save'     => 'save',
        'back'     => 'home',
        'hero'     => 'lock',
    ];
    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];

    /* Interactive 3D icon. sizes: xs | sm | md | lg */
    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($icon, $ramp, $slugs) {
        [$a, $b, $c] = $ramp[$tone];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';
        $img = $slug
            ? '<img class="pmi-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.pmi-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="pmi-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="pmi-3d '.$size.($slug ? '' : ' fb').'"><span class="pmi-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $quick = [
        ['Manage Roles',     'Create and edit roles',           'manage',  'sky',     route('role-builder.index')],
        ['Role Builder',     'Back to the role dashboard',      'back',    'teal',    route('role-builder.dashboard')],
        ['Compare Roles',    'Compare access side by side',     'compare', 'purple',  '#compareToolbar'],
        ['Browse Modules',   'Jump to the permission list',     'modules', 'warning', '#permList'],
    ];
@endphp

<div class="pm-page">
@include('permission-matrix._style')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .pmi-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .pmi-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .pmi-3d.sm{width:48px;height:48px}
    .pmi-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .pmi-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:pmiFloat 4.2s ease-in-out infinite}
    .pmi-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .pmi-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .pmi-ico{display:none}
    .pmi-3d.fb .pmi-3d-img{display:none}
    .pmi-3d.fb .pmi-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .pmi-3d.pop .pmi-3d-in{animation:pmiPop .55s cubic-bezier(.3,1.6,.5,1),pmiFloat 4.2s ease-in-out infinite .55s}
    @keyframes pmiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes pmiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.pmi-3d-in{animation:none!important}.pmi-3d{transition:none}}

    /* ================= BANNER ================= */
    .pmi-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .pmi-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .pmi-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .pmi-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .pmi-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .pmi-welcome-right{display:flex;align-items:center;gap:26px}
    .pmi-date{text-align:right;font-size:13px;color:#64748B}
    .pmi-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .pmi-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .pmi-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:18px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .pmi-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .pmi-kpi small{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748B}
    .pmi-kpi strong{display:block;font-size:28px;line-height:1.15;color:#123B5D;margin:2px 0}
    .pmi-kpi span.foot{font-size:11px;color:#94A3B8}

    /* ================= QUICK ACTIONS ================= */
    .pmi-box{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:20px;margin-bottom:16px}
    .pmi-box h3{margin:0;font-size:15px;color:#123B5D}
    .pmi-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}
    .pmi-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .pmi-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .pmi-quick strong{font-size:12px;font-weight:650;color:#123B5D}
    .pmi-quick small{font-size:10px;color:#64748B;line-height:1.4}

    /* ================= PERMISSION TOOLS ================= */
    .pmi-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    .pmi-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:230px;outline:none;background:#fff;font-family:inherit}
    .pmi-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .pmi-mini{border:1px solid #CBD5E1;background:#fff;border-radius:8px;padding:7px 12px;font-size:12px;font-weight:650;color:#123B5D;cursor:pointer;font-family:inherit}
    .pmi-mini:hover{border-color:#176B87;background:#F1F7FA}
    .pmi-mini.off{color:#DC2626}
    .pmi-mini.off:hover{background:#FEE2E2;border-color:#FCA5A5}
    .pmi-muted{font-size:12px;color:#64748B}
    .pmi-group-tools{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
    .pmi-chip{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;background:#DCFCE7;color:#166534}
    .pmi-chip.none{background:#FEE2E2;color:#DC2626}
    .pmi-chip.some{background:#FEF3C7;color:#B45309}
    .pmi-hide{display:none!important}
    .pmi-dirty{display:none;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#B45309}
    .pmi-dirty.show{display:inline-flex}
    .pmi-dirty:before{content:'';width:8px;height:8px;border-radius:50%;background:#D97706}
    .pmi-empty{display:none;padding:26px 15px;text-align:center;color:#64748B;border:1px dashed #CBD5E1;border-radius:12px;margin-bottom:14px}
    .pmi-yes{display:inline-flex;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;background:#DCFCE7;color:#166534}
    .pmi-no{display:inline-flex;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;background:#FEE2E2;color:#DC2626}

    @@media(max-width:1000px){.pmi-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.pmi-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.pmi-welcome h1{font-size:22px}.pmi-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="pmi-welcome" data-tilt>
    <div>
        <div class="pmi-eyebrow">{{ $greet }} <i></i> Access Control</div>
        <h1>Welcome Back, Admin!</h1>
        <p>Manage role access across SchoolNexa modules with a centralized permission matrix.</p>
    </div>
    <div class="pmi-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="pmi-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="pm-head">
    <div><h1 class="pm-title">Permission Matrix</h1><p class="pm-sub">Manage role access across SchoolNexa modules with a centralized permission matrix.</p></div>
</div>

@if(session('success'))<div class="pm-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="pm-alert pm-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

{{-- KPI CARDS --}}
<div class="pmi-kpis">
    <div class="pmi-kpi" data-tilt>
        {!! $tile('roles', 'sky') !!}
        <div><small>Total Roles</small><strong data-count="{{ $roles->count() }}">{{ $roles->count() }}</strong><span class="foot">Available to configure</span></div>
    </div>
    <div class="pmi-kpi" data-tilt>
        {!! $tile('assigned', 'success') !!}
        <div><small>Assigned Permissions</small><strong id="kpiAssigned">{{ $assignedCt }}</strong><span class="foot">{{ $selectedRole ? $selectedRole->name : 'No role selected' }}</span></div>
    </div>
    <div class="pmi-kpi" data-tilt>
        {!! $tile('perms', 'purple') !!}
        <div><small>Total Permissions</small><strong data-count="{{ $totalPerms }}">{{ $totalPerms }}</strong><span class="foot">Across all modules</span></div>
    </div>
    <div class="pmi-kpi" data-tilt>
        {!! $tile('modules', 'warning') !!}
        <div><small>Modules</small><strong data-count="{{ $moduleCnt }}">{{ $moduleCnt }}</strong><span class="foot">Permission groups</span></div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="pmi-box">
    <h3>Quick Actions</h3>
    <div class="pmi-quick-grid">
        @foreach($quick as [$label, $sub, $ic, $tone, $url])
            <a class="pmi-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $label }}</strong>
                <small>{{ $sub }}</small>
            </a>
        @endforeach
    </div>
</div>

{{-- ROLE SELECT / COMPARE (unchanged behaviour) --}}
<section class="pm-panel" id="compareToolbar">
    <form method="GET" action="{{ route('permission-matrix.index') }}" class="pm-toolbar">
        <div class="pm-field"><label>Select Role</label>
            <select name="role_id" onchange="this.form.submit()">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected((int)$selectedRoleId===(int)$role->id)>{{ $role->name }}{{ $role->is_system ? ' (System)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="pm-field"><label>Compare roles</label>
            <select name="compare_roles[]" multiple size="1" style="min-width:260px;height:40px">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(in_array($role->id,array_map('intval',(array)request('compare_roles',[]))))>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="pm-btn" name="compare" value="1">Compare Selected</button>
        <a class="pm-btn" href="{{ route('permission-matrix.index',['role_id'=>$selectedRoleId]) }}">Reset</a>
    </form>
</section>

@if($selectedRole)
<form method="POST" action="{{ route('permission-matrix.update') }}" id="pmForm">@csrf @method('PUT')
    <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

    <section class="pm-panel">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
            <div>
                <h3 style="margin-bottom:4px">{{ $selectedRole->name }}</h3>
                <div class="pm-sub">{{ $selectedRole->code }} · {{ ucfirst($selectedRole->scope_type) }} scope · {{ $selectedRole->is_system ? 'System role' : 'Custom role' }}</div>
            </div>
            <span class="pm-count"><span id="assignedCount">{{ $assignedCt }}</span> permissions currently assigned</span>
        </div>
    </section>

    @if($isSystem)<div class="pm-alert">This is a protected system role. Its permissions are visible but cannot be changed here.</div>@endif

    {{-- Search / filter / bulk tools --}}
    <section class="pm-panel" id="permList">
        <div class="pmi-tools">
            <input type="search" id="permSearch" class="pmi-input" placeholder="Search permission, code or description…" autocomplete="off">
            <select id="permState" class="pmi-input" style="min-width:150px">
                <option value="">All permissions</option>
                <option value="on">Enabled only</option>
                <option value="off">Disabled only</option>
            </select>
            @unless($isSystem)
                <button type="button" class="pmi-mini" id="enableVisible">Enable all shown</button>
                <button type="button" class="pmi-mini off" id="disableVisible">Disable all shown</button>
            @endunless
            <span class="pmi-muted" id="permShown"></span>
            <span class="pmi-dirty" id="dirtyFlag">Unsaved changes</span>
        </div>
    </section>

    <div class="pmi-empty" id="permNoMatch">No permissions match your search.</div>

    @foreach($groups as $module => $permissions)
        <section class="pm-group" data-module="{{ $module }}">
            <div class="pm-group-head">
                <strong>{{ $module }}</strong>
                <div class="pmi-group-tools">
                    <span class="pmi-chip" data-chip>{{ $permissions->whereIn('id',$assigned)->count() }} / {{ $permissions->count() }} enabled</span>
                    @unless($isSystem)
                        <button type="button" class="pmi-mini" data-bulk="on">All on</button>
                        <button type="button" class="pmi-mini off" data-bulk="off">All off</button>
                    @endunless
                </div>
            </div>
            @foreach($permissions as $permission)
                <div class="pm-perm" data-text="{{ strtolower($permission->name . ' ' . $permission->code . ' ' . ($permission->description ?? '')) }}">
                    <div><div class="pm-name">{{ $permission->name }}</div><div class="pm-code">{{ $permission->code }}</div></div>
                    <div class="pm-desc">{{ $permission->description ?: 'No description available.' }}</div>
                    <label class="pm-toggle"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array((int)$permission->id,$assigned,true)) @disabled($isSystem)> Enabled</label>
                </div>
            @endforeach
        </section>
    @endforeach

    @unless($isSystem)
        <div class="pm-sticky"><span class="pm-count">Changes apply to this role's permission assignments.</span><button type="submit" class="pm-btn primary">Save Permission Matrix</button></div>
    @endunless
</form>
@else
    <section class="pm-panel pm-empty">No roles found. Create a role in Role Builder before configuring its permissions.</section>
@endif

@if(request()->boolean('compare') && $matrixRoles->count())
<section class="pm-panel"><h3>Role Comparison</h3>
    <div style="overflow:auto">
        <table class="pm-compare">
            <thead><tr><th>PERMISSION</th>@foreach($matrixRoles as $compareRole)<th>{{ $compareRole->name }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach($groups as $module=>$permissions)
                <tr><th colspan="{{ $matrixRoles->count()+1 }}">{{ $module }}</th></tr>
                @foreach($permissions as $permission)
                    <tr>
                        <td><strong>{{ $permission->name }}</strong><div class="pm-code">{{ $permission->code }}</div></td>
                        @foreach($matrixRoles as $compareRole)
                            @php $has = !empty($matrix[$compareRole->id][$permission->id]); @endphp
                            <td><span class="{{ $has ? 'pmi-yes' : 'pmi-no' }}">{{ $has ? 'Granted' : 'Not granted' }}</span></td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.pmi-3d', card);
            if (!art) return;
            card.addEventListener('mousemove', function (e) {
                const r = card.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
                const cl = v => Math.max(-22, Math.min(22, v));
                art.style.setProperty('--ry', cl(x * 44) + 'deg');
                art.style.setProperty('--rx', cl(-y * 44) + 'deg');
                art.style.setProperty('--s', '1.14');
            });
            card.addEventListener('mouseleave', function () {
                art.style.setProperty('--ry', '0deg'); art.style.setProperty('--rx', '0deg'); art.style.setProperty('--s', '1');
            });
            card.addEventListener('click', function () { art.classList.remove('pop'); void art.offsetWidth; art.classList.add('pop'); });
        });
    }

    /* ---- Quick action that scrolls to a section on this page ---- */
    $$('[data-scroll]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            const s = $(a.getAttribute('data-scroll')); if (!s) return;
            s.scrollIntoView({ behavior: 'smooth', block: 'start' });
            s.style.transition = 'box-shadow .3s'; s.style.boxShadow = '0 0 0 3px rgba(23,107,135,.35)';
            setTimeout(function () { s.style.boxShadow = ''; }, 1200);
        });
    });

    /* ---- KPI count-up ---- */
    $$('[data-count]').forEach(function (el) {
        const target = parseInt(el.getAttribute('data-count'), 10) || 0; let start = null;
        if (target === 0) return;
        function step(ts) {
            if (!start) start = ts; const p = Math.min((ts - start) / 700, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });

    /* ---- Permission matrix interactivity ---- */
    const form = $('#pmForm');
    if (!form) return;

    const boxes  = $$('input[name="permissions[]"]', form);
    const groups = $$('.pm-group', form);
    const initial = boxes.map(b => b.checked);
    const isLocked = boxes.length > 0 && boxes.every(b => b.disabled);
    let submitting = false;

    function updateCounts() {
        let assigned = 0;
        groups.forEach(function (g) {
            const gb = $$('input[name="permissions[]"]', g);
            const on = gb.filter(b => b.checked).length;
            assigned += on;
            const chip = $('[data-chip]', g);
            if (chip) {
                chip.textContent = on + ' / ' + gb.length + ' enabled';
                chip.classList.toggle('none', on === 0);
                chip.classList.toggle('some', on > 0 && on < gb.length);
            }
        });
        $('#assignedCount').textContent = assigned;
        const k = $('#kpiAssigned'); if (k) k.textContent = assigned;
        const dirty = boxes.some((b, i) => b.checked !== initial[i]);
        $('#dirtyFlag').classList.toggle('show', dirty);
        return dirty;
    }

    function rowVisible(row) { return !row.classList.contains('pmi-hide'); }

    function applyFilter() {
        const q = $('#permSearch').value.trim().toLowerCase(), st = $('#permState').value;
        let shown = 0, total = 0;
        groups.forEach(function (g) {
            let groupShown = 0;
            $$('.pm-perm', g).forEach(function (row) {
                const cb = $('input[type="checkbox"]', row);
                const ok = (!q || row.dataset.text.includes(q))
                        && (!st || (st === 'on' ? cb.checked : !cb.checked));
                row.classList.toggle('pmi-hide', !ok);
                total++; if (ok) { shown++; groupShown++; }
            });
            g.classList.toggle('pmi-hide', groupShown === 0);
        });
        $('#permShown').textContent = shown + ' of ' + total + ' shown';
        $('#permNoMatch').style.display = shown === 0 ? 'block' : 'none';
    }

    $('#permSearch').addEventListener('input', applyFilter);
    $('#permState').addEventListener('change', applyFilter);
    boxes.forEach(b => b.addEventListener('change', function () { updateCounts(); if ($('#permState').value) applyFilter(); }));

    function setRows(rows, value) {
        rows.forEach(function (row) {
            if (!rowVisible(row)) return;
            const cb = $('input[type="checkbox"]', row);
            if (cb && !cb.disabled) cb.checked = value;
        });
        updateCounts(); applyFilter();
    }

    $$('[data-bulk]', form).forEach(function (btn) {
        btn.addEventListener('click', function () {
            setRows($$('.pm-perm', btn.closest('.pm-group')), btn.getAttribute('data-bulk') === 'on');
        });
    });
    const en = $('#enableVisible'), di = $('#disableVisible');
    if (en) en.addEventListener('click', () => setRows($$('.pm-perm', form), true));
    if (di) di.addEventListener('click', () => setRows($$('.pm-perm', form), false));

    form.addEventListener('submit', function () { submitting = true; });
    window.addEventListener('beforeunload', function (e) {
        if (!isLocked && !submitting && updateCounts()) { e.preventDefault(); e.returnValue = ''; }
    });

    updateCounts(); applyFilter();
});
</script>
@endsection