
@extends('layouts.schoolnexa')

@section('title', 'Schools')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .school-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .school-page * { box-sizing: border-box; }

    .school-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .school-heading h1 {
        color: #123B5D;
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .school-heading p {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .school-btn {
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

    .school-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .school-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .school-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    .school-btn-light:hover { background: #F8FAFC; }

    .school-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .school-panel-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 16px;
    }

    .school-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .school-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 18px;
    }

    .school-stat-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .school-stat-value {
        color: #123B5D;
        font-size: 26px;
        font-weight: 800;
    }

    .school-filters {
        display: flex;
        align-items: end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .school-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        flex: 1;
        min-width: 180px;
    }

    .school-field label {
        color: #25313C;
        font-size: 12px;
        font-weight: 600;
    }

    .school-control {
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

    .school-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .school-table-wrap { overflow-x: auto; }

    .school-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .school-table th {
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

    .school-table td {
        padding: 15px 14px;
        border-bottom: 1px solid #E2E8F0;
        font-size: 13px;
        color: #25313C;
        vertical-align: middle;
    }

    .school-table tr:last-child td { border-bottom: 0; }

    .school-name {
        color: #123B5D;
        font-weight: 700;
    }

    .school-muted {
        color: #64748B;
        font-size: 12px;
        margin-top: 4px;
    }

    .school-badge {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .school-active {
        color: #15803D;
        background: #DCFCE7;
    }

    .school-inactive {
        color: #B91C1C;
        background: #FEE2E2;
    }

    .school-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .school-action {
        border: 1px solid #CBD5E1;
        border-radius: 7px;
        background: #fff;
        color: #176B87;
        padding: 7px 10px;
        font: 600 11px 'Inter', sans-serif;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .school-action:hover { background: #F1F5F9; }

    .school-action-danger { color: #DC2626; }

    .school-alert {
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 13px;
        font-weight: 500;
    }

    .school-alert-success {
        color: #166534;
        background: #DCFCE7;
        border: 1px solid #BBF7D0;
    }

    .school-empty {
        padding: 40px 20px !important;
        text-align: center;
        color: #64748B !important;
    }

    .school-pagination { margin-top: 18px; }

    @media (max-width: 640px) {
        .school-heading h1 { font-size: 21px; }
        .school-panel { padding: 15px; }
        .school-filters { align-items: stretch; }
    }
</style>

<div class="school-page">

    {{-- Page Header --}}
    <div class="school-header">
        <div class="school-heading">
            <h1>Schools</h1>
            <p>Manage schools registered under your organizations.</p>
        </div>

        @if(auth()->user()->hasPermission('SCHOOLS.CREATE'))
            <a href="{{ route('schools.create') }}" class="school-btn school-btn-primary">
                <span>＋</span> Add School
            </a>
        @endif
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="school-alert school-alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistics --}}
    <div class="school-stats">
        <div class="school-stat">
            <div class="school-stat-label">Total Schools</div>
            <div class="school-stat-value">{{ $schools->total() }}</div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="school-panel">
        <h3 class="school-panel-title">Search and Filter</h3>

        <form method="GET" action="{{ route('schools.index') }}" class="school-filters">

            <div class="school-field">
                <label for="search">Search schools</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="school-control"
                    placeholder="Search by school name, code or email..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="school-field">
                <label for="organization_id">Organization</label>
                <select name="organization_id" id="organization_id" class="school-control">
                    <option value="">All Organizations</option>

                    @foreach($organizations as $organization)
                        <option
                            value="{{ $organization->id }}"
                            @selected((string) request('organization_id') === (string) $organization->id)
                        >
                            {{ $organization->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="school-field" style="max-width: 170px;">
                <label for="status">Status</label>
                <select name="status" id="status" class="school-control">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>

            <button type="submit" class="school-btn school-btn-primary">
                Apply Filters
            </button>

            <a href="{{ route('schools.index') }}" class="school-btn school-btn-light">
                Reset
            </a>

        </form>
    </div>

    {{-- Schools Directory --}}
    <div class="school-panel">

        <h3 class="school-panel-title">School Directory</h3>

        <div class="school-table-wrap">
            <table class="school-table">

                <thead>
                    <tr>
                        <th>School</th>
                        <th>Organization</th>
                        <th>School Code</th>
                        <th>Contact</th>
                        <th>School Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($schools as $school)

                        <tr>

                            {{-- School --}}
                            <td>
                                <div class="school-name">
                                    {{ $school->name }}
                                </div>

                                <div class="school-muted">
                                    {{ $school->affiliation_number ?: 'No affiliation number' }}
                                </div>
                            </td>

                            {{-- Organization --}}
                            <td>
                                <div class="school-name">
                                    {{ $school->organization?->name ?? '—' }}
                                </div>
                            </td>

                            {{-- School Code --}}
                            <td>
                                {{ $school->code }}
                            </td>

                            {{-- Contact --}}
                            <td>
                                <div>{{ $school->email ?: '—' }}</div>
                                <div class="school-muted">
                                    {{ $school->phone ?: '—' }}
                                </div>
                            </td>

                            {{-- School Type --}}
                            <td>
                                {{ $school->school_type ?: '—' }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="school-badge {{ $school->status === 'active' ? 'school-active' : 'school-inactive' }}">
                                    {{ ucfirst($school->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="school-actions">

                                    @if(auth()->user()->hasPermission('SCHOOLS.UPDATE'))

                                        <a
                                            href="{{ route('schools.edit', $school) }}"
                                            class="school-action"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('schools.status', $school) }}"
                                            onsubmit="return confirm('Are you sure you want to change this school status?')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="school-action school-action-danger"
                                            >
                                                {{ $school->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="school-empty">
                                No schools found. Add your first school to get started.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        <div class="school-pagination">
            {{ $schools->withQueryString()->links() }}
        </div>

    </div>

</div>

@endsection
