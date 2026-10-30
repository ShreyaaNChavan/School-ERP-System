<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | SchoolNexa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f7fc;
            color: #172b46;
            font-family: Inter, Arial, sans-serif;
            font-size: 13px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button {
            font: inherit;
        }

        .app {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 235px;
            flex-shrink: 0;
            background: #fff;
            border-right: 1px solid #e6ebf2;
            padding: 17px 12px;
            display: flex;
            flex-direction: column;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 8px 22px;
            font-size: 20px;
            font-weight: 800;
            color: #173b66;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #eaf3ff;
            color: #1673d1;
            display: grid;
            place-items: center;
            font-size: 17px;
        }

        .brand span {
            color: #1682d9;
        }

        .side-caption {
            padding: 13px 10px 6px;
            color: #8592a5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
        }

        .side-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 11px;
            margin: 2px 0;
            border-radius: 7px;
            color: #35465d;
            font-size: 12px;
            transition: .15s;
        }

        .side-link:hover,
        .side-link.active {
            background: #eaf4fb;
            color: #14558c;
        }

        .side-icon {
            width: 18px;
            text-align: center;
            font-size: 15px;
            color: #426587;
        }

        .side-bottom {
            margin-top: auto;
            border-top: 1px solid #edf0f5;
            padding-top: 13px;
        }

        .profile-mini {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 12px 8px 4px;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #dcebd9;
            display: grid;
            place-items: center;
            color: #245a3a;
            font-weight: 700;
        }

        .profile-name {
            font-size: 12px;
            font-weight: 700;
        }

        .profile-role {
            font-size: 10px;
            color: #8290a2;
            margin-top: 3px;
        }

        .main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 58px;
            background: #fff;
            border-bottom: 1px solid #e8edf4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 0 23px;
        }

        .search {
            width: min(310px, 35%);
            border: 1px solid #e8edf4;
            border-radius: 7px;
            padding: 9px 12px;
            color: #8491a4;
            background: #fbfcfe;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-select,
        .top-button {
            border: 1px solid #e7ebf2;
            background: #fff;
            color: #34465c;
            border-radius: 7px;
            padding: 9px 12px;
        }

        .top-button {
            width: 36px;
            height: 36px;
            padding: 0;
            font-size: 17px;
        }

        .top-avatar {
            width: 31px;
            height: 31px;
            border-radius: 50%;
            background: #dcebd9;
            display: grid;
            place-items: center;
        }

        .page {
            max-width: 1700px;
            margin: auto;
            padding: 16px 18px 30px;
        }

        .welcome {
            min-height: 82px;
            border-radius: 12px;
            background: #eaf4ff;
            padding: 15px 23px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .eyebrow {
            color: #74869d;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 5px;
        }

        .welcome h1 {
            margin: 0;
            font-size: 22px;
            color: #183e65;
        }

        .welcome p {
            color: #71849b;
            font-size: 11px;
            margin: 5px 0 0;
        }

        .welcome-art {
            font-size: 36px;
            padding: 0 25px;
        }

        .welcome-date {
            text-align: right;
            color: #71849b;
            font-size: 10px;
        }

        .welcome-date strong {
            display: block;
            color: #18446d;
            font-size: 17px;
            margin-top: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            padding: 11px;
            background: #fff;
            border: 1px solid #e1e8f0;
            margin-bottom: 12px;
        }

        .stat-card {
            min-height: 106px;
            border: 1px solid #edf0f4;
            padding: 10px 13px;
            text-align: center;
            background: #fff;
        }

        .stat-icon {
            height: 52px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            background: transparent !important;
        }

        .stat-icon img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            transition: transform .25s ease;
        }

        .stat-card:hover .stat-icon img {
            transform: translateY(-4px) scale(1.08);
        }

        .stat-label {
            font-size: 11px;
            color: #34445a;
        }

        .stat-value {
            font-size: 21px;
            font-weight: 750;
            margin: 6px 0 3px;
            color: #23364d;
        }

        .stat-note {
            font-size: 9px;
            color: #16a05b;
        }

        .stat-note.neutral {
            color: #718096;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.05fr 1.05fr 1fr;
            gap: 12px;
            align-items: start;
        }

        .panel {
            background: #fff;
            border: 1px solid #e1e8f0;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 11px;
            min-width: 0;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 13px;
        }

        .panel-title {
            font-size: 14px;
            font-weight: 750;
            color: #244665;
            margin: 0;
        }

        .panel-link {
            font-size: 10px;
            color: #3873ae;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .quick-item {
            border: 1px solid #e1e8f0;
            background: #f8fafd;
            border-radius: 7px;
            min-height: 65px;
            padding: 10px 5px;
            text-align: center;
            color: #31475e;
        }

        .quick-item span {
            display: block;
            font-size: 19px;
            margin-bottom: 5px;
        }

        .quick-item strong {
            font-size: 10px;
            font-weight: 600;
        }

        .calendar-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .calendar-month {
            font-weight: 700;
            color: #365675;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            gap: 5px 2px;
        }

        .calendar-grid span {
            padding: 6px 0;
            font-size: 10px;
            color: #53667b;
        }

        .calendar-grid .day {
            color: #8390a0;
            font-size: 9px;
        }

        .calendar-grid .today {
            background: #174c76;
            color: #fff;
            border-radius: 5px;
        }

        .event-row,
        .alert-row,
        .notice-row {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid #eef1f5;
        }

        .event-row:last-child,
        .alert-row:last-child,
        .notice-row:last-child {
            border: 0;
        }

        .row-icon {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border-radius: 7px;
            display: grid;
            place-items: center;
            background: #edf4fc;
            font-size: 15px;
        }

        .row-title {
            font-size: 10px;
            font-weight: 700;
            color: #344b63;
        }

        .row-sub {
            font-size: 9px;
            color: #8996a6;
            margin-top: 4px;
        }

        .pill {
            font-size: 9px;
            border-radius: 10px;
            padding: 3px 7px;
            background: #fff0e4;
            color: #dc8738;
            margin-left: auto;
        }

        .chart-placeholder {
            height: 135px;
            display: flex;
            align-items: end;
            justify-content: space-around;
            gap: 9px;
            border-bottom: 1px solid #e7edf4;
            padding: 8px 5px 0;
        }

        .bar-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: end;
            align-items: center;
            height: 100%;
            gap: 5px;
        }

        .bar {
            width: 100%;
            max-width: 25px;
            background: #234f73;
            border-radius: 4px 4px 0 0;
            min-height: 3px;
        }

        .bar-label {
            font-size: 9px;
            color: #8491a1;
        }

        .growth-list {
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .growth-line {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 10px;
        }

        .growth-name {
            width: 105px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .growth-track {
            flex: 1;
            height: 7px;
            background: #edf1f6;
            border-radius: 8px;
            overflow: hidden;
        }

        .growth-fill {
            height: 100%;
            background: #2877aa;
            border-radius: 8px;
        }

        .growth-value {
            width: 24px;
            text-align: right;
            color: #758398;
        }

        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .metric-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .metric {
            background: #f5f8fc;
            padding: 12px;
            border-radius: 7px;
        }

        .metric strong {
            display: block;
            font-size: 18px;
            color: #214d72;
            margin-top: 6px;
        }

        .metric small {
            color: #8390a2;
            font-size: 9px;
        }

        .ai-panel {
            margin-top: 12px;
            padding: 17px 20px;
            background: #e9ddff;
            border: 1px solid #c5a9fa;
            border-radius: 9px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .ai-panel h3 {
            margin: 0 0 6px;
            color: #5b3c9e;
            font-size: 15px;
        }

        .ai-panel p {
            margin: 0;
            color: #5d5570;
            font-size: 11px;
        }

        .ai-button {
            background: #6243a6;
            color: white;
            border: 0;
            padding: 9px 14px;
            border-radius: 6px;
            font-size: 10px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #95a0af;
            font-size: 10px;
        }

        @media(max-width:1200px) {
            .sidebar {
                width: 205px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
            }

            .dashboard-grid> :last-child {
                grid-column: span 2;
            }
        }

        @media(max-width:800px) {
            .sidebar {
                width: 64px;
                padding: 12px 7px;
            }

            .brand {
                justify-content: center;
                padding: 8px 0 20px;
                font-size: 0;
            }

            .brand-mark {
                flex-shrink: 0;
            }

            .side-caption,
            .side-text,
            .profile-info {
                display: none;
            }

            .side-link {
                justify-content: center;
                padding: 12px 5px;
            }

            .side-icon {
                font-size: 17px;
            }

            .profile-mini {
                justify-content: center;
            }

            .topbar {
                padding: 0 12px;
            }

            .search {
                width: 35%;
            }

            .top-select {
                display: none;
            }

            .page {
                padding: 12px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-grid> :last-child {
                grid-column: auto;
            }

            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width:480px) {
            .welcome {
                padding: 13px;
            }

            .welcome-art,
            .welcome-date {
                display: none;
            }

            .welcome h1 {
                font-size: 18px;
            }

            .topbar {
                height: 52px;
            }

            .search {
                width: 45%;
            }

            .top-actions {
                gap: 5px;
            }

            .top-button {
                width: 31px;
                height: 31px;
            }

            .quick-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 8px 22px;
            text-decoration: none;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 19px;
            font-weight: 800;
            color: #173b66;
        }

        .brand-name span {
            color: #1682d9;
        }

        .side-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 11px;
            margin: 2px 0;
            border-radius: 7px;
            color: #35465d;
            font-size: 12px;
            transition: .2s;
        }

        .side-link svg {
            color: #547493;
            flex-shrink: 0;
        }

        .side-link:hover,
        .side-link.active {
            background: #eaf4fb;
            color: #14558c;
        }

        .side-link:hover svg,
        .side-link.active svg {
            color: #1673bd;
        }

        @media(max-width:800px) {
            .brand {
                justify-content: center;
                padding: 8px 0 20px;
            }

            .brand-logo {
                width: 42px;
                height: 42px;
            }

            .brand-name {
                display: none;
            }
        }

        .sn-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 15px;
            border: 1px solid #176ca7;
            border-radius: 7px;
            background: #176ca7;
            color: white;
            font-size: 12px;
            font-weight: 650;
            cursor: pointer;
        }

        .sn-button:hover {
            background: #125783;
            color: white;
        }

        .sn-button.secondary {
            background: white;
            color: #40566e;
            border-color: #dce4ed;
        }

        .sn-alert {
            padding: 12px 15px;
            border-radius: 7px;
            margin-bottom: 14px;
            font-size: 12px;
        }

        .sn-alert.success {
            background: #e8f7ee;
            color: #167447;
        }

        .sn-alert.error {
            background: #fff0f0;
            color: #b42318;
        }

        .org-stats {
            grid-template-columns: minmax(180px, 250px);
            margin-bottom: 12px;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-bottom: 18px;
        }

        .sn-input {
            width: 100%;
            min-height: 39px;
            border: 1px solid #dfe6ef;
            border-radius: 7px;
            padding: 9px 11px;
            background: white;
            color: #253d55;
            font-size: 12px;
            outline: none;
        }

        .filter-bar .sn-input:first-child {
            flex: 1;
            min-width: 190px;
        }

        .filter-bar select.sn-input {
            width: 155px;
        }

        .sn-input:focus {
            border-color: #3988c5;
            box-shadow: 0 0 0 3px rgba(57, 136, 197, .1);
        }

        .table-scroll {
            overflow-x: auto;
        }

        .sn-table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .sn-table th {
            padding: 12px;
            text-align: left;
            color: #78889b;
            background: #f8fafc;
            font-size: 10px;
            text-transform: uppercase;
        }

        .sn-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf0f4;
            font-size: 11px;
        }

        .status-pill {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 10px;
        }

        .status-pill.active {
            background: #e8f7ee;
            color: #16804b;
        }

        .status-pill.inactive {
            background: #fff0f0;
            color: #c33b3b;
        }

        .table-action {
            color: #176ca7;
            border: 0;
            background: none;
            cursor: pointer;
            font-size: 11px;
            margin-right: 8px;
        }

        .empty-state {
            text-align: center;
            color: #8794a4;
            padding: 35px !important;
        }

        .pagination-wrap {
            margin-top: 17px;
        }

        .form-panel {
            max-width: 950px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 22px;
        }

        .form-field label {
            display: block;
            margin-bottom: 7px;
            font-size: 11px;
            font-weight: 650;
            color: #42566e;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .field-error {
            display: block;
            color: #c33b3b;
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px solid #edf0f4;
        }

        @media(max-width:600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>
    <div class="app">
        @include('partials.sidebar')
        <main class="main">
            <header class="topbar">
                <input class="search" type="search" placeholder="⌕  Search platform...">
                <div class="top-actions">
                    <select class="top-select" aria-label="Academic year">
                        <option>Platform overview</option>
                    </select>
                    <select class="top-select" aria-label="Language">
                        <option>English</option>
                    </select>
                    <button class="top-button" type="button" title="Quick actions">＋</button>
                    <button class="top-button" type="button" title="Display settings">☼</button>
                    <button class="top-button" type="button" title="Notifications">♧</button>
                    <div class="top-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="top-button" type="submit" title="Logout">↪</button>
                    </form>
                </div>
            </header>

            <section class="page">
                @yield('content')
                <div class="footer">© {{ date('Y') }} SchoolNexa · School Management Platform</div>
            </section>
        </main>
    </div>
</body>

</html>