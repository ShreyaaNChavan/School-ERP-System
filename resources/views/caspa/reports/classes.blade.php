@extends('layouts.schoolnexa')
@section('content')
    <div class="sn-page rpt">
        <style>
            .rpt {
                font-family: Inter, system-ui, sans-serif;
                color: #25313c
            }

            .rpt * {
                box-sizing: border-box
            }

            .rh {
                display: flex;
                justify-content: space-between;
                gap: 15px;
                align-items: flex-start;
                margin-bottom: 20px
            }

            .rh h1 {
                font-size: 23px;
                color: #123b5d;
                margin: 0 0 5px
            }

            .muted {
                color: #64748b;
                font-size: 13px
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 40px;
                padding: 0 15px;
                border: 1px solid #cbd5e1;
                border-radius: 9px;
                background: #fff;
                color: #123b5d;
                text-decoration: none;
                font-size: 13px;
                font-weight: 650;
                cursor: pointer
            }

            .primary {
                background: #123b5d;
                color: white;
                border-color: #123b5d
            }

            .panel,
            .kpi {
                background: #fff;
                border: 1px solid #dbe3ec;
                border-radius: 14px;
                padding: 18px;
                margin-bottom: 16px
            }

            .panel h3 {
                font-size: 15px;
                color: #123b5d;
                margin: 0 0 15px
            }

            .filters {
                display: grid;
                grid-template-columns: repeat(6, minmax(0, 1fr));
                gap: 12px
            }

            .field label {
                display: block;
                font-size: 12px;
                font-weight: 650;
                margin-bottom: 6px;
                color: #475569
            }

            .field input,
            .field select {
                width: 100%;
                height: 42px;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                padding: 0 9px;
                font: 13px Inter
            }

            .kpis {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 14px;
                margin-bottom: 16px
            }

            .kpi {
                margin: 0
            }

            .kpi span {
                font-size: 12px;
                color: #64748b
            }

            .kpi strong {
                display: block;
                margin-top: 8px;
                font-size: 25px;
                color: #123b5d
            }

            .tblwrap {
                overflow: auto
            }

            .tbl {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px
            }

            .tbl th {
                background: #f8fafc;
                color: #64748b;
                font-size: 11px;
                text-align: left;
                padding: 12px;
                border-bottom: 1px solid #e2e8f0;
                white-space: nowrap
            }

            .tbl td {
                padding: 12px;
                border-bottom: 1px solid #e2e8f0;
                white-space: nowrap
            }

            .empty {
                text-align: center !important;
                padding: 30px !important;
                color: #64748b
            }

            .pager {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
                margin-top: 15px
            }

            .links {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px
            }

            .link {
                padding: 16px;
                border: 1px solid #dbe3ec;
                border-radius: 11px;
                text-decoration: none;
                color: #123b5d
            }

            .link strong {
                display: block;
                margin-bottom: 5px
            }

            .link span,
            .note {
                font-size: 12px;
                color: #64748b
            }

            .note {
                margin: 10px 0
            }

            @media(max-width:1050px) {
                .filters {
                    grid-template-columns: repeat(3, 1fr)
                }

                .kpis {
                    grid-template-columns: repeat(2, 1fr)
                }
            }

            @media(max-width:650px) {
                .rh {
                    flex-direction: column
                }

                .filters,
                .links {
                    grid-template-columns: 1fr
                }
            }

            @media print {

                .actions,
                .filterbox,
                .pager {
                    display: none !important
                }
            }
        </style>
        <div class="rh">
            <div>
                <h1>{{ $meta[0] }}</h1>
                <div class="muted">{{ $meta[1] }}</div>
            </div>
            <div class="actions"><button class="btn" onclick="window.print()" type="button">Print</button>
                <a href="{{ route('caspa.reports.index', ['report' => 'dashboard']) }}" class="btn">
                    Back to Reports
                </a>
            </div>
        </div>
        @if(session('success'))
        <div class="panel">{{ session('success') }}</div>@endif
        @if($errors->any())
        <div class="panel">{{ $errors->first() }}</div>@endif
        <form method="GET" action="{{ url('/caspa/reports/classes') }}" class="panel filterbox">
            <h3>Report Filters</h3>
            <div class="filters">
                <div class="field"><label>From date</label><input type="date" name="from"
                        value="{{ $filters['from'] ?? '' }}"></div>
                <div class="field"><label>To date</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="field"><label>Academic year</label><select name="academic_year_id">
                        <option value="">All years</option>@foreach($years as $y)
                            <option value="{{ $y->id }}" @selected((string) ($filters['academic_year_id'] ?? '') === (string) $y->id)>
                                {{ $y->name }}
                        </option>@endforeach
                    </select></div>
                <div class="field"><label>Class</label><select name="class_id">
                        <option value="">All classes</option>@foreach($classes as $c)
                            <option value="{{ $c->id }}" @selected((string) ($filters['class_id'] ?? '') === (string) $c->id)>
                                {{ $c->name }}
                        </option>@endforeach
                    </select></div>
                <div class="field"><label>Section</label><select name="section_id">
                        <option value="">All sections</option>@foreach($sections as $s)
                            <option value="{{ $s->id }}" @selected((string) ($filters['section_id'] ?? '') === (string) $s->id)>
                                {{ $s->name }}
                        </option>@endforeach
                    </select></div>
                <div class="field"><label>Search</label><input name="q" value="{{ $filters['q'] ?? '' }}"
                        placeholder="Search records"></div>
            </div>
            <div style="display:flex;gap:9px;margin-top:13px"><button class="btn primary">Apply filters</button><a
                    class="btn" href="{{ url('/caspa/reports/classes') }}">Reset</a></div>
        </form>
        <div class="kpis">@foreach($data['cards'] as $c)
        <div class="kpi"><span>{{ $c['label'] }}</span><strong>{{ $c['value'] }}</strong></div>@endforeach
        </div>
        <div class="panel">
            <h3>Class & Section Analytics</h3>
            <div class="tblwrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Class Code</th>
                            <th>Section</th>
                            <th>Enrolled Students</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($data['rows'] as $row)
                        <tr>
                            <td>{{ data_get($row, "class_name") ?? "—" }}</td>
                            <td>{{ data_get($row, "code") ?? "—" }}</td>
                            <td>{{ data_get($row, "section") ?? "—" }}</td>
                            <td>{{ data_get($row, "enrolled_students") ?? "—" }}</td>
                    </tr>@empty<tr>
                            <td colspan="4" class="empty">No records found for the selected filters.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="pager"><span class="btn">Showing
                    {{ $data['rows']->firstItem() ?? 0 }}–{{ $data['rows']->lastItem() ?? 0 }} of
                    {{ $data['rows']->total() }}</span>
                <div>{{ $data['rows']->links() }}</div>
            </div>
        </div>
        @foreach(($data['notes'] ?? []) as $note)
        <div class="note">{{ $note }}</div>@endforeach
    </div>
@endsection