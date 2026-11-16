<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | SchoolNexa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
            align-items: flex-start;
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
            <header class="topbar sn-topbar">
                <div class="sn-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="search" id="snGlobalSearch" type="search"
                           placeholder="Search pages and modules..." autocomplete="off"
                           aria-label="Search platform" aria-expanded="false">
                    <kbd>⌘ K</kbd>
                    <div class="sn-search-results" id="snSearchResults" role="listbox" hidden></div>
                </div>

                <div class="top-actions">
                    <span class="sn-context-label"><i class="fa-solid fa-layer-group"></i> Platform overview</span>

                    <div class="sn-top-dropdown">
                        <button class="top-button" type="button" id="snQuickBtn" title="Quick actions" aria-expanded="false">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        <div class="sn-top-menu sn-quick-menu" id="snQuickMenu" hidden>
                            <strong>Quick access</strong>
                            <div id="snQuickLinks"><span class="sn-menu-empty">Loading shortcuts…</span></div>
                        </div>
                    </div>

                    <button class="top-button" type="button" id="snThemeBtn" title="Toggle dark mode" aria-label="Toggle dark mode">
                        <i class="fa-regular fa-moon"></i>
                    </button>

                    <div class="sn-top-dropdown">
                        <button class="top-button sn-notification-btn" type="button" id="snNotificationBtn"
                                title="Notifications" aria-expanded="false">
                            <i class="fa-regular fa-bell"></i><span class="sn-notification-dot"></span>
                        </button>
                        <div class="sn-top-menu sn-notification-menu" id="snNotificationMenu" hidden>
                            <div class="sn-menu-heading"><strong>Notifications</strong><span>Recent updates</span></div>
                            <div class="sn-notification-empty">
                                <i class="fa-regular fa-bell-slash"></i>
                                <strong>You're all caught up</strong>
                                <span>New notifications will appear here when available.</span>
                            </div>
                        </div>
                    </div>

                    <div class="sn-top-dropdown">
                        <button class="sn-profile-trigger" type="button" id="snProfileBtn" aria-expanded="false">
                            <span class="top-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                            <span class="sn-profile-trigger-name">{{ auth()->user()->name ?? 'Account' }}</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="sn-top-menu sn-profile-menu" id="snProfileMenu" hidden>
                            <div class="sn-profile-summary">
                                <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                                <span>{{ auth()->user()->email ?? '' }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="sn-logout-action" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <section class="page">
                @yield('content')
                <div class="footer">© {{ date('Y') }} SchoolNexa · School Management Platform</div>
            </section>
        </main>
    </div>


    {{-- SchoolNexa Flash Toast Notifications --}}
    @php
        $toastMessages = [];

        foreach (['success', 'error', 'warning', 'info'] as $type) {
            if (session()->has($type)) {
                $toastMessages[] = [
                    'type' => $type,
                    'message' => session($type)
                ];
            }
        }

        if ($errors->any()) {
            $toastMessages[] = [
                'type' => 'error',
                'message' => $errors->first()
            ];
        }
    @endphp

    <div id="sn-toast-container" style="position:fixed;top:90px;right:24px;z-index:99999;
            display:flex;flex-direction:column;gap:12px;
            width:min(380px,calc(100vw - 32px));">

        @foreach($toastMessages as $toast)
            <div class="sn-toast sn-toast-{{ $toast['type'] }}">
                <div class="sn-toast-icon">
                    @if($toast['type'] === 'success')
                        ✓
                    @elseif($toast['type'] === 'error')
                        !
                    @elseif($toast['type'] === 'warning')
                        ⚠
                    @else
                        i
                    @endif
                </div>

                <div class="sn-toast-content">
                    <strong>{{ ucfirst($toast['type']) }}</strong>
                    <span>{{ $toast['message'] }}</span>
                </div>

                <button type="button" class="sn-toast-close" onclick="this.parentElement.remove()">
                    &times;
                </button>
            </div>
        @endforeach
    </div>

    <style>
        .sn-toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px;
            border: 1px solid;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(15, 23, 42, .13);
            animation: snToastIn .3s ease;
            font-family: Inter, sans-serif;
        }

        .sn-toast-success {
            border-color: #86efac;
        }

        .sn-toast-error {
            border-color: #fca5a5;
        }

        .sn-toast-warning {
            border-color: #fcd34d;
        }

        .sn-toast-info {
            border-color: #93c5fd;
        }

        .sn-toast-icon {
            width: 27px;
            height: 27px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
        }

        .sn-toast-success .sn-toast-icon {
            color: #16a34a;
            background: #dcfce7;
        }

        .sn-toast-error .sn-toast-icon {
            color: #dc2626;
            background: #fee2e2;
        }

        .sn-toast-warning .sn-toast-icon {
            color: #d97706;
            background: #fef3c7;
        }

        .sn-toast-info .sn-toast-icon {
            color: #2563eb;
            background: #dbeafe;
        }

        .sn-toast-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 13px;
            color: #25313c;
        }

        .sn-toast-content strong {
            font-size: 14px;
        }

        .sn-toast-content span {
            line-height: 1.5;
        }

        .sn-toast-close {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 22px;
            cursor: pointer;
            padding: 0 3px;
        }

        @keyframes snToastIn {
            from {
                opacity: 0;
                transform: translateX(25px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes snToastOut {
            to {
                opacity: 0;
                transform: translateX(25px);
            }
        }


        .sidebar {
            position: sticky;
            top: 0;
            align-self: flex-start;
            width: 235px;
            flex: 0 0 235px;
            height: 100vh;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 100;
        }

        @media (max-width: 1200px) {
            .sidebar {
                width: 205px;
                flex-basis: 205px;
            }
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 64px;
                flex-basis: 64px;
            }
        }

        .sidebar-brand {
            flex-shrink: 0;
        }

        .sidebar-navigation {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .sidebar-navigation::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-navigation::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .sidebar-profile {
            flex-shrink: 0;
            position: relative;
            background: #fff;
            border-top: 1px solid #e5e7eb;
        }

        /* SchoolNexa fixed sidebar + independently scrolling content
   Append this block at the VERY END of the <style> block in
   resources/views/layouts/schoolnexa.blade.php. */

        html,
        body {
            width: 100%;
            height: 100%;
            min-height: 0;
        }

        body {
            margin: 0;
            overflow: hidden;
        }

        .app {
            width: 100%;
            height: 100vh;
            height: 100dvh;
            min-height: 0;
            display: flex;
            align-items: stretch;
            overflow: hidden;
        }

        .sidebar {
            position: sticky;
            top: 0;
            align-self: flex-start;
            width: 235px;
            height: 100vh;
            height: 100dvh;
            min-height: 0;
            flex: 0 0 235px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-nav {
            flex: 1 1 auto;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
            padding-bottom: 10px;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav-group {
            flex: 0 0 auto;
        }

        .side-bottom {
            flex: 0 0 auto;
            margin-top: 0;
            background: #fff;
            padding-top: 10px;
        }

        .main {
            flex: 1 1 auto;
            width: 0;
            min-width: 0;
            height: 100vh;
            height: 100dvh;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .sidebar-logout {
            margin: 0;
        }

        .sidebar-logout button {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            cursor: pointer;
            font: inherit;
        }

        .sidebar-empty {
            padding: 14px 10px;
            color: #8290a2;
            font-size: 12px;
        }

        @media (max-width: 1200px) {
            .sidebar {
                width: 205px;
                flex-basis: 205px;
            }
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 64px;
                flex-basis: 64px;
                padding: 12px 7px;
            }

            .sidebar-nav {
                min-width: 0;
            }

            .sidebar .side-text,
            .sidebar .side-caption,
            .sidebar .profile-info {
                display: none;
            }

            .sidebar .side-link {
                justify-content: center;
                padding: 12px 5px;
            }

            .sidebar .profile-mini {
                justify-content: center;
            }

            .sidebar .brand {
                justify-content: center;
                padding: 8px 0 20px;
            }

            .sidebar .brand-name {
                display: none;
            }
        }

        /* Interactive SchoolNexa topbar */
        .sn-topbar { gap:18px; }
        .sn-search-wrap { position:relative; width:min(430px,48%); display:flex; align-items:center; }
        .sn-search-wrap>i { position:absolute; left:13px; color:#8292a6; z-index:1; }
        .sn-search-wrap .search { width:100%; padding:10px 48px 10px 37px; color:#263b52; outline:none; }
        .sn-search-wrap .search:focus { border-color:#4c91c7; box-shadow:0 0 0 3px rgba(76,145,199,.12); background:#fff; }
        .sn-search-wrap kbd { position:absolute; right:10px; color:#91a0b1; background:#f1f4f8; border:1px solid #e3e8ef; border-radius:4px; padding:2px 5px; font-size:9px; }
        .sn-search-results,.sn-top-menu { position:absolute; top:calc(100% + 9px); right:0; z-index:1000; background:#fff; border:1px solid #e4eaf1; border-radius:11px; box-shadow:0 14px 38px rgba(20,42,67,.16); color:#263b52; }
        .sn-search-results { left:0; right:auto; width:100%; max-height:380px; overflow:auto; padding:7px; }
        .sn-search-results a,.sn-quick-link { display:flex; align-items:center; gap:10px; padding:10px; border-radius:7px; color:#344b63; font-size:12px; }
        .sn-search-results a:hover,.sn-quick-link:hover { background:#eff6fc; color:#176ca7; }
        .sn-search-results a i,.sn-quick-link i { width:17px; color:#6485a3; text-align:center; }
        .sn-search-empty,.sn-menu-empty { display:block; padding:13px; color:#8a98a9; font-size:11px; }
        .sn-context-label { display:flex; align-items:center; gap:7px; color:#64758a; font-size:11px; white-space:nowrap; padding:0 5px; }
        .sn-context-label i { color:#4380ad; }
        .sn-top-dropdown { position:relative; }
        .sn-top-menu { width:285px; padding:9px; }
        .sn-top-menu[hidden],.sn-search-results[hidden] { display:none !important; }
        .sn-top-menu>strong { display:block; padding:8px 9px 10px; font-size:12px; }
        .sn-quick-menu { width:270px; }
        .sn-notification-menu { width:310px; }
        .sn-menu-heading { display:flex; justify-content:space-between; align-items:center; padding:8px 9px 12px; border-bottom:1px solid #edf1f5; }
        .sn-menu-heading strong { font-size:13px; }
        .sn-menu-heading span { font-size:10px; color:#8b98a8; }
        .sn-notification-empty { min-height:145px; display:flex; flex-direction:column; justify-content:center; align-items:center; gap:7px; text-align:center; padding:20px; color:#8795a6; }
        .sn-notification-empty i { font-size:24px; color:#9aabba; }
        .sn-notification-empty strong { color:#40566e; font-size:12px; }
        .sn-notification-empty span { font-size:10px; line-height:1.5; }
        .sn-notification-dot { position:absolute; width:7px; height:7px; border-radius:50%; background:#e35d6a; top:7px; right:7px; border:1px solid #fff; }
        .sn-profile-trigger { display:flex; align-items:center; gap:8px; border:0; background:transparent; cursor:pointer; color:#40566e; padding:2px 4px; }
        .sn-profile-trigger-name { max-width:115px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:11px; font-weight:650; }
        .sn-profile-trigger>i { font-size:9px; color:#8795a6; }
        .sn-profile-menu { width:235px; }
        .sn-profile-summary { padding:9px 10px 13px; display:flex; flex-direction:column; gap:5px; border-bottom:1px solid #edf1f5; }
        .sn-profile-summary strong { font-size:12px; color:#253d55; }
        .sn-profile-summary span { font-size:10px; color:#8492a3; overflow-wrap:anywhere; }
        .sn-logout-action { display:flex; align-items:center; gap:9px; width:100%; padding:11px 10px; margin-top:5px; border:0; border-radius:7px; background:transparent; color:#bd3d48; cursor:pointer; text-align:left; font-size:11px; }
        .sn-logout-action:hover { background:#fff1f2; }
        body.sn-dark { background:#111c2b; color:#e5edf7; }
        body.sn-dark .main,body.sn-dark .page { background:#111c2b; }
        body.sn-dark .topbar,body.sn-dark .panel,body.sn-dark .sn-top-menu,body.sn-dark .sn-search-results { background:#1b2a3d; color:#e5edf7; border-color:#304258; }
        body.sn-dark .search,body.sn-dark .top-button,body.sn-dark .top-select { background:#23354b; color:#e5edf7; border-color:#3a4d64; }
        body.sn-dark .sn-search-results a,body.sn-dark .sn-quick-link,body.sn-dark .sn-profile-trigger { color:#dbe7f4; }
        body.sn-dark .sn-search-results a:hover,body.sn-dark .sn-quick-link:hover { background:#2a4059; }
        body.sn-dark .sn-profile-summary strong,body.sn-dark .sn-notification-empty strong { color:#e5edf7; }
        body.sn-dark .sn-menu-heading,body.sn-dark .sn-profile-summary { border-color:#34485f; }
        @media(max-width:800px) {
            .sn-search-wrap { width:min(300px,48%); }
            .sn-context-label,.sn-profile-trigger-name,.sn-profile-trigger>i { display:none; }
            .sn-top-menu { right:-5px; }
        }
        @media(max-width:480px) {
            .sn-search-wrap { width:42%; }
            .sn-search-wrap kbd { display:none; }
            .sn-search-wrap .search { padding-left:32px; }
            .sn-profile-trigger { padding:0; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.sn-toast').forEach(function (toast) {
                setTimeout(function () {
                    if (!toast.isConnected) return;

                    toast.style.animation = 'snToastOut .3s ease forwards';

                    setTimeout(function () {
                        toast.remove();
                    }, 300);
                }, 4500);
            });
        });
    </script>


    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('snGlobalSearch');
        const results = document.getElementById('snSearchResults');
        const quickBtn = document.getElementById('snQuickBtn');
        const quickMenu = document.getElementById('snQuickMenu');
        const notificationBtn = document.getElementById('snNotificationBtn');
        const notificationMenu = document.getElementById('snNotificationMenu');
        const profileBtn = document.getElementById('snProfileBtn');
        const profileMenu = document.getElementById('snProfileMenu');
        const themeBtn = document.getElementById('snThemeBtn');

        // Search only links already rendered in the role-filtered sidebar.
        const pageLinks = Array.from(document.querySelectorAll('.sidebar a[href], .sidebar-navigation a[href], .sidebar-nav a[href]'))
            .filter(link => link.href && !link.href.endsWith('#'))
            .filter((link, index, list) => list.findIndex(item => item.href === link.href) === index)
            .map(link => ({ href: link.href, label: (link.innerText || link.textContent || '').trim().replace(/\s+/g, ' ') }))
            .filter(item => item.label.length > 1);

        function renderSearch(query) {
            const q = query.trim().toLowerCase();
            if (!q) { results.hidden = true; search.setAttribute('aria-expanded', 'false'); return; }
            const matches = pageLinks.filter(item => item.label.toLowerCase().includes(q)).slice(0, 8);
            results.innerHTML = '';
            if (!matches.length) {
                results.innerHTML = '<div class="sn-search-empty">No matching pages found in your available menu.</div>';
            } else {
                matches.forEach(item => {
                    const a = document.createElement('a');
                    a.href = item.href;
                    const icon = document.createElement('i');
                    icon.className = 'fa-solid fa-arrow-up-right-from-square';
                    const label = document.createElement('span');
                    label.textContent = item.label;
                    a.append(icon, label);
                    results.appendChild(a);
                });
            }
            results.hidden = false;
            search.setAttribute('aria-expanded', 'true');
        }
        search?.addEventListener('input', () => renderSearch(search.value));
        search?.addEventListener('focus', () => { if (search.value.trim()) renderSearch(search.value); });
        search?.addEventListener('keydown', e => {
            if (e.key === 'Escape') { results.hidden = true; search.blur(); }
            if (e.key === 'Enter') {
                const first = results.querySelector('a');
                if (first) window.location.href = first.href;
            }
        });
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault(); search?.focus();
            }
        });

        // Quick actions inherit the current user's sidebar permissions.
        const quickLinks = document.getElementById('snQuickLinks');
        pageLinks.slice(0, 6).forEach(item => {
            const a = document.createElement('a');
            a.className = 'sn-quick-link';
            a.href = item.href;
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-bolt';
            const label = document.createElement('span');
            label.textContent = item.label;
            a.append(icon, label);
            quickLinks?.appendChild(a);
        });
        if (quickLinks && !quickLinks.children.length) quickLinks.innerHTML = '<span class="sn-menu-empty">No shortcuts available.</span>';

        const dropdowns = [
            [quickBtn, quickMenu],
            [notificationBtn, notificationMenu],
            [profileBtn, profileMenu]
        ];
        function closeMenus(except) {
            dropdowns.forEach(([button, menu]) => {
                if (menu !== except) { menu.hidden = true; button?.setAttribute('aria-expanded', 'false'); }
            });
        }
        dropdowns.forEach(([button, menu]) => button?.addEventListener('click', e => {
            e.stopPropagation();
            const willOpen = menu.hidden;
            closeMenus(menu);
            menu.hidden = !willOpen;
            button.setAttribute('aria-expanded', String(willOpen));
        }));
        document.addEventListener('click', e => {
            if (!e.target.closest('.sn-top-dropdown') && !e.target.closest('.sn-profile-trigger')) closeMenus(null);
            if (!e.target.closest('.sn-search-wrap')) results.hidden = true;
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenus(null); });

        // Theme preference is saved in this browser.
        const savedTheme = localStorage.getItem('schoolnexa-theme');
        if (savedTheme === 'dark') document.body.classList.add('sn-dark');
        function updateThemeIcon() {
            if (!themeBtn) return;
            themeBtn.innerHTML = document.body.classList.contains('sn-dark')
                ? '<i class="fa-regular fa-sun"></i>'
                : '<i class="fa-regular fa-moon"></i>';
        }
        updateThemeIcon();
        themeBtn?.addEventListener('click', function () {
            document.body.classList.toggle('sn-dark');
            localStorage.setItem('schoolnexa-theme', document.body.classList.contains('sn-dark') ? 'dark' : 'light');
            updateThemeIcon();
        });
    });
    </script>

</body>

</html>