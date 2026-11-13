@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page">

    <style>
        /* =====================================
           CERTIFICATE HISTORY
        ===================================== */

        .cert-wrap {
            max-width: 1250px;
            margin: 0 auto;
        }

        .cert-wrap > * + * {
            margin-top: 22px;
        }

        /* Header */
        .cert-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .cert-heading {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .cert-heading-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #EAF4F8;
            color: #176B87;
            border-radius: 13px;
            font-size: 21px;
        }

        .cert-head .sn-title {
            margin: 0;
            font-size: 24px;
            font-weight: 750;
            color: #123B5D;
            letter-spacing: -.5px;
        }

        .cert-head .sn-sub {
            margin-top: 5px;
            color: #64748B;
            font-size: 13px;
        }

        .cert-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cert-header-actions .sn-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
            transition: background-color .15s, border-color .15s;
        }

        /* Dashboard: white, light border, dark bold text */
        .cert-header-actions .sn-btn-outline {
            background: #fff;
            border: 1px solid #CBD5E1;
            color: #25313C;
        }

        .cert-header-actions .sn-btn-outline:hover {
            background: rgba(23, 107, 135, .06);
            border-color: #176B87;
        }

        .cert-header-actions .sn-btn-outline:focus-visible {
            outline: none;
            border-color: #176B87;
            box-shadow: 0 0 0 3px rgba(23, 107, 135, .10);
        }

        /* Summary cards */
        .cert-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .cert-stat {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 19px;
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .025);
        }

        .cert-stat-icon {
            width: 43px;
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            font-size: 18px;
            flex-shrink: 0;
        }

        .cert-stat-icon.blue {
            color: #176B87;
            background: #EAF4F8;
        }

        .cert-stat-icon.purple {
            color: #5B3FA3;
            background: #F0EDFF;
        }

        .cert-stat-icon.green {
            color: #15803D;
            background: #ECFDF3;
        }

        .cert-stat-label {
            color: #64748B;
            font-size: 11px;
            font-weight: 600;
        }

        .cert-stat-value {
            margin-top: 4px;
            color: #123B5D;
            font-size: 23px;
            font-weight: 750;
            line-height: 1.2;
        }

        /* Cards */
        .cert-card {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 13px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
        }

        /* Filter section */
        .cert-filter-head {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 19px 22px;
            border-bottom: 1px solid #E8EDF3;
            background: #FCFDFE;
        }

        .cert-filter-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #176B87;
            background: #EAF4F8;
            border-radius: 9px;
        }

        .cert-filter-head h2 {
            margin: 0;
            color: #123B5D;
            font-size: 14px;
            font-weight: 750;
        }

        .cert-filter-head p {
            margin: 4px 0 0;
            color: #64748B;
            font-size: 11px;
        }

        .cert-filter-body {
            padding: 20px 22px;
        }

        .cert-filter {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(190px, 240px) auto;
            gap: 16px;
            align-items: end;
        }

        .cert-field {
            min-width: 0;
        }

        .cert-field label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .cert-input-wrap {
            position: relative;
        }

        .cert-input-wrap > i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 13px;
            pointer-events: none;
        }

        .cert-field input,
        .cert-field select {
            width: 100%;
            height: 43px;
            box-sizing: border-box;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background: #fff;
            color: #25313C;
            font-family: Inter, sans-serif;
            font-size: 12px;
            transition: border-color .2s, box-shadow .2s;
        }

        .cert-field input {
            padding: 0 13px 0 37px;
        }

        .cert-field select {
            padding: 0 32px 0 12px;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .cert-field input:focus,
        .cert-field select:focus {
            outline: none;
            border-color: #176B87;
            box-shadow: 0 0 0 3px rgba(23, 107, 135, .10);
        }

        .cert-field input::placeholder {
            color: #94A3B8;
        }

        .cert-filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cert-filter-actions .sn-btn {
            height: 43px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 15px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            text-decoration: none;
            box-sizing: border-box;
            transition: all .2s ease;
        }

        /* Table section header */
        .cert-table-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 21px 22px;
        }

        .cert-table-title {
            margin: 0;
            color: #123B5D;
            font-size: 15px;
            font-weight: 750;
        }

        .cert-table-sub {
            margin-top: 5px;
            color: #64748B;
            font-size: 11px;
        }

        .cert-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 11px;
            border: 1px solid #BAE6FD;
            border-radius: 20px;
            background: #F0F9FF;
            color: #0369A1;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Table */
        .cert-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .cert-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
        }

        .cert-table thead {
            background: #F8FAFC;
        }

        .cert-table th {
            padding: 13px 19px;
            border-top: 1px solid #E8EDF3;
            border-bottom: 1px solid #E8EDF3;
            color: #64748B;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .55px;
        }

        .cert-table td {
            padding: 16px 19px;
            border-bottom: 1px solid #F1F5F9;
            color: #475569;
            font-size: 12px;
            vertical-align: middle;
        }

        .cert-table tbody tr {
            transition: background .15s ease;
        }

        .cert-table tbody tr:hover {
            background: #F8FBFD;
        }

        .cert-table tbody tr:last-child td {
            border-bottom: none;
        }

        .cert-number {
            color: #176B87;
            font-size: 12px;
            font-weight: 750;
        }

        .cert-student-name {
            color: #25313C;
            font-size: 12px;
            font-weight: 700;
        }

        .cert-muted {
            margin-top: 5px;
            color: #94A3B8;
            font-size: 10px;
        }

        .cert-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border: 1px solid #E0E7FF;
            border-radius: 20px;
            background: #EEF2FF;
            color: #4338A3;
            font-size: 10px;
            font-weight: 700;
        }

        .cert-date {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
        }

        .cert-date i {
            color: #94A3B8;
        }

        /* Row actions */
        .cert-row-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .cert-action-btn {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 10px;
            border: 1px solid #CBD5E1;
            border-radius: 7px;
            background: #fff;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: all .18s ease;
        }

        .cert-action-btn.view:hover {
            background: #F0F9FF;
            border-color: #7DD3FC;
            color: #0369A1;
        }

        .cert-action-btn.download {
            color: #176B87;
            border-color: #B6D8E3;
        }

        .cert-action-btn.download:hover {
            background: #EAF4F8;
            border-color: #176B87;
        }

        /* Empty state */
        .cert-empty {
            padding: 55px 20px !important;
            text-align: center;
            white-space: normal;
        }

        .cert-empty-icon {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border-radius: 15px;
            background: #F1F5F9;
            color: #94A3B8;
            font-size: 22px;
        }

        .cert-empty strong {
            display: block;
            color: #334155;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .cert-empty p {
            margin: 0;
            color: #94A3B8;
            font-size: 11px;
            line-height: 1.6;
        }

        /* Pagination */
        .cert-pagination {
            padding: 16px 22px;
            border-top: 1px solid #E8EDF3;
            background: #FCFDFE;
        }

        .cert-pagination nav {
            display: flex;
            justify-content: flex-end;
        }

        .cert-pagination svg {
            width: 16px;
            height: 16px;
        }

        /* Responsive */
        @media(max-width: 900px) {
            .cert-filter {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .cert-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media(max-width: 650px) {
            .cert-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .cert-header-actions {
                width: 100%;
            }

            .cert-header-actions .sn-btn {
                flex: 1;
            }

            .cert-stats {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .cert-stat {
                padding: 14px 16px;
            }

            .cert-stat-value {
                font-size: 20px;
            }

            .cert-filter {
                grid-template-columns: 1fr;
            }

            .cert-filter-actions {
                grid-column: auto;
            }

            .cert-filter-actions .sn-btn {
                flex: 1;
            }

            .cert-filter-body {
                padding: 17px;
            }

            .cert-filter-head,
            .cert-table-head {
                padding: 17px;
            }

            .cert-table th,
            .cert-table td {
                padding: 13px 14px;
            }
        }
    </style>

    <div class="cert-wrap">

        {{-- Page Header --}}
        <div class="sn-head cert-head">
            <div class="cert-heading">
                <div class="cert-heading-icon">
                    <i class="fas fa-certificate"></i>
                </div>

                <div>
                    <h1 class="sn-title">Certificate History</h1>
                    <div class="sn-sub">
                        Search, view and manage all certificates issued by your school.
                    </div>
                </div>
            </div>

            <div class="cert-header-actions">
                <a href="{{ route('dashboard') }}"
                   class="sn-btn sn-btn-outline">
                    Dashboard
                </a>

                <a href="{{ route('certificates.create') }}"
                   class="sn-btn sn-btn-primary">
                    Generate Certificate
                </a>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="cert-stats">

            <div class="cert-stat">
                <div class="cert-stat-icon blue">
                    <i class="fas fa-certificate"></i>
                </div>
                <div>
                    <div class="cert-stat-label">Total Certificates</div>
                    <div class="cert-stat-value">
                        {{ $certificates->total() }}
                    </div>
                </div>
            </div>

            <div class="cert-stat">
                <div class="cert-stat-icon purple">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <div class="cert-stat-label">Matching Records</div>
                    <div class="cert-stat-value">
                        {{ $certificates->count() }}
                    </div>
                </div>
            </div>

            <div class="cert-stat">
                <div class="cert-stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="cert-stat-label">Current Page</div>
                    <div class="cert-stat-value">
                        {{ $certificates->currentPage() }}
                        <span style="font-size:12px;color:#94A3B8;font-weight:500">
                            / {{ max(1, $certificates->lastPage()) }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Search and Filters --}}
        <div class="cert-card">

            <div class="cert-filter-head">
                <div class="cert-filter-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>

                <div>
                    <h2>Search & Filters</h2>
                    <p>Find certificates using student details or certificate type.</p>
                </div>
            </div>

            <div class="cert-filter-body">
                <form method="GET"
                      action="{{ route('certificates.index') }}"
                      class="cert-filter">

                    <div class="cert-field">
                        <label for="search">Search Certificates</label>

                        <div class="cert-input-wrap">
                            <i class="fas fa-search"></i>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Certificate no., student name or admission no."
                            >
                        </div>
                    </div>

                    <div class="cert-field">
                        <label for="type">Certificate Type</label>

                        <select id="type" name="type">
                            <option value="">All certificate types</option>

                            @foreach($types as $type)
                                <option value="{{ $type }}"
                                    @selected(request('type') === $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="cert-filter-actions">
                        <button type="submit" class="sn-btn sn-btn-primary">
                            <i class="fas fa-search"></i>
                            Apply Filters
                        </button>

                        <a href="{{ route('certificates.index') }}"
                           class="sn-btn sn-btn-outline">
                            <i class="fas fa-undo"></i>
                            Reset
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Issued Certificates Table --}}
        <div class="cert-card">

            <div class="cert-table-head">
                <div>
                    <h2 class="cert-table-title">Issued Certificates</h2>
                    <div class="cert-table-sub">
                        Manage and access previously generated certificates.
                    </div>
                </div>

                <div class="cert-count">
                    <i class="fas fa-file-alt"></i>
                    {{ $certificates->total() }} Records
                </div>
            </div>

            <div class="cert-table-wrap">
                <table class="cert-table">

                    <thead>
                        <tr>
                            <th>CERTIFICATE NUMBER</th>
                            <th>STUDENT DETAILS</th>
                            <th>CERTIFICATE TYPE</th>
                            <th>ISSUED DATE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($certificates as $certificate)

                            <tr>
                                {{-- Certificate number --}}
                                <td>
                                    <div class="cert-number">
                                        {{ $certificate->certificate_number }}
                                    </div>
                                </td>

                                {{-- Student --}}
                                <td>
                                    <div class="cert-student-name">
                                        {{ trim(($certificate->first_name ?? '').' '.($certificate->middle_name ?? '').' '.($certificate->last_name ?? '')) ?: '—' }}
                                    </div>

                                    <div class="cert-muted">
                                        Admission No:
                                        {{ $certificate->admission_number ?? '—' }}
                                    </div>
                                </td>

                                {{-- Type --}}
                                <td>
                                    <span class="cert-type">
                                        <i class="fas fa-award"></i>
                                        {{ $certificate->certificate_type }}
                                    </span>
                                </td>

                                {{-- Date --}}
                                <td>
                                    <span class="cert-date">
                                        <i class="far fa-calendar-alt"></i>
                                        {{ \Carbon\Carbon::parse($certificate->issued_date)->format('d M Y') }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="cert-row-actions">

                                        <a href="{{ route('certificates.show', $certificate->id) }}"
                                           class="cert-action-btn view">
                                            <i class="fas fa-eye"></i>
                                            View
                                        </a>

                                        <a href="{{ route('certificates.download', $certificate->id) }}"
                                           class="cert-action-btn download">
                                            <i class="fas fa-download"></i>
                                            Download
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="cert-empty">
                                    <div class="cert-empty-icon">
                                        <i class="fas fa-file-circle-xmark"></i>
                                    </div>

                                    <strong>No certificates found</strong>

                                    <p>
                                        No matching certificate records are available.
                                        Try adjusting your search filters or generate a new certificate.
                                    </p>
                                </td>
                            </tr>

                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            @if($certificates->hasPages())
                <div class="cert-pagination">
                    {{ $certificates->appends(request()->query())->links() }}
                </div>
            @endif

        </div>

    </div>
</div>
@endsection