@extends('layouts.schoolnexa')

@section('content')

<style>
    .gdn-page {
        color: #25313C;
        font-family: 'Inter', sans-serif;
    }

    .gdn-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .gdn-title {
        font-size: 23px;
        font-weight: 700;
        margin: 0 0 5px;
        color: #123B5D;
    }

    .gdn-subtitle {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .gdn-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .gdn-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .gdn-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .gdn-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .gdn-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 17px 19px;
    }

    .gdn-stat-label {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 9px;
    }

    .gdn-stat-value {
        font-size: 25px;
        font-weight: 700;
        color: #123B5D;
    }

    .gdn-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 19px;
        margin-bottom: 18px;
    }

    .gdn-panel-title {
        font-size: 15px;
        font-weight: 700;
        color: #25313C;
        margin: 0 0 15px;
    }

    .gdn-search {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .gdn-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        color: #25313C;
        outline: none;
        background: #fff;
    }

    .gdn-input:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23, 107, 135, .10);
    }

    .gdn-search-field {
        flex: 1;
        min-width: 220px;
    }

    .gdn-btn-outline {
        background: #fff;
        color: #25313C;
        border-color: #CBD5E1;
    }

    .gdn-table-wrap {
        overflow-x: auto;
    }

    .gdn-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .gdn-table th {
        text-align: left;
        padding: 12px 13px;
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        border-bottom: 1px solid #E2E8F0;
    }

    .gdn-table td {
        padding: 14px 13px;
        border-bottom: 1px solid #E2E8F0;
        font-size: 13px;
        vertical-align: middle;
    }

    .gdn-table tr:last-child td {
        border-bottom: none;
    }

    .gdn-name {
        font-weight: 650;
        color: #123B5D;
        margin-bottom: 4px;
    }

    .gdn-muted {
        color: #64748B;
        font-size: 12px;
    }

    .gdn-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 27px;
        height: 27px;
        padding: 0 8px;
        border-radius: 20px;
        background: #EAF3F8;
        color: #176B87;
        font-weight: 700;
        font-size: 12px;
    }

    .gdn-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .gdn-action {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 7px;
        border: 1px solid #CBD5E1;
        color: #123B5D;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        background: #fff;
    }

    .gdn-action:hover {
        background: #F1F5F9;
        color: #176B87;
    }

    .gdn-empty {
        text-align: center;
        padding: 38px 15px !important;
        color: #64748B;
    }

    .gdn-alert {
        padding: 12px 15px;
        border-radius: 9px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .gdn-alert-success {
        background: #F0FDF4;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .gdn-pagination {
        margin-top: 17px;
    }

    @media(max-width: 700px) {
        .gdn-stats {
            grid-template-columns: 1fr;
        }

        .gdn-panel {
            padding: 14px;
        }
    }
</style>

<div class="gdn-page">

    {{-- Header --}}
    <div class="gdn-header">
        <div>
            <h2 class="gdn-title">Parent Directory</h2>
            <p class="gdn-subtitle">
                Manage parent and guardian information and their linked students.
            </p>
        </div>

        @if(auth()->user()->hasPermission('GUARDIANS.CREATE'))
            <a href="{{ route('guardians.create') }}"
               class="gdn-btn gdn-btn-primary">
                <span>+</span> Add Parent
            </a>
        @endif
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="gdn-alert gdn-alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistics --}}
    <div class="gdn-stats">

        <div class="gdn-stat">
            <div class="gdn-stat-label">Total Parents / Guardians</div>
            <div class="gdn-stat-value">{{ $stats['total'] }}</div>
        </div>

        <div class="gdn-stat">
            <div class="gdn-stat-label">Linked With Students</div>
            <div class="gdn-stat-value">{{ $stats['linked'] }}</div>
        </div>

        <div class="gdn-stat">
            <div class="gdn-stat-label">Not Linked</div>
            <div class="gdn-stat-value">{{ $stats['unlinked'] }}</div>
        </div>

    </div>

    {{-- Search --}}
    <div class="gdn-panel">
        <h3 class="gdn-panel-title">Search Parents</h3>

        <form method="GET" action="{{ route('guardians.index') }}"
              class="gdn-search">

            <div class="gdn-search-field">
                <input
                    type="text"
                    name="search"
                    class="gdn-input"
                    placeholder="Search name, phone, email or student..."
                    value="{{ request('search') }}"
                >
            </div>

            <button type="submit"
                    class="gdn-btn gdn-btn-primary">
                Search
            </button>

            <a href="{{ route('guardians.index') }}"
               class="gdn-btn gdn-btn-outline">
                Reset
            </a>

        </form>
    </div>

    {{-- Parent Table --}}
    <div class="gdn-panel">

        <h3 class="gdn-panel-title">Parent Records</h3>

        <div class="gdn-table-wrap">
            <table class="gdn-table">

                <thead>
                    <tr>
                        <th>Parent / Guardian</th>
                        <th>Relationship</th>
                        <th>Contact</th>
                        <th>Linked Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($guardians as $guardian)

                        <tr>
                            <td>
                                <div class="gdn-name">
                                    {{ $guardian->full_name }}
                                </div>

                                <div class="gdn-muted">
                                    {{ $guardian->email ?: 'No email provided' }}
                                </div>
                            </td>

                            <td>
                                {{ $guardian->relationship }}
                            </td>

                            <td>
                                <div>{{ $guardian->phone }}</div>

                                @if($guardian->alternate_phone)
                                    <div class="gdn-muted">
                                        {{ $guardian->alternate_phone }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="gdn-count">
                                    {{ $guardian->students_count }}
                                </span>
                            </td>

                            <td>
                                <div class="gdn-actions">

                                    @if(auth()->user()->hasPermission('GUARDIANS.VIEW'))
                                        <a href="{{ route('guardians.show', $guardian) }}"
                                           class="gdn-action">
                                            View Profile
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('GUARDIANS.UPDATE'))
                                        <a href="{{ route('guardians.edit', $guardian) }}"
                                           class="gdn-action">
                                            Edit
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="gdn-empty">
                                <strong>No parent records found.</strong>
                                <div class="gdn-muted" style="margin-top:6px;">
                                    Add a parent or guardian to get started.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="gdn-pagination">
            {{ $guardians->links() }}
        </div>

    </div>

</div>

@endsection
