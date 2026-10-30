
@extends('layouts.schoolnexa')

@section('title', 'Campuses')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .campus-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .campus-page * { box-sizing: border-box; }

    .campus-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .campus-heading h1 {
        color: #123B5D;
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .campus-heading p {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .campus-btn {
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

    .campus-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .campus-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .campus-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    .campus-btn-light:hover { background: #F8FAFC; }

    .campus-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .campus-panel-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 16px;
    }

    .campus-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .campus-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 18px;
    }

    .campus-stat-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .campus-stat-value {
        color: #123B5D;
        font-size: 26px;
        font-weight: 800;
    }

    .campus-filters {
        display: flex;
        align-items: end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .campus-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        flex: 1;
        min-width: 180px;
    }

    .campus-field label {
        color: #25313C;
        font-size: 12px;
        font-weight: 600;
    }

    .campus-control {
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

    .campus-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .campus-table-wrap { overflow-x: auto; }

    .campus-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .campus-table th {
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

    .campus-table td {
        padding: 15px 14px;
        border-bottom: 1px solid #E2E8F0;
        font-size: 13px;
        color: #25313C;
        vertical-align: middle;
    }

    .campus-table tr:last-child td { border-bottom: 0; }

    .campus-name {
        color: #123B5D;
        font-weight: 700;
    }

    .campus-muted {
        color: #64748B;
        font-size: 12px;
        margin-top: 4px;
    }

    .campus-badge {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .campus-active {
        color: #15803D;
        background: #DCFCE7;
    }

    .campus-inactive {
        color: #B91C1C;
        background: #FEE2E2;
    }

    .campus-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .campus-action {
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

    .campus-action:hover { background: #F1F5F9; }

    .campus-action-danger { color: #DC2626; }

    .campus-alert {
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 13px;
        font-weight: 500;
    }

    .campus-alert-success {
        color: #166534;
        background: #DCFCE7;
        border: 1px solid #BBF7D0;
    }

    .campus-empty {
        padding: 40px 20px !important;
        text-align: center;
        color: #64748B !important;
    }

    .campus-pagination { margin-top: 18px; }

    @media (max-width: 640px) {
        .campus-heading h1 { font-size: 21px; }
        .campus-panel { padding: 15px; }
        .campus-filters { align-items: stretch; }
    }
</style>

<div class="campus-page">

    {{-- Page Header --}}
    <div class="campus-header">

        <div class="campus-heading">
            <h1>Campuses</h1>
            <p>Manage multiple campuses under your schools.</p>
        </div>

        @if(auth()->user()->hasPermission('CAMPUSES.CREATE'))
            <a href="{{ route('campuses.create') }}" class="campus-btn campus-btn-primary">
                <span>＋</span> Add Campus
            </a>
        @endif

    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="campus-alert campus-alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistics --}}
    <div class="campus-stats">
        <div class="campus-stat">
            <div class="campus-stat-label">Total Campuses</div>
            <div class="campus-stat-value">{{ $campuses->total() }}</div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="campus-panel">

        <h3 class="campus-panel-title">Search and Filter</h3>

        <form method="GET" action="{{ route('campuses.index') }}" class="campus-filters">

            <div class="campus-field">
                <label for="search">Search campuses</label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    class="campus-control"
                    placeholder="Search by campus, code or city..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="campus-field">
                <label for="school_id">School</label>

                <select name="school_id" id="school_id" class="campus-control">
                    <option value="">All Schools</option>

                    @foreach($schools as $school)
                        <option
                            value="{{ $school->id }}"
                            @selected((string) request('school_id') === (string) $school->id)
                        >
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="campus-field" style="max-width: 170px;">
                <label for="status">Status</label>

                <select name="status" id="status" class="campus-control">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>

            <button type="submit" class="campus-btn campus-btn-primary">
                Apply Filters
            </button>

            <a href="{{ route('campuses.index') }}" class="campus-btn campus-btn-light">
                Reset
            </a>

        </form>

    </div>

    {{-- Campus Directory --}}
    <div class="campus-panel">

        <h3 class="campus-panel-title">Campus Directory</h3>

        <div class="campus-table-wrap">

            <table class="campus-table">

                <thead>
                    <tr>
                        <th>Campus</th>
                        <th>School</th>
                        <th>Organization</th>
                        <th>Campus Code</th>
                        <th>Location</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($campuses as $campus)

                        <tr>

                            {{-- Campus --}}
                            <td>
                                <div class="campus-name">
                                    {{ $campus->name }}
                                </div>

                                <div class="campus-muted">
                                    {{ $campus->address_line_1 ?: 'Address not provided' }}
                                </div>
                            </td>

                            {{-- School --}}
                            <td>
                                <div class="campus-name">
                                    {{ $campus->school?->name ?? '—' }}
                                </div>

                                <div class="campus-muted">
                                    {{ $campus->school?->code ?? '' }}
                                </div>
                            </td>

                            {{-- Organization --}}
                            <td>
                                {{ $campus->school?->organization?->name ?? '—' }}
                            </td>

                            {{-- Campus Code --}}
                            <td>{{ $campus->code }}</td>

                            {{-- Location --}}
                            <td>
                                {{ collect([
                                    $campus->city,
                                    $campus->state,
                                    $campus->postal_code
                                ])->filter()->join(', ') ?: '—' }}
                            </td>

                            {{-- Phone --}}
                            <td>{{ $campus->phone ?: '—' }}</td>

                            {{-- Status --}}
                            <td>
                                <span class="campus-badge {{ $campus->status === 'active' ? 'campus-active' : 'campus-inactive' }}">
                                    {{ ucfirst($campus->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="campus-actions">

                                    @if(auth()->user()->hasPermission('CAMPUSES.UPDATE'))

                                        <a
                                            href="{{ route('campuses.edit', $campus) }}"
                                            class="campus-action"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('campuses.status', $campus) }}"
                                            onsubmit="return confirm('Are you sure you want to change this campus status?')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="campus-action campus-action-danger"
                                            >
                                                {{ $campus->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="campus-empty">
                                No campuses found. Add your first campus to get started.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        <div class="campus-pagination">
            {{ $campuses->withQueryString()->links() }}
        </div>

    </div>

</div>

@endsection

