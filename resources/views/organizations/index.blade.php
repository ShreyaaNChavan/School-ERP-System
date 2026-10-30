
@extends('layouts.schoolnexa')

@section('title', 'Organizations')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .org-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .org-page * { box-sizing: border-box; }

    .org-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .org-heading h1 {
        color: #123B5D;
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .org-heading p {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .org-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 9px;
        font: 600 13px 'Inter', sans-serif;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: .2s;
    }

    .org-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .org-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .org-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    .org-btn-light:hover { background: #F8FAFC; }

    .org-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .org-panel-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 16px;
    }

    .org-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .org-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 18px;
    }

    .org-stat-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .org-stat-value {
        color: #123B5D;
        font-size: 26px;
        font-weight: 800;
    }

    .org-filters {
        display: flex;
        align-items: end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .org-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        flex: 1;
        min-width: 180px;
    }

    .org-field label {
        color: #25313C;
        font-size: 12px;
        font-weight: 600;
    }

    .org-control {
        width: 100%;
        min-height: 40px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        padding: 9px 11px;
        color: #25313C;
        background: #fff;
        font: 400 13px 'Inter', sans-serif;
        outline: none;
    }

    .org-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .org-table-wrap { overflow-x: auto; }

    .org-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 750px;
    }

    .org-table th {
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 13px 14px;
        border-bottom: 1px solid #CBD5E1;
    }

    .org-table td {
        padding: 15px 14px;
        border-bottom: 1px solid #E2E8F0;
        font-size: 13px;
        color: #25313C;
        vertical-align: middle;
    }

    .org-table tr:last-child td { border-bottom: 0; }

    .org-name {
        color: #123B5D;
        font-weight: 700;
    }

    .org-muted {
        color: #64748B;
        font-size: 12px;
        margin-top: 4px;
    }

    .org-badge {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .org-active {
        color: #15803D;
        background: #DCFCE7;
    }

    .org-inactive {
        color: #B91C1C;
        background: #FEE2E2;
    }

    .org-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .org-action {
        border: 1px solid #CBD5E1;
        border-radius: 7px;
        background: #fff;
        color: #176B87;
        padding: 7px 10px;
        font: 600 11px 'Inter', sans-serif;
        text-decoration: none;
        cursor: pointer;
    }

    .org-action:hover { background: #F1F5F9; }

    .org-action-danger { color: #DC2626; }

    .org-alert {
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 13px;
        font-weight: 500;
    }

    .org-alert-success {
        color: #166534;
        background: #DCFCE7;
        border: 1px solid #BBF7D0;
    }

    .org-empty {
        padding: 40px 20px !important;
        text-align: center;
        color: #64748B !important;
    }

    .org-pagination {
        margin-top: 18px;
    }

    @media (max-width: 640px) {
        .org-heading h1 { font-size: 21px; }
        .org-panel { padding: 15px; }
        .org-filters { align-items: stretch; }
    }
</style>

<div class="org-page">

    {{-- Page Header --}}
    <div class="org-header">
        <div class="org-heading">
            <h1>Organizations</h1>
            <p>Manage and monitor all registered organizations.</p>
        </div>

        @if(auth()->user()->hasPermission('ORGANIZATIONS.CREATE'))
            <a href="{{ route('organizations.create') }}" class="org-btn org-btn-primary">
                <span>＋</span> Add Organization
            </a>
        @endif
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="org-alert org-alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistics --}}
    <div class="org-stats">
        <div class="org-stat">
            <div class="org-stat-label">Total Organizations</div>
            <div class="org-stat-value">{{ $organizations->total() }}</div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="org-panel">
        <h3 class="org-panel-title">Search and Filter</h3>

        <form method="GET" action="{{ route('organizations.index') }}" class="org-filters">

            <div class="org-field">
                <label for="search">Search organizations</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="org-control"
                    placeholder="Search by name, email or phone..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="org-field" style="max-width: 200px;">
                <label for="status">Status</label>
                <select name="status" id="status" class="org-control">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>

            <button type="submit" class="org-btn org-btn-primary">
                Apply Filters
            </button>

            <a href="{{ route('organizations.index') }}" class="org-btn org-btn-light">
                Reset
            </a>

        </form>
    </div>

    {{-- Organizations Table --}}
    <div class="org-panel">
        <h3 class="org-panel-title">Organization Directory</h3>

        <div class="org-table-wrap">
            <table class="org-table">

                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Schools</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($organizations as $organization)

                        <tr>
                            {{-- Organization --}}
                            <td>
                                <div class="org-name">
                                    {{ $organization->name }}
                                </div>

                                <div class="org-muted">
                                    {{ $organization->legal_name ?: 'Legal name not provided' }}
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td>
                                <div>
                                    {{ $organization->primary_email ?: '—' }}
                                </div>

                                <div class="org-muted">
                                    {{ $organization->primary_phone ?: '—' }}
                                </div>
                            </td>

                            {{-- Location --}}
                            <td>
                                {{ collect([
                                    $organization->city,
                                    $organization->state,
                                    $organization->country_code
                                ])->filter()->join(', ') ?: '—' }}
                            </td>

                            {{-- Schools Count --}}
                            <td>
                                {{ $organization->schools_count }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="org-badge {{ $organization->status === 'active' ? 'org-active' : 'org-inactive' }}">
                                    {{ ucfirst($organization->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="org-actions">

                                    @if(auth()->user()->hasPermission('ORGANIZATIONS.UPDATE'))

                                        <a
                                            href="{{ route('organizations.edit', $organization) }}"
                                            class="org-action"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('organizations.status', $organization) }}"
                                            onsubmit="return confirm('Are you sure you want to change this organization status?')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="org-action org-action-danger"
                                            >
                                                {{ $organization->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="org-empty">
                                No organizations found. Add your first organization to get started.
                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        <div class="org-pagination">
            {{ $organizations->withQueryString()->links() }}
        </div>

    </div>

</div>
@endsection
