@extends('layouts.schoolnexa')

@section('content')

{{-- ===== 3D ILLUSTRATED ICON SPRITE (inline SVG, no emojis, no external files) ===== --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="lbgBlue"   x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7db4ff"/><stop offset="1" stop-color="#1d4ed8"/></linearGradient>
        <linearGradient id="lbgRed"    x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff8a8a"/><stop offset="1" stop-color="#d91f26"/></linearGradient>
        <linearGradient id="lbgGreen"  x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#6ee7a0"/><stop offset="1" stop-color="#15803d"/></linearGradient>
        <linearGradient id="lbgAmber"  x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe08a"/><stop offset="1" stop-color="#f59e0b"/></linearGradient>
        <linearGradient id="lbgOrange" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffb877"/><stop offset="1" stop-color="#ea580c"/></linearGradient>
        <linearGradient id="lbgPurple" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#b9a2ff"/><stop offset="1" stop-color="#5b21b6"/></linearGradient>
        <linearGradient id="lbgTeal"   x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#6ff0dc"/><stop offset="1" stop-color="#0f766e"/></linearGradient>
        <linearGradient id="lbgPaper"  x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#d6e4f5"/></linearGradient>
        <linearGradient id="lbgShine"  x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".55"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>
    </defs>

    {{-- Open book with red top (catalogue / titles) --}}
    <symbol id="lb-book" viewBox="0 0 64 64">
        <path d="M5 21 L32 10 L59 21 L59 26 L32 15 L5 26 Z" fill="url(#lbgRed)"/>
        <path d="M7 27 L30 18.5 L30 55 L7 48 Z" fill="url(#lbgPaper)" stroke="#4f8ff0" stroke-width="2.6" stroke-linejoin="round"/>
        <path d="M57 27 L34 18.5 L34 55 L57 48 Z" fill="url(#lbgPaper)" stroke="#4f8ff0" stroke-width="2.6" stroke-linejoin="round"/>
        <path d="M12 32 L25 27.5 M12 38 L25 33.5 M12 44 L25 39.5 M52 32 L39 27.5 M52 38 L39 33.5 M52 44 L39 39.5" stroke="#9db7d9" stroke-width="2.2" stroke-linecap="round"/>
    </symbol>

    {{-- Stack of books (total copies) --}}
    <symbol id="lb-stack" viewBox="0 0 64 64">
        <rect x="6"  y="42" width="50" height="14" rx="4" fill="url(#lbgBlue)"/>
        <rect x="10" y="46" width="42" height="4"  rx="2" fill="#fff" opacity=".75"/>
        <rect x="12" y="27" width="46" height="14" rx="4" fill="url(#lbgGreen)"/>
        <rect x="16" y="31" width="38" height="4"  rx="2" fill="#fff" opacity=".75"/>
        <rect x="8"  y="12" width="44" height="14" rx="4" fill="url(#lbgOrange)"/>
        <rect x="12" y="16" width="36" height="4"  rx="2" fill="#fff" opacity=".75"/>
        <rect x="8" y="12" width="44" height="6" rx="3" fill="url(#lbgShine)"/>
    </symbol>

    {{-- Green check badge (available) --}}
    <symbol id="lb-check" viewBox="0 0 64 64">
        <circle cx="32" cy="32" r="24" fill="url(#lbgGreen)"/>
        <ellipse cx="32" cy="18" rx="17" ry="9" fill="url(#lbgShine)"/>
        <path d="M20 33 L29 42 L45 24" stroke="#fff" stroke-width="6.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>

    {{-- Purple book with amber ribbon (currently issued) --}}
    <symbol id="lb-issued" viewBox="0 0 64 64">
        <rect x="11" y="7" width="42" height="50" rx="6" fill="url(#lbgPurple)"/>
        <rect x="11" y="7" width="9" height="50" rx="4" fill="#3b0f8c" opacity=".45"/>
        <rect x="48" y="11" width="7" height="42" rx="2.5" fill="url(#lbgPaper)"/>
        <path d="M34 7 V32 L40 27 L46 32 V7 Z" fill="url(#lbgAmber)"/>
        <rect x="11" y="7" width="42" height="14" rx="6" fill="url(#lbgShine)"/>
    </symbol>

    {{-- Red alarm clock (overdue) --}}
    <symbol id="lb-clock" viewBox="0 0 64 64">
        <circle cx="14" cy="14" r="8" fill="url(#lbgAmber)"/>
        <circle cx="50" cy="14" r="8" fill="url(#lbgAmber)"/>
        <circle cx="32" cy="36" r="23" fill="url(#lbgRed)"/>
        <circle cx="32" cy="36" r="16.5" fill="url(#lbgPaper)"/>
        <path d="M32 25 V36 L39 41" stroke="#d91f26" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <ellipse cx="32" cy="22" rx="15" ry="7" fill="url(#lbgShine)"/>
    </symbol>

    {{-- Green plus tile (add book) --}}
    <symbol id="lb-plus" viewBox="0 0 64 64">
        <rect x="8" y="8" width="48" height="48" rx="15" fill="url(#lbgGreen)"/>
        <rect x="8" y="8" width="48" height="24" rx="15" fill="url(#lbgShine)"/>
        <path d="M32 20 V44 M20 32 H44" stroke="#fff" stroke-width="6.5" stroke-linecap="round"/>
    </symbol>

    {{-- Blue paper-plane tile (issue a book) --}}
    <symbol id="lb-send" viewBox="0 0 64 64">
        <rect x="8" y="8" width="48" height="48" rx="15" fill="url(#lbgBlue)"/>
        <rect x="8" y="8" width="48" height="24" rx="15" fill="url(#lbgShine)"/>
        <path d="M17 31 L47 17 L38 47 L31 35 Z" fill="#fff"/>
        <path d="M31 35 L47 17" stroke="#7db4ff" stroke-width="2.5" stroke-linecap="round"/>
    </symbol>

    {{-- Clipboard (transactions) --}}
    <symbol id="lb-list" viewBox="0 0 64 64">
        <rect x="10" y="9" width="44" height="50" rx="7" fill="url(#lbgPurple)"/>
        <rect x="16" y="16" width="32" height="38" rx="4" fill="url(#lbgPaper)"/>
        <rect x="22" y="4" width="20" height="12" rx="5" fill="url(#lbgAmber)"/>
        <path d="M22 28 H42 M22 36 H42 M22 44 H33" stroke="#8aa3c4" stroke-width="3.2" stroke-linecap="round"/>
    </symbol>

    {{-- Orange return tile (manage returns) --}}
    <symbol id="lb-return" viewBox="0 0 64 64">
        <rect x="8" y="8" width="48" height="48" rx="15" fill="url(#lbgOrange)"/>
        <rect x="8" y="8" width="48" height="24" rx="15" fill="url(#lbgShine)"/>
        <path d="M26 21 L16 31 L26 41" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M18 31 H36 A10 10 0 0 1 46 41" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round"/>
    </symbol>
</svg>

<div class="sn-page">

    @include('library._styles')

    <style>
        /* ---------- Welcome Banner ---------- */
        .lbi-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
            padding: 34px 38px;
            margin-bottom: 20px;
            border-radius: 20px;
            border: 1px solid #DCE8F6;
            background: linear-gradient(120deg, #EAF2FC 0%, #EFF5FD 60%, #F4F8FE 100%);
        }

        .lbi-greet {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #176B87;
        }

        .lbi-dot { opacity: .8; }

        .lbi-welcome {
            font-size: 34px;
            font-weight: 800;
            color: #123B5D;
            margin: 14px 0 12px;
            line-height: 1.15;
            letter-spacing: -.5px;
        }

        .lbi-overview {
            font-size: 15px;
            color: #5B6B80;
            margin: 0;
        }

        .lbi-banner-right {
            display: flex;
            align-items: center;
            gap: 34px;
        }

        .lbi-banner-ico {
            width: 112px;
            height: 112px;
            filter: drop-shadow(0 14px 10px rgba(15,23,42,.18));
        }

        .lbi-clock { text-align: right; }
        .lbi-date { font-size: 15px; color: #475569; }
        .lbi-time { font-size: 32px; font-weight: 800; color: #123B5D; margin-top: 4px; letter-spacing: -.3px; }

        /* ---------- Illustrated 3D icons ---------- */
        .lbi-ico {
            display: block;
            flex: 0 0 auto;
            filter: drop-shadow(0 8px 6px rgba(15,23,42,.20));
            transition: transform .2s;
        }

        /* ---------- KPI cards ---------- */
        .lbi-kpi {
            display: flex;
            align-items: center;
            gap: 18px;
            transition: transform .15s, box-shadow .15s;
        }

        .lbi-kpi:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.08); }
        .lbi-kpi:hover .lbi-ico,
        .lbi-qa:hover .lbi-ico { transform: translateY(-3px) scale(1.06); }

        .lbi-kpi .lbi-ico { width: 62px; height: 62px; }

        .lbi-kpi .sn-label {
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #334155;
            margin: 0;
        }

        .lbi-kpi .sn-kpi { line-height: 1.1; margin: 4px 0 2px; }
        .lbi-kpi-note { font-size: 13.5px; color: #64748B; }

        /* ---------- Quick actions ---------- */
        .lbi-qa-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px;
            margin-top: 16px;
        }

        .lbi-qa {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 128px;
            padding: 16px 10px;
            border-radius: 16px;
            border: 1px solid #D5E2F1;
            background: #F7FAFE;
            color: #123B5D;
            font-family: Inter, sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            transition: transform .15s, box-shadow .15s, border-color .15s, background .15s;
        }

        .lbi-qa:hover {
            background: #fff;
            border-color: #176B87;
            color: #123B5D;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(23,107,135,.14);
        }

        .lbi-qa .lbi-ico { width: 52px; height: 52px; }

        /* ---------- Sections ---------- */
        .lbi-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .lbi-section-head .sn-section-title { margin: 0; }
        .lbi-book-title { color: #123B5D; font-weight: 600; }

        .lbi-table-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 12px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            color: #123B5D;
            background: #fff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .lbi-table-action:hover { background: #F8FAFC; color: #123B5D; }

        @media (max-width: 760px) {
            .lbi-banner { padding: 24px 22px; }
            .lbi-welcome { font-size: 26px; }
            .lbi-banner-right { width: 100%; justify-content: space-between; gap: 16px; }
            .lbi-banner-ico { width: 80px; height: 80px; }
            .lbi-clock { text-align: right; }
            .lbi-time { font-size: 26px; }
        }
    </style>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="sn-alert">{{ session('success') }}</div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="sn-alert sn-error">
            <ul style="margin:0;padding-left:18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- WELCOME BANNER --}}
    <div class="lbi-banner">
        <div>
            <div class="lbi-greet">
                <span id="lbGreetText">Good Afternoon</span>
                <span class="lbi-dot">&bull;</span>
                <span>Platform Administrator</span>
            </div>

            <div class="lbi-welcome">Welcome Back, Super Admin!</div>

            <p class="lbi-overview">Here's an overview of your library management system.</p>
        </div>

        <div class="lbi-banner-right">
            <svg class="lbi-banner-ico" viewBox="0 0 64 64" aria-hidden="true"><use href="#lb-book"/></svg>

            <div class="lbi-clock">
                <div class="lbi-date" id="lbClockDate">{{ now()->format('l, d M Y') }}</div>
                <div class="lbi-time" id="lbClockTime">{{ now()->format('h:i A') }}</div>
            </div>
        </div>
    </div>

    {{-- PAGE HEADER --}}
    <div class="sn-head">
        <div>
            <h1 class="sn-title">Library Dashboard</h1>
            <div class="sn-sub">Books, circulation and overdue activity</div>
        </div>

        <div class="sn-actions">
            <a href="{{ route('library.books.index') }}" class="sn-btn sn-btn-outline">Book Catalogue</a>
            <a href="{{ route('library.loans.issue') }}" class="sn-btn sn-btn-primary">+ Issue a Book</a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="sn-grid">
        @foreach([
            ['Book Titles',      $bookCount, 'lb-book',   'Titles in catalogue'],
            ['Total Copies',     $copyCount, 'lb-stack',  'Physical copies'],
            ['Available Copies', $available, 'lb-check',  'Ready to issue'],
            ['Currently Issued', $issued,    'lb-issued', 'Currently on loan'],
            ['Overdue',          $overdue,   'lb-clock',  'Past due date'],
        ] as [$label, $value, $icon, $note])

            <div class="sn-kpi-card lbi-kpi">
                <svg class="lbi-ico" viewBox="0 0 64 64" aria-hidden="true"><use href="#{{ $icon }}"/></svg>

                <div>
                    <div class="sn-label">{{ $label }}</div>
                    <div class="sn-kpi" data-count="{{ (int) $value }}">{{ number_format($value) }}</div>
                    <div class="lbi-kpi-note">{{ $note }}</div>
                </div>
            </div>

        @endforeach
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="sn-card">

        <h2 class="sn-section-title" style="margin:0">Quick Actions</h2>

        <div class="lbi-qa-grid">

            <a class="lbi-qa" href="{{ route('library.books.index') }}">
                <svg class="lbi-ico" viewBox="0 0 64 64" aria-hidden="true"><use href="#lb-book"/></svg>
                Book Catalogue
            </a>

            <a class="lbi-qa" href="{{ route('library.books.create') }}">
                <svg class="lbi-ico" viewBox="0 0 64 64" aria-hidden="true"><use href="#lb-plus"/></svg>
                Add Book
            </a>

            <a class="lbi-qa" href="{{ route('library.loans.issue') }}">
                <svg class="lbi-ico" viewBox="0 0 64 64" aria-hidden="true"><use href="#lb-send"/></svg>
                Issue a Book
            </a>

            <a class="lbi-qa" href="{{ route('library.loans.index') }}">
                <svg class="lbi-ico" viewBox="0 0 64 64" aria-hidden="true"><use href="#lb-list"/></svg>
                All Transactions
            </a>

            <a class="lbi-qa" href="{{ route('library.loans.index', ['status' => 'issued']) }}">
                <svg class="lbi-ico" viewBox="0 0 64 64" aria-hidden="true"><use href="#lb-return"/></svg>
                Manage Returns
            </a>

        </div>

    </div>

    {{-- RECENT TRANSACTIONS --}}
    <div class="sn-card">

        <div class="lbi-section-head">
            <h2 class="sn-section-title">Recent Library Transactions</h2>

            <a class="lbi-table-action" href="{{ route('library.loans.index') }}">View All</a>
        </div>

        <div class="sn-table-wrap">

            <table class="sn-table">

                <thead>
                    <tr>
                        <th>BOOK</th>
                        <th>BORROWER</th>
                        <th>ISSUED DATE</th>
                        <th>DUE DATE</th>
                        <th>STATUS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($recentLoans as $loan)

                        <tr>

                            <td>
                                <div class="lbi-book-title">{{ $loan->copy->book->title ?? '—' }}</div>
                                <div class="sn-muted">Accession: {{ $loan->copy->accession_number ?? '—' }}</div>
                            </td>

                            <td>
                                @if($loan->student)
                                    {{ trim($loan->student->first_name.' '.$loan->student->last_name) }}
                                    <div class="sn-muted">Student</div>
                                @elseif($loan->staff)
                                    {{ trim($loan->staff->first_name.' '.$loan->staff->last_name) }}
                                    <div class="sn-muted">Staff</div>
                                @else
                                    —
                                @endif
                            </td>

                            <td>{{ optional($loan->issued_at)->format('d M Y') ?? '—' }}</td>

                            <td>{{ optional($loan->due_at)->format('d M Y') ?? '—' }}</td>

                            <td><span class="sn-badge">{{ ucfirst($loan->status) }}</span></td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="sn-empty">No library transactions found.</td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

{{-- LIVE CLOCK AND KPI ANIMATION --}}
<script>
(function () {

    function tick() {

        const date = new Date();
        const hour = date.getHours();

        const greeting = document.getElementById('lbGreetText');
        const clockDate = document.getElementById('lbClockDate');
        const clockTime = document.getElementById('lbClockTime');

        if (!greeting || !clockDate || !clockTime) {
            return;
        }

        greeting.textContent =
            hour < 12 ? 'Good Morning' :
            hour < 17 ? 'Good Afternoon' :
            'Good Evening';

        const weekday = date.toLocaleDateString('en-GB', { weekday: 'long' });

        const formattedDate = date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });

        clockDate.textContent = weekday + ', ' + formattedDate;

        clockTime.textContent = date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    tick();
    setInterval(tick, 15000);

    // KPI count-up animation
    document.querySelectorAll('[data-count]').forEach(function (element) {

        const target = parseInt(element.dataset.count, 10) || 0;

        if (target === 0) {
            return;
        }

        let start = null;
        const duration = 700;

        function animate(timestamp) {

            if (!start) {
                start = timestamp;
            }

            const progress = Math.min((timestamp - start) / duration, 1);
            const value = Math.round(target * (1 - Math.pow(1 - progress, 3)));

            element.textContent = value.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }

        requestAnimationFrame(animate);

    });

})();
</script>

@endsection