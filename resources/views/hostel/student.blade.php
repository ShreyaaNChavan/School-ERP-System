@extends('layouts.schoolnexa')

@section('title', 'My Hostel')

@section('content')
@php
    use Illuminate\Support\Carbon;

    $allocation  = $allocation ?? null;
    $studentName = $student->full_name ?? $student->first_name ?? 'Student';

    /* ---------- 3D icon helpers (icons8 "3d-fluency"; falls back to a drawn icon if the picture can't load) ---------- */
    $paths = [
        'hostel'   => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6M7 11h2M15 11h2"/>',
        'room'     => '<rect x="5" y="3" width="14" height="18" rx="1.5"/><circle cx="15" cy="12" r="1"/>',
        'bed'      => '<path d="M3 18V7M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/><circle cx="7" cy="11" r="1.8"/>',
        'type'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 10v10"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
        'phone'    => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2z"/>',
        'pin'      => '<path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'people'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1.5A4.5 4.5 0 0 1 7 14h4a4.5 4.5 0 0 1 4.5 4.5V20"/><path d="M17 4.5a3.5 3.5 0 0 1 0 7M21.5 20v-1.5A4.5 4.5 0 0 0 18.5 14.3"/>',
    ];
    $slugs = [
        'hostel' => 'bed', 'room' => 'key', 'bed' => 'bed', 'type' => 'organization', 'calendar' => 'calendar',
        'user' => 'user', 'phone' => 'phone', 'pin' => 'marker', 'clock' => 'clock', 'people' => 'conference-call',
    ];
    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];
    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($paths, $slugs, $ramp) {
        [$a, $b, $c] = $ramp[$tone] ?? $ramp['sky'];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';
        $img   = $slug ? '<img class="hsi-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.hsi-3d\').classList.add(\'fb\')">' : '';
        $svg   = isset($paths[$name]) ? '<svg xmlns="http://www.w3.org/2000/svg" width="'.$px.'" height="'.$px.'" viewBox="0 0 24 24" aria-hidden="true">'.$paths[$name].'</svg>' : '';
        $fb    = '<span class="hsi-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$svg.'</span>';
        return '<span class="hsi-3d '.$size.($slug ? '' : ' fb').'"><span class="hsi-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    /* ---------- status ---------- */
    $statusRaw   = strtolower((string) ($allocation->status ?? ''));
    $statusLabel = $allocation ? ucfirst($allocation->status ?? 'active') : 'Not Allocated';
    $statusTone  = !$allocation ? 'none'
        : ((str_contains($statusRaw, 'inactive') || str_contains($statusRaw, 'deactiv') || str_contains($statusRaw, 'vacat') || str_contains($statusRaw, 'cancel') || str_contains($statusRaw, 'expire') || str_contains($statusRaw, 'terminat')) ? 'inactive'
        : ((str_contains($statusRaw, 'pending') || str_contains($statusRaw, 'request')) ? 'pending' : 'active'));

    /* ---------- dates / stay progress ---------- */
    $parse = function ($v) { try { return $v ? Carbon::parse($v) : null; } catch (\Throwable $e) { return null; } };
    $start = $parse($allocation->start_date ?? null);
    $end   = $parse($allocation->end_date ?? null);
    $now   = now();
    $daysStayed = $start ? max(0, (int) $start->copy()->startOfDay()->diffInDays($now->copy()->startOfDay(), false)) : null;
    $stayPct = null; $daysLeft = null;
    if ($start && $end && $end->gt($start)) {
        $stayPct  = max(0, min(100, ($now->timestamp - $start->timestamp) / ($end->timestamp - $start->timestamp) * 100));
        $daysLeft = max(0, (int) $now->copy()->startOfDay()->diffInDays($end->copy()->startOfDay(), false));
    }

    /* ---------- room / bed visual ---------- */
    $capRaw   = $allocation->room_capacity ?? null;
    $capacity = is_numeric($capRaw) ? max(1, min(12, (int) $capRaw)) : null;
    $bedRaw   = trim((string) ($allocation->bed_number ?? ''));
    $myBed    = null;
    if ($capacity) {
        if (ctype_digit($bedRaw) && (int) $bedRaw > 0) { $myBed = (($bedRaw - 1) % $capacity) + 1; }
        elseif (preg_match('/^[A-Za-z]$/', $bedRaw))    { $myBed = ((ord(strtoupper($bedRaw)) - 65) % $capacity) + 1; }
        elseif (preg_match('/(\d+)\s*$/', $bedRaw, $m))  { $myBed = (((int) $m[1] - 1) % $capacity) + 1; }
    }

    /* ---------- map (Google Maps embed, no API key) ---------- */
    $address = trim((string) ($allocation->hostel_address ?? ''));
    $lat     = $allocation->latitude  ?? $allocation->hostel_latitude  ?? null;
    $lng     = $allocation->longitude ?? $allocation->hostel_longitude ?? null;
    $query   = (is_numeric($lat) && is_numeric($lng))
        ? $lat.','.$lng
        : trim(($allocation->hostel_name ?? '').' '.$address);
    $hasMap  = $allocation && (is_numeric($lat) && is_numeric($lng) || $address !== '');
    $embed   = 'https://www.google.com/maps?q='.rawurlencode($query).'&z=16&output=embed';
    $openUrl = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($query);
    $dirUrl  = 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($query);

    $wardenPhone = trim((string) ($allocation->warden_phone ?? ''));
    $telHref     = $wardenPhone !== '' ? 'tel:'.preg_replace('/[^0-9+]/', '', $wardenPhone) : null;

    $initials = collect(preg_split('/\s+/', trim($studentName)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');

    $summaryText = $allocation
        ? "Hostel: ".($allocation->hostel_name ?? '—')."\nRoom: ".($allocation->room_number ?? '—')."\nBed: ".($allocation->bed_number ?? '—')."\nRoom type: ".($allocation->room_type ?? '—')
          ."\nWarden: ".($allocation->warden_name ?? 'Not provided')." (".($wardenPhone !== '' ? $wardenPhone : 'no contact').")\nAddress: ".($address !== '' ? $address : 'Not provided')
        : '';
@endphp

<div class="hs-page">

    {{-- ================= HEADER ================= --}}
    <div class="hs-heading">
        <div>
            <span class="hs-eyebrow">STUDENT SERVICES</span>
            <h1>My Hostel</h1>
            <p>View your hostel, room, bed allocation and accommodation details.</p>
        </div>
        <span class="hs-status {{ $statusTone }}"><i></i>{{ $statusLabel }}</span>
    </div>

    @if(!$allocation)

        <div class="hs-empty">
            {!! $tile('hostel', 'sky', 'lg') !!}
            <h2>No hostel allocation found</h2>
            <p>You currently do not have an active hostel accommodation allocation.</p>
            <p>Contact the hostel administration for assistance.</p>
        </div>

    @else

        {{-- ================= BANNER ================= --}}
        <div class="hs-banner" data-tilt>
            <div class="hs-banner-main">
                <div class="hs-av">{{ $initials ?: 'S' }}</div>
                <div>
                    <small>STUDENT</small>
                    <strong>{{ $studentName }}</strong>
                    <span>Admission No: {{ $student->admission_number ?? '—' }}</span>
                </div>
            </div>
            <div class="hs-banner-side">
                <div>
                    <small>HOSTEL</small>
                    <strong>{{ $allocation->hostel_name ?? '—' }}</strong>
                    <span>{{ $allocation->hostel_type ?? 'Hostel accommodation' }}</span>
                </div>
                {!! $tile('hostel', 'sky', 'lg') !!}
            </div>
        </div>

        {{-- ================= KEY FACTS ================= --}}
        <div class="hs-grid">
            @foreach([
                ['HOSTEL NAME', $allocation->hostel_name ?? '—', 'hostel', 'navy', null],
                ['ROOM NUMBER', $allocation->room_number ?? '—', 'room',   'teal', $allocation->room_number ?? null],
                ['BED NUMBER',  $allocation->bed_number ?? '—',  'bed',    'purple', $allocation->bed_number ?? null],
                ['ROOM TYPE',   $allocation->room_type ?? '—',   'type',   'warning', null],
            ] as [$label, $value, $ic, $tone, $copy])
                <div class="hs-card" data-tilt>
                    {!! $tile($ic, $tone) !!}
                    <div class="hs-card-body">
                        <small>{{ $label }}</small>
                        <strong>{{ $value }}</strong>
                    </div>
                    @if($copy)
                        <button type="button" class="hs-copy" data-copy="{{ $copy }}" aria-label="Copy {{ strtolower($label) }}" title="Copy">
                            <svg viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9"/></svg>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- ================= STAY + ROOM VISUAL ================= --}}
        <div class="hs-two">

            <div class="hs-panel">
                <h2 class="hs-h">{!! $tile('calendar', 'sky', 'xs') !!}Allocation Details</h2>

                <div class="hs-details">
                    <div><small>Allocation Status</small><strong>{{ ucfirst($allocation->status ?? 'active') }}</strong></div>
                    <div><small>Start Date</small><strong>{{ $start ? $start->format('d M Y') : '—' }}</strong></div>
                    <div><small>End Date</small><strong>{{ $end ? $end->format('d M Y') : 'Currently active' }}</strong></div>
                    <div><small>Academic Year</small><strong>{{ $allocation->academic_year_name ?? '—' }}</strong></div>
                </div>

                @if($start)
                    <div class="hs-stay">
                        <div class="hs-stay-top">
                            <span>Stay progress</span>
                            <b>
                                @if($stayPct !== null){{ number_format($stayPct, 0) }}% complete @else Since {{ $start->format('d M Y') }} @endif
                            </b>
                        </div>
                        <div class="hs-track"><i data-w="{{ $stayPct !== null ? round($stayPct, 1) : 100 }}" class="{{ $stayPct === null ? 'open' : '' }}"></i></div>
                        <div class="hs-stay-foot">
                            <span>{{ number_format($daysStayed ?? 0) }} day{{ ($daysStayed ?? 0) === 1 ? '' : 's' }} stayed</span>
                            <span>@if($daysLeft !== null){{ number_format($daysLeft) }} day{{ $daysLeft === 1 ? '' : 's' }} remaining @else No end date set @endif</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="hs-panel">
                <h2 class="hs-h">{!! $tile('people', 'teal', 'xs') !!}Your Room</h2>

                @if($capacity)
                    <div class="hs-room-meta">
                        <span>Room {{ $allocation->room_number ?? '—' }}</span>
                        <span>{{ $capacity }} bed{{ $capacity === 1 ? '' : 's' }}</span>
                    </div>
                    <div class="hs-beds" style="--n:{{ min($capacity, 4) }}">
                        @for($i = 1; $i <= $capacity; $i++)
                            <div class="hs-bed {{ $myBed === $i ? 'mine' : '' }}" title="{{ $myBed === $i ? 'Your bed' : 'Bed '.$i }}">
                                <svg viewBox="0 0 48 32" aria-hidden="true">
                                    <rect x="2" y="4" width="3" height="24" rx="1.5"/>
                                    <rect x="5" y="14" width="41" height="9" rx="2"/>
                                    <rect x="9" y="8" width="11" height="6" rx="3"/>
                                    <rect x="43" y="14" width="3" height="14" rx="1.5"/>
                                </svg>
                                <span>{{ $myBed === $i ? 'Your bed' : 'Bed '.$i }}</span>
                            </div>
                        @endfor
                    </div>
                    <p class="hs-note">Layout is illustrative. Occupancy of other beds is not shown.</p>
                @else
                    <div class="hs-details one">
                        <div><small>Room Capacity</small><strong>{{ $allocation->room_capacity ?? '—' }}</strong></div>
                        <div><small>Room Type</small><strong>{{ $allocation->room_type ?? '—' }}</strong></div>
                    </div>
                    <p class="hs-note">Room capacity has not been provided, so a bed layout can't be drawn.</p>
                @endif
            </div>
        </div>

        {{-- ================= CONTACT ================= --}}
        <div class="hs-panel">
            <div class="hs-panel-head">
                <h2 class="hs-h">{!! $tile('user', 'success', 'xs') !!}Hostel Contact Information</h2>
                <div class="hs-actions">
                    @if($telHref)<a class="hs-btn primary" href="{{ $telHref }}">Call Warden</a>@endif
                    <button type="button" class="hs-btn" id="hsCopyAll" data-copy="{{ $summaryText }}">Copy details</button>
                </div>
            </div>

            <div class="hs-details">
                <div><small>Warden</small><strong>{{ $allocation->warden_name ?? 'Not provided' }}</strong></div>
                <div>
                    <small>Warden Contact</small>
                    <strong>
                        {{ $wardenPhone !== '' ? $wardenPhone : 'Not provided' }}
                        @if($wardenPhone !== '')<button type="button" class="hs-copy sm" data-copy="{{ $wardenPhone }}" aria-label="Copy phone number" title="Copy"><svg viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9"/></svg></button>@endif
                    </strong>
                </div>
                <div><small>Hostel Address</small><strong>{{ $address !== '' ? $address : 'Not provided' }}</strong></div>
                <div><small>Room Capacity</small><strong>{{ $allocation->room_capacity ?? '—' }}</strong></div>
            </div>
        </div>

        {{-- ================= GOOGLE MAP ================= --}}
        <div class="hs-panel">
            <div class="hs-panel-head">
                <h2 class="hs-h">{!! $tile('pin', 'warning', 'xs') !!}Hostel Location</h2>
                @if($hasMap)
                    <div class="hs-actions">
                        <div class="hs-seg" id="hsMapType">
                            <button type="button" class="on" data-t="m">Map</button>
                            <button type="button" data-t="k">Satellite</button>
                        </div>
                        <a class="hs-btn" href="{{ $openUrl }}" target="_blank" rel="noopener noreferrer">Open in Google Maps</a>
                        <a class="hs-btn primary" href="{{ $dirUrl }}" target="_blank" rel="noopener noreferrer">Get Directions</a>
                    </div>
                @endif
            </div>

            @if($hasMap)
                <div class="hs-map" id="hsMapBox">
                    <div class="hs-map-load" id="hsMapLoad"><span></span>Loading map</div>
                    <iframe id="hsMap"
                            title="Hostel location on Google Maps"
                            src="{{ $embed }}"
                            loading="lazy"
                            allowfullscreen
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <p class="hs-note">{{ $allocation->hostel_name ?? 'Hostel' }}@if($address !== '') · {{ $address }}@endif</p>
            @else
                <div class="hs-empty small">
                    <h2>Location not available</h2>
                    <p>The hostel address has not been provided yet, so the map can't be shown.</p>
                </div>
            @endif
        </div>

    @endif
</div>

<div class="hs-toast" id="hsToast" role="status" aria-live="polite"></div>

<style>
.hs-page{--navy:#123B5D;--teal:#176B87;--muted:#64748B;--line:#CBD5E1;font-family:Inter,sans-serif;color:#25313C;display:flex;flex-direction:column;gap:18px}
.hs-page *{box-sizing:border-box}
.hs-page h1,.hs-page h2,.hs-page p{margin:0}

.hs-heading{display:flex;justify-content:space-between;align-items:center;gap:15px}
.hs-eyebrow{font-size:10px;font-weight:700;letter-spacing:1px;color:var(--teal)}
.hs-heading h1{font-size:23px;color:var(--navy);margin:6px 0 !important}
.hs-heading p{font-size:13px;color:var(--muted)}
.hs-status{display:inline-flex;align-items:center;gap:7px;padding:7px 13px;border-radius:20px;font-size:11px;font-weight:700}
.hs-status i{width:7px;height:7px;border-radius:50%;background:currentColor}
.hs-status.active{background:#DCFCE7;color:#166534}
.hs-status.inactive{background:#FEE2E2;color:#DC2626}
.hs-status.pending{background:#FEF3C7;color:#92400E}
.hs-status.none{background:#F1F5F9;color:var(--muted)}

.hs-banner,.hs-card,.hs-panel,.hs-empty{background:#fff;border:1px solid var(--line);border-radius:14px}
.hs-banner{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;padding:22px 24px;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);border-color:#DCE8F7}
.hs-banner-main,.hs-banner-side{display:flex;align-items:center;gap:16px;min-width:0}
.hs-banner small,.hs-banner span,.hs-details small{display:block;font-size:11px;color:var(--muted)}
.hs-banner small{letter-spacing:.06em;font-weight:700}
.hs-banner strong{display:block;font-size:17px;color:var(--navy);margin:4px 0}
.hs-av{width:52px;height:52px;border-radius:50%;display:grid;place-items:center;flex:0 0 auto;background:#fff;border:2px solid #fff;box-shadow:0 3px 10px rgba(18,59,93,.15);color:var(--navy);font-weight:800;font-size:17px}

.hs-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.hs-card{display:flex;align-items:center;gap:14px;padding:18px;position:relative;transition:transform .15s,box-shadow .15s}
.hs-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(18,59,93,.12)}
.hs-card-body{min-width:0;flex:1}
.hs-card small{display:block;font-size:10px;font-weight:700;letter-spacing:.05em;color:var(--muted)}
.hs-card strong{display:block;font-size:17px;color:var(--navy);margin-top:6px;overflow-wrap:anywhere}

.hs-two{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,1fr);gap:16px;align-items:stretch}
.hs-panel{padding:20px;min-width:0}
.hs-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.hs-panel h2{font-size:15px;color:var(--navy)}
.hs-two .hs-panel > .hs-h{margin-bottom:18px}
.hs-h{display:flex;align-items:center;gap:10px}
.hs-h .hsi-3d{margin-bottom:6px}
.hs-details{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
.hs-two .hs-details{grid-template-columns:repeat(2,minmax(0,1fr))}
.hs-details.one{grid-template-columns:repeat(2,minmax(0,1fr))}
.hs-details strong{display:flex;align-items:center;gap:6px;font-size:13px;color:#25313C;margin-top:7px;overflow-wrap:anywhere}

.hs-stay{margin-top:22px;padding-top:18px;border-top:1px solid #EEF2F6}
.hs-stay-top,.hs-stay-foot{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:var(--muted)}
.hs-stay-top b{color:var(--navy)}
.hs-track{height:9px;border-radius:99px;background:#E2E8F0;overflow:hidden;margin:9px 0}
.hs-track i{display:block;height:100%;width:0;border-radius:99px;background:linear-gradient(90deg,#176B87,#3B82F6);transition:width 1s cubic-bezier(.2,.8,.2,1)}
.hs-track i.open{background:repeating-linear-gradient(135deg,#16A34A 0 10px,#22C55E 10px 20px)}

.hs-room-meta{display:flex;gap:8px;margin-bottom:14px}
.hs-room-meta span{font-size:11px;font-weight:700;padding:5px 11px;border-radius:99px;background:#F1F5F9;color:var(--navy)}
.hs-beds{display:grid;grid-template-columns:repeat(var(--n,2),minmax(0,1fr));gap:12px}
.hs-bed{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px 10px;border:1px solid #E2E8F0;border-radius:12px;background:#F8FAFC;transition:.15s}
.hs-bed svg{width:62px;height:42px;fill:#CBD5E1}
.hs-bed span{font-size:11px;font-weight:600;color:var(--muted)}
.hs-bed:hover{transform:translateY(-2px)}
.hs-bed.mine{border-color:var(--teal);background:#F0F9FB;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
.hs-bed.mine svg{fill:var(--teal)}
.hs-bed.mine span{color:var(--teal);font-weight:800}
.hs-note{margin-top:12px;font-size:11px;color:var(--muted)}

.hs-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.hs-btn{display:inline-flex;align-items:center;height:36px;padding:0 14px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--navy);font:600 12px Inter,sans-serif;text-decoration:none;cursor:pointer;transition:.15s}
.hs-btn:hover{border-color:var(--teal);background:#F0F9FF}
.hs-btn.primary{background:var(--navy);color:#fff;border-color:var(--navy)}
.hs-btn.primary:hover{background:#0F3250}
.hs-seg{display:inline-flex;padding:3px;border-radius:10px;background:#F1F5F9}
.hs-seg button{border:0;background:transparent;padding:6px 13px;border-radius:8px;font:700 11px Inter,sans-serif;color:var(--muted);cursor:pointer}
.hs-seg button.on{background:#fff;color:var(--teal);box-shadow:0 1px 4px rgba(15,23,42,.1)}

.hs-copy{flex:0 0 auto;width:30px;height:30px;display:grid;place-items:center;border:1px solid #E2E8F0;border-radius:8px;background:#fff;cursor:pointer;opacity:0;transition:.15s}
.hs-card:hover .hs-copy,.hs-copy:focus-visible,.hs-copy.sm{opacity:1}
.hs-copy.sm{width:24px;height:24px;border-radius:7px;margin-left:2px}
.hs-copy svg{width:14px;height:14px;fill:none;stroke:var(--muted);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.hs-copy:hover{border-color:var(--teal)}
.hs-copy:hover svg{stroke:var(--teal)}

.hs-map{position:relative;height:420px;border-radius:12px;overflow:hidden;border:1px solid #E2E8F0;background:#EEF2F6}
.hs-map iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.hs-map-load{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;gap:10px;font-size:12px;color:var(--muted);transition:opacity .3s}
.hs-map-load span{width:16px;height:16px;border-radius:50%;border:2px solid #CBD5E1;border-top-color:var(--teal);animation:hsSpin .8s linear infinite}
.hs-map-load.done{opacity:0;pointer-events:none}
@keyframes hsSpin{to{transform:rotate(360deg)}}

.hs-empty{display:flex;flex-direction:column;align-items:center;text-align:center;padding:50px 20px;gap:6px}
.hs-empty.small{padding:30px 20px;border:0}
.hs-empty h2{font-size:18px;color:var(--navy);margin-top:10px}
.hs-empty p{font-size:13px;color:var(--muted)}

.hs-toast{position:fixed;left:50%;bottom:28px;transform:translateX(-50%) translateY(20px);background:var(--navy,#123B5D);color:#fff;font:600 13px Inter,sans-serif;padding:10px 18px;border-radius:10px;opacity:0;pointer-events:none;transition:.25s;z-index:9999}
.hs-toast.show{opacity:1;transform:translateX(-50%)}

/* 3D icons */
.hsi-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:56px;height:56px;transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));transition:transform .18s ease-out;will-change:transform;cursor:pointer}
.hsi-3d.xs{width:38px;height:38px;margin-bottom:6px}
.hsi-3d.lg{width:84px;height:84px;margin-bottom:10px}
.hsi-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:hsiFloat 4.2s ease-in-out infinite}
.hsi-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
.hsi-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
.hsi-ico{display:none}
.hsi-3d.fb .hsi-3d-img{display:none}
.hsi-3d.fb .hsi-ico{position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
.hsi-3d.fb .hsi-ico svg{fill:none;stroke:#fff;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.hsi-3d.pop .hsi-3d-in{animation:hsiPop .55s cubic-bezier(.3,1.6,.5,1),hsiFloat 4.2s ease-in-out infinite .55s}
@keyframes hsiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
@keyframes hsiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
@@media (prefers-reduced-motion:reduce){.hsi-3d-in{animation:none!important}.hsi-3d,.hs-track i{transition:none}.hs-map-load span{animation:none}}

@@media(max-width:1000px){.hs-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.hs-details{grid-template-columns:repeat(2,minmax(0,1fr))}.hs-two{grid-template-columns:1fr}}
@@media(max-width:600px){.hs-grid,.hs-details,.hs-two .hs-details{grid-template-columns:1fr}.hs-heading{align-items:flex-start;flex-direction:column}.hs-banner{padding:18px}.hs-map{height:320px}.hs-copy{opacity:1}}
</style>

<script>
(function () {
    var root = document.querySelector('.hs-page'); if (!root) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* 3D icon tilt / pop */
    if (!reduce) {
        root.querySelectorAll('[data-tilt]').forEach(function (card) {
            var art = card.querySelector('.hsi-3d'); if (!art) return;
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
                var cl = function (v) { return Math.max(-22, Math.min(22, v)); };
                art.style.setProperty('--ry', cl(x * 44) + 'deg'); art.style.setProperty('--rx', cl(-y * 44) + 'deg'); art.style.setProperty('--s', '1.14');
            });
            card.addEventListener('mouseleave', function () { art.style.setProperty('--ry', '0deg'); art.style.setProperty('--rx', '0deg'); art.style.setProperty('--s', '1'); });
            card.addEventListener('click', function () { art.classList.remove('pop'); void art.offsetWidth; art.classList.add('pop'); });
        });
    }

    /* stay progress bar animates in */
    root.querySelectorAll('.hs-track i[data-w]').forEach(function (b) {
        var w = b.getAttribute('data-w') + '%';
        if (reduce) { b.style.width = w; } else { requestAnimationFrame(function () { setTimeout(function () { b.style.width = w; }, 80); }); }
    });

    /* copy buttons */
    var toast = document.getElementById('hsToast'), timer;
    function say(msg) { toast.textContent = msg; toast.classList.add('show'); clearTimeout(timer); timer = setTimeout(function () { toast.classList.remove('show'); }, 1800); }
    function copy(text) {
        if (navigator.clipboard && window.isSecureContext) { return navigator.clipboard.writeText(text); }
        return new Promise(function (ok, fail) {
            var t = document.createElement('textarea'); t.value = text; t.style.position = 'fixed'; t.style.opacity = '0'; document.body.appendChild(t); t.select();
            try { document.execCommand('copy') ? ok() : fail(); } catch (e) { fail(e); } document.body.removeChild(t);
        });
    }
    root.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            copy(btn.getAttribute('data-copy')).then(function () { say(btn.id === 'hsCopyAll' ? 'Hostel details copied' : 'Copied to clipboard'); }, function () { say('Copy is not available in this browser'); });
        });
    });

    /* Google Map: loading state + Map / Satellite toggle */
    var frame = document.getElementById('hsMap'), load = document.getElementById('hsMapLoad');
    if (frame) {
        var done = function () { if (load) load.classList.add('done'); };
        frame.addEventListener('load', done); setTimeout(done, 6000);
        var base = frame.getAttribute('src').replace(/&t=[a-z]/, '');
        root.querySelectorAll('#hsMapType button').forEach(function (b) {
            b.addEventListener('click', function () {
                root.querySelectorAll('#hsMapType button').forEach(function (o) { o.classList.toggle('on', o === b); });
                if (load) load.classList.remove('done');
                frame.setAttribute('src', base + (b.getAttribute('data-t') === 'k' ? '&t=k' : ''));
            });
        });
    }
})();
</script>
@endsection