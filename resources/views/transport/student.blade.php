@extends('layouts.schoolnexa')

@section('title', 'My Transport')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Student - My Transport
    |--------------------------------------------------------------------------
    | Existing variables: $assignment, $student
    | Optional variables (pass from the controller when available):
    |   $busLocation       ['lat' => 12.97, 'lng' => 77.59, 'updated_at' => '2026-11-15 08:10:00', 'speed' => 32]
    |   $locationEndpoint  URL returning JSON {lat, lng, updated_at, speed}; polled every 15 s for live tracking
    |   $schoolAddress     destination used for the Route tab (defaults to the campus name)
    | Stop coordinates are read from $assignment->stop_latitude / stop_longitude when present.
    */
    $assignment = $assignment ?? null;
    $studentName = $student->full_name ?? $student->first_name ?? 'Student';
    $initials = collect(preg_split('/\s+/', trim($studentName)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');

    $busLocation = $busLocation ?? null;
    $locationEndpoint = $locationEndpoint ?? null;

    $busLat = data_get($busLocation, 'lat', data_get($assignment, 'bus_latitude', data_get($assignment, 'latitude')));
    $busLng = data_get($busLocation, 'lng', data_get($assignment, 'bus_longitude', data_get($assignment, 'longitude')));
    $busUpdated = data_get($busLocation, 'updated_at', data_get($assignment, 'location_updated_at'));
    $busSpeed = data_get($busLocation, 'speed');
    $hasBus = is_numeric($busLat) && is_numeric($busLng);

    $stopLat = data_get($assignment, 'stop_latitude');
    $stopLng = data_get($assignment, 'stop_longitude');
    $stopName = data_get($assignment, 'stop_name');
    $stopAddress = data_get($assignment, 'stop_address');
    $stopQuery = (is_numeric($stopLat) && is_numeric($stopLng))
        ? $stopLat . ',' . $stopLng
        : trim(($stopName ?? '') . ' ' . ($stopAddress ?? ''));
    $hasStop = $stopQuery !== '';

    $destination = $schoolAddress ?? session('campus_name');
    $hasRoute = $hasStop && filled($destination);

    $busQuery = $hasBus ? $busLat . ',' . $busLng : null;
    $embed = fn ($q) => 'https://www.google.com/maps?q=' . urlencode($q) . '&z=15&output=embed';
    $maps = [
        'bus'   => $hasBus ? ['embed' => $embed($busQuery), 'open' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($busQuery)] : null,
        'stop'  => $hasStop ? ['embed' => $embed($stopQuery), 'open' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($stopQuery)] : null,
        'route' => $hasRoute ? [
            'embed' => 'https://www.google.com/maps?saddr=' . urlencode($stopQuery) . '&daddr=' . urlencode($destination) . '&output=embed',
            'open'  => 'https://www.google.com/maps/dir/?api=1&origin=' . urlencode($stopQuery) . '&destination=' . urlencode($destination) . '&travelmode=driving',
        ] : null,
    ];
    $firstTab = $hasBus ? 'bus' : ($hasStop ? 'stop' : null);

    $pickupTime = $assignment?->pickup_time ? \Carbon\Carbon::parse($assignment->pickup_time) : null;
    $dropTime = $assignment?->drop_time ? \Carbon\Carbon::parse($assignment->drop_time) : null;
    $driverPhone = $assignment->driver_phone ?? null;
    $statusText = $assignment ? ucfirst($assignment->status) : 'Not Allocated';
    $isActive = $assignment && strtolower((string) $assignment->status) === 'active';
@endphp

<div class="st-page" id="stPage">

    {{-- ===== Header ===== --}}
    <div class="st-heading">
        <div>
            <span class="st-eyebrow">STUDENT SERVICES</span>
            <h1>My Transport</h1>
            <p>View your assigned bus, route, pickup point and driver information.</p>
        </div>
        <span class="st-status {{ $isActive ? 'active' : ($assignment ? 'warn' : 'inactive') }}">
            <i></i>{{ $statusText }}
        </span>
    </div>

    @if(session('success'))
        <div class="st-alert success">{{ session('success') }}</div>
    @endif

    @if(!$assignment)
        <div class="st-empty">
            <div class="st-empty-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="12" rx="3"/><path d="M3 11h18M7 16v3M17 16v3"/><circle cx="7.5" cy="13.5" r=".6"/><circle cx="16.5" cy="13.5" r=".6"/></svg>
            </div>
            <h2>No transport allocation found</h2>
            <p>Your school has not assigned a transport route to your profile yet.</p>
            <p>Please contact the transport department for assistance.</p>
        </div>
    @else

        {{-- ===== Student + live pickup countdown ===== --}}
        <div class="st-profile">
            <div class="st-person">
                <span class="st-avatar">{{ mb_strtoupper($initials ?: 'S') }}</span>
                <div>
                    <small>STUDENT</small>
                    <strong>{{ $studentName }}</strong>
                    <span>Admission No: {{ $student->admission_number ?? '—' }}</span>
                </div>
            </div>
            <div>
                <small>TRIP TYPE</small>
                <strong>{{ ucfirst($assignment->trip_type ?? 'Both') }}</strong>
                <span>Transport allocation</span>
            </div>
            <div>
                <small>NEXT PICKUP</small>
                <strong id="stCountdown" data-pickup="{{ $pickupTime ? $pickupTime->format('H:i') : '' }}" data-drop="{{ $dropTime ? $dropTime->format('H:i') : '' }}">
                    {{ $pickupTime ? $pickupTime->format('h:i A') : 'Not scheduled' }}
                </strong>
                <span id="stCountdownNote">Scheduled pickup time</span>
            </div>
        </div>

        {{-- ===== Summary cards ===== --}}
        <div class="st-grid">
            <div class="st-card tone-blue">
                <span class="st-card-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="12" rx="3"/><path d="M3 11h18M7 16v3M17 16v3"/><circle cx="7.5" cy="13.5" r=".6"/><circle cx="16.5" cy="13.5" r=".6"/></svg></span>
                <small>ASSIGNED BUS</small>
                <h2>{{ $assignment->registration_number ?? 'Not linked yet' }}</h2>
                <p>{{ $assignment->make_model ?? 'Vehicle details unavailable' }}</p>
            </div>

            <div class="st-card tone-teal">
                <span class="st-card-icon"><svg viewBox="0 0 24 24"><circle cx="6" cy="19" r="2.2"/><circle cx="18" cy="5" r="2.2"/><path d="M8 19h7a3.5 3.5 0 0 0 0-7H9a3.5 3.5 0 0 1 0-7h7"/></svg></span>
                <small>ROUTE NAME</small>
                <h2>{{ $assignment->route_name ?? '—' }}</h2>
                <p>{{ $assignment->route_code ?? 'Assigned transport route' }}</p>
            </div>

            <div class="st-card tone-amber">
                <span class="st-card-icon"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21Z"/><circle cx="12" cy="9.8" r="2.6"/></svg></span>
                <small>PICKUP POINT</small>
                <h2>{{ $assignment->stop_name ?? 'Not assigned' }}</h2>
                <p>{{ $assignment->stop_address ?? 'Pickup address unavailable' }}</p>
            </div>

            <div class="st-card tone-green">
                <span class="st-card-icon"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18M7 15h3"/></svg></span>
                <small>TRANSPORT FEE</small>
                <h2>₹{{ number_format((float)($assignment->fee_amount ?? 0), 2) }}</h2>
                <p>Assigned transport fee</p>
            </div>
        </div>

        {{-- ===== Journey + Driver ===== --}}
        <div class="st-split">
            <div class="st-panel">
                <h2>Route &amp; Pickup Schedule</h2>

                <div class="st-timeline">
                    <div class="st-step">
                        <span class="dot"></span>
                        <div>
                            <small>Pickup</small>
                            <strong>{{ $assignment->stop_name ?? 'Pickup point not assigned' }}</strong>
                            <span>{{ $pickupTime ? $pickupTime->format('h:i A') : 'Not scheduled' }}</span>
                        </div>
                    </div>
                    <div class="st-step">
                        <span class="dot end"></span>
                        <div>
                            <small>Drop</small>
                            <strong>{{ filled($destination) ? $destination : 'School campus' }}</strong>
                            <span>{{ $dropTime ? $dropTime->format('h:i A') : 'Not scheduled' }}</span>
                        </div>
                    </div>
                </div>

                <div class="st-details two">
                    <div><small>Route Distance</small><strong>{{ $assignment->distance_km !== null ? $assignment->distance_km . ' km' : '—' }}</strong></div>
                    <div><small>Allocation Start</small><strong>{{ $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date)->format('d M Y') : '—' }}</strong></div>
                </div>
            </div>

            <div class="st-panel">
                <h2>Driver Information</h2>

                <div class="st-driver">
                    <span class="st-avatar lg">{{ mb_strtoupper(mb_substr($assignment->driver_name ?? 'D', 0, 1)) }}</span>
                    <div>
                        <strong>{{ $assignment->driver_name ?? 'Not assigned' }}</strong>
                        <span>{{ $driverPhone ?? 'Contact not available' }}</span>
                    </div>
                </div>

                <div class="st-actions">
                    @if($driverPhone)
                        <a class="st-btn primary" href="tel:{{ preg_replace('/[^0-9+]/', '', $driverPhone) }}">
                            <svg viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>
                            Call driver
                        </a>
                        <button type="button" class="st-btn" data-copy="{{ $driverPhone }}">
                            <svg viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9"/></svg>
                            <span>Copy number</span>
                        </button>
                    @else
                        <span class="st-muted">Driver contact will appear here once assigned.</span>
                    @endif
                </div>

                <div class="st-details two">
                    <div><small>Vehicle Number</small><strong>{{ $assignment->registration_number ?? 'Not linked' }}</strong></div>
                    <div><small>Vehicle Type</small><strong>{{ $assignment->vehicle_type ?? '—' }}</strong></div>
                </div>
            </div>
        </div>

        {{-- ===== Google Map ===== --}}
        <div class="st-panel">
            <div class="st-panel-heading">
                <div>
                    <h2>Live Bus Tracking</h2>
                    <p>View your bus, pickup point and route on Google Maps.</p>
                </div>
                <span class="st-status {{ $hasBus ? 'active' : 'inactive' }}" id="stGpsBadge"><i></i><span>{{ $hasBus ? 'Live' : 'GPS not connected' }}</span></span>
            </div>

            @if($firstTab || $hasRoute)
                <div class="st-tabs" role="tablist">
                    <button type="button" class="st-tab" data-tab="bus" role="tab" @if(!$maps['bus']) disabled title="Bus GPS is not connected yet" @endif>Bus Location</button>
                    <button type="button" class="st-tab" data-tab="stop" role="tab" @if(!$maps['stop']) disabled @endif>Pickup Stop</button>
                    <button type="button" class="st-tab" data-tab="route" role="tab" @if(!$maps['route']) disabled title="Needs a pickup stop and a destination" @endif>Route to School</button>
                </div>

                <div class="st-map">
                    <iframe id="stMap" title="Google Map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    <div class="st-map-bar">
                        <span id="stMapMeta">
                            @if($hasBus && $busUpdated)
                                Last updated {{ \Carbon\Carbon::parse($busUpdated)->diffForHumans() }}@if($busSpeed !== null) &middot; {{ round($busSpeed) }} km/h @endif
                            @else
                                Showing the selected location
                            @endif
                        </span>
                        <a class="st-btn sm" id="stMapOpen" href="#" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                            Open in Google Maps
                        </a>
                    </div>
                </div>

                @unless($hasBus)
                    <p class="st-note">Live bus position appears once a GPS device or the driver tracking app is connected. Until then, the pickup stop and route are shown.</p>
                @endunless
            @else
                <div class="st-gps">
                    <div class="st-gps-icon"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21Z"/><circle cx="12" cy="9.8" r="2.6"/></svg></div>
                    <strong>Map is not available yet</strong>
                    <p>A pickup point or bus GPS location has not been added to your allocation.</p>
                </div>
            @endif
        </div>
    @endif
</div>

<style>
.st-page{--navy:#123B5D;--teal:#176B87;--line:#CBD5E1;--muted:#64748B;font-family:Inter,sans-serif;color:#25313C}
.st-page *{box-sizing:border-box}
.st-page svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.st-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:22px}
.st-eyebrow{font-size:10px;font-weight:700;letter-spacing:1px;color:var(--teal)}
.st-heading h1{font-size:23px;color:var(--navy);margin:6px 0}
.st-heading p,.st-panel-heading p{font-size:13px;color:var(--muted);margin:0}
.st-status{display:inline-flex;align-items:center;gap:7px;padding:7px 13px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.st-status i{width:7px;height:7px;border-radius:50%;background:currentColor}
.st-status.active{background:#DCFCE7;color:#166534}
.st-status.active i{animation:stPulse 1.8s infinite}
.st-status.warn{background:#FEF3C7;color:#92400E}
.st-status.inactive{background:#F1F5F9;color:var(--muted)}
@keyframes stPulse{0%{box-shadow:0 0 0 0 rgba(22,101,52,.45)}70%{box-shadow:0 0 0 7px rgba(22,101,52,0)}100%{box-shadow:0 0 0 0 rgba(22,101,52,0)}}

.st-profile,.st-card,.st-panel,.st-empty{background:#fff;border:1px solid var(--line);border-radius:13px}
.st-profile{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:20px;padding:18px 22px;margin-bottom:18px;align-items:center}
.st-profile small,.st-profile span,.st-details small{display:block;color:var(--muted);font-size:11px}
.st-profile strong{display:block;color:var(--navy);font-size:15px;margin:5px 0}
.st-person{display:flex;align-items:center;gap:13px}
.st-avatar{flex:0 0 auto;width:44px;height:44px;border-radius:50%;display:grid;place-items:center;background:#EAF1F8;color:var(--navy);font-weight:700;font-size:15px}
.st-avatar.lg{width:48px;height:48px}

.st-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
.st-card{padding:17px;transition:transform .15s,box-shadow .15s,border-color .15s}
.st-card:hover{transform:translateY(-3px);box-shadow:0 12px 26px rgba(18,59,93,.10);border-color:#9CC5D5}
.st-card-icon{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;margin-bottom:13px;background:var(--bg);color:var(--fg)}
.tone-blue{--bg:#E8F0FE;--fg:#1D4ED8}.tone-teal{--bg:#E3F4F6;--fg:#0F766E}.tone-amber{--bg:#FEF3E2;--fg:#B45309}.tone-green{--bg:#E4F6EA;--fg:#15803D}
.st-card small{font-size:10px;color:var(--muted);font-weight:700;letter-spacing:.04em}
.st-card h2{font-size:17px;color:var(--navy);margin:8px 0;overflow-wrap:anywhere}
.st-card p{font-size:11px;color:var(--muted);margin:0}

.st-split{display:grid;grid-template-columns:1.1fr 1fr;gap:17px;margin-bottom:17px}
.st-split .st-panel{margin-bottom:0}
.st-panel{padding:20px;margin-bottom:17px}
.st-panel h2{font-size:15px;color:var(--navy);margin:0 0 17px}
.st-details{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
.st-details.two{grid-template-columns:repeat(2,minmax(0,1fr));margin-top:20px;padding-top:18px;border-top:1px solid #EEF2F6}
.st-details strong{display:block;font-size:13px;color:#25313C;margin-top:7px;overflow-wrap:anywhere}

.st-timeline{position:relative;display:flex;flex-direction:column;gap:22px;padding-left:4px}
.st-timeline::before{content:'';position:absolute;left:10px;top:12px;bottom:14px;width:2px;background:repeating-linear-gradient(to bottom,#9CC5D5 0 5px,transparent 5px 9px)}
.st-step{position:relative;display:flex;gap:16px;align-items:flex-start}
.st-step .dot{flex:0 0 auto;width:14px;height:14px;margin-top:3px;margin-left:3px;border-radius:50%;background:#fff;border:3px solid var(--teal);z-index:1}
.st-step .dot.end{background:var(--navy);border-color:var(--navy)}
.st-step small{display:block;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)}
.st-step strong{display:block;font-size:14px;color:var(--navy);margin:3px 0}
.st-step span{font-size:12px;color:var(--muted)}

.st-driver{display:flex;align-items:center;gap:14px;margin-bottom:16px}
.st-driver strong{display:block;font-size:15px;color:var(--navy)}
.st-driver span{display:block;font-size:12px;color:var(--muted);margin-top:3px}
.st-actions{display:flex;gap:9px;flex-wrap:wrap;align-items:center}
.st-btn{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 14px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--navy);font:600 12px Inter,sans-serif;cursor:pointer;text-decoration:none;transition:.15s}
.st-btn svg{width:16px;height:16px}
.st-btn:hover{border-color:var(--teal);background:#F0F9FB;transform:translateY(-1px)}
.st-btn.primary{background:var(--navy);border-color:var(--navy);color:#fff}
.st-btn.primary:hover{background:#0F3250;color:#fff}
.st-btn.sm{height:34px;font-size:11px}
.st-btn.done{border-color:#16A34A;color:#166534;background:#F0FDF4}
.st-muted{font-size:12px;color:var(--muted)}

.st-panel-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:17px}
.st-panel-heading h2{margin-bottom:5px}
.st-tabs{display:inline-flex;gap:4px;padding:4px;background:#F1F5F9;border-radius:11px;margin-bottom:14px;flex-wrap:wrap}
.st-tab{border:0;background:transparent;padding:8px 15px;border-radius:8px;font:600 12px Inter,sans-serif;color:var(--muted);cursor:pointer;transition:.15s}
.st-tab:hover:not(:disabled){color:var(--navy)}
.st-tab.on{background:#fff;color:var(--teal);box-shadow:0 1px 4px rgba(15,23,42,.12)}
.st-tab:disabled{opacity:.45;cursor:not-allowed}
.st-map{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#F8FAFC}
.st-map iframe{display:block;width:100%;height:420px;border:0}
.st-map-bar{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 14px;border-top:1px solid var(--line);background:#fff;font-size:12px;color:var(--muted);flex-wrap:wrap}
.st-note{margin:12px 0 0;font-size:12px;color:var(--muted)}
.st-gps{text-align:center;background:#F8FAFC;border:1px dashed var(--line);border-radius:12px;padding:30px 15px}
.st-gps-icon{width:46px;height:46px;margin:0 auto 10px;border-radius:50%;display:grid;place-items:center;background:#E8F0F6;color:var(--teal)}
.st-gps strong{font-size:13px;color:var(--navy)}
.st-gps p{font-size:12px;color:var(--muted);margin:7px 0 0}

.st-empty{text-align:center;padding:55px 20px}
.st-empty-icon{width:64px;height:64px;margin:0 auto 14px;border-radius:50%;display:grid;place-items:center;background:#E8F0F6;color:var(--teal)}
.st-empty-icon svg{width:30px;height:30px}
.st-empty h2{color:var(--navy);font-size:18px}
.st-empty p{color:var(--muted);font-size:13px}
.st-alert{padding:12px;border-radius:8px;margin-bottom:15px}
.st-alert.success{background:#DCFCE7;color:#166534}

@@media(max-width:1000px){.st-grid{grid-template-columns:repeat(2,1fr)}.st-split{grid-template-columns:1fr}.st-profile{grid-template-columns:1fr 1fr}}
@@media(max-width:600px){.st-grid{grid-template-columns:1fr}.st-profile{grid-template-columns:1fr}.st-heading{align-items:flex-start;flex-direction:column}.st-map iframe{height:320px}.st-details{grid-template-columns:1fr 1fr}}
@@media(prefers-reduced-motion:reduce){.st-card,.st-btn{transition:none}.st-status.active i{animation:none}}
</style>

@if($assignment)
<script>
(function () {
    var MAPS = @json($maps);
    var ENDPOINT = @json($locationEndpoint);
    var frame = document.getElementById('stMap');
    var openLink = document.getElementById('stMapOpen');
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.st-tab'));
    var meta = document.getElementById('stMapMeta');
    var badge = document.getElementById('stGpsBadge');
    var current = null;

    function show(key) {
        if (!frame || !MAPS[key]) return;
        current = key;
        tabs.forEach(function (t) { t.classList.toggle('on', t.getAttribute('data-tab') === key); });
        frame.src = MAPS[key].embed;
        if (openLink) openLink.href = MAPS[key].open;
    }
    tabs.forEach(function (t) {
        t.addEventListener('click', function () { if (!t.disabled) show(t.getAttribute('data-tab')); });
    });
    show(@json($firstTab));

    /* Live bus position: poll the endpoint and refresh the Bus Location map */
    if (ENDPOINT) {
        var tick = function () {
            fetch(ENDPOINT, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
                .then(function (d) {
                    if (!d || isNaN(d.lat) || isNaN(d.lng)) return;
                    var q = d.lat + ',' + d.lng;
                    MAPS.bus = { embed: 'https://www.google.com/maps?q=' + encodeURIComponent(q) + '&z=16&output=embed',
                                 open: 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(q) };
                    var busTab = document.querySelector('.st-tab[data-tab="bus"]');
                    if (busTab) busTab.disabled = false;
                    if (current === 'bus' || current === null) show('bus');
                    if (badge) { badge.className = 'st-status active'; badge.querySelector('span').textContent = 'Live'; }
                    if (meta && current === 'bus') meta.textContent = 'Updated ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + (d.speed != null ? ' · ' + Math.round(d.speed) + ' km/h' : '');
                })
                .catch(function () {
                    if (badge) { badge.className = 'st-status inactive'; badge.querySelector('span').textContent = 'Signal lost'; }
                });
        };
        tick();
        setInterval(tick, 15000);
    }

    /* Pickup countdown */
    var cd = document.getElementById('stCountdown'), note = document.getElementById('stCountdownNote');
    function fmt12(hm) { var p = hm.split(':'), h = +p[0], m = p[1]; return ((h + 11) % 12 + 1) + ':' + m + ' ' + (h < 12 ? 'AM' : 'PM'); }
    function countdown() {
        if (!cd) return;
        var pick = cd.getAttribute('data-pickup'), drop = cd.getAttribute('data-drop');
        if (!pick) return;
        var now = new Date(), p = pick.split(':'), t = new Date(now.getFullYear(), now.getMonth(), now.getDate(), +p[0], +p[1]);
        var diff = Math.round((t - now) / 60000);
        cd.textContent = fmt12(pick);
        if (diff > 0) { var h = Math.floor(diff / 60), m = diff % 60; note.textContent = 'In ' + (h ? h + ' h ' : '') + m + ' min'; }
        else if (drop) { note.textContent = 'Pickup completed. Drop at ' + fmt12(drop); }
        else { note.textContent = 'Pickup time has passed today'; }
    }
    countdown(); setInterval(countdown, 30000);

    /* Copy driver number */
    document.querySelectorAll('[data-copy]').forEach(function (b) {
        b.addEventListener('click', function () {
            var txt = b.getAttribute('data-copy'), label = b.querySelector('span');
            var done = function () { b.classList.add('done'); if (label) label.textContent = 'Copied'; setTimeout(function () { b.classList.remove('done'); if (label) label.textContent = 'Copy number'; }, 1600); };
            if (navigator.clipboard) navigator.clipboard.writeText(txt).then(done, done); else done();
        });
    });
})();
</script>
@endif
@endsection