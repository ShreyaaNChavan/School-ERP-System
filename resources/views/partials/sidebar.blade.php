<aside class="sidebar">

    <a href="{{ route('dashboard') }}" class="brand">
        <img src="{{ asset('images/schoolnexa-logo.png') }}" alt="SchoolNexa Logo" class="brand-logo">
        <span class="brand-name">School<span>Nexa</span></span>
    </a>

    <div class="side-caption">Platform</div>

    <a href="{{ route('dashboard') }}" class="side-link active">
        <x-icon name="grid" size="19" />
        <span>Dashboard</span>
    </a>

    <div class="side-caption">Administration</div>

    <!-- @foreach([
        ['Organizations','building'],
        ['Schools','school'],
        ['Campuses','map'],
        ['Users & Roles','users'],
        ['Permissions','shield'],
    ] as [$label,$icon])
        <a href="#administration" class="side-link">
            <x-icon :name="$icon" size="18"/>
            <span>{{ $label }}</span>
        </a>
    @endforeach-->

    @foreach([
            ['Organizations', 'building', 'organizations.index'],
            ['Schools', 'school', null],
            ['Campuses', 'map', null],
            ['Users & Roles', 'users', null],
            ['Permissions', 'shield', null],
        ] as [$label, $icon, $route])
        <a href="{{ $route ? route($route) : '#' }}" class="side-link">
            <x-icon :name="$icon" size="18" />
            <span>{{ $label }}</span>
        </a>
    @endforeach
    
    <div class="side-caption">Academics</div>

    @foreach([
            ['Students', 'graduation'],
            ['Teachers & Staff', 'users'],
            ['Academics', 'book'],
            ['Attendance', 'calendar'],
            ['Exams & Results', 'file'],
        ] as [$label, $icon])
        <a href="#academics" class="side-link">
            <x-icon :name="$icon" size="18" />
            <span>{{ $label }}</span>
        </a>
    @endforeach

    <div class="side-caption">Business & Operations</div>

    @foreach([
            ['Admissions', 'users'],
            ['Finance & Fees', 'wallet'],
            ['HR & Payroll', 'briefcase'],
            ['Transport', 'bus'],
            ['Hostel', 'home'],
            ['Library', 'book'],
            ['Inventory', 'file'],
        ] as [$label, $icon])
        <a href="#operations" class="side-link">
            <x-icon :name="$icon" size="18" />
            <span>{{ $label }}</span>
        </a>
    @endforeach

    <div class="side-caption">Safety & Intelligence</div>

    @foreach([
            ['Safety & Compliance', 'shield'],
            ['Reports & Analytics', 'chart'],
            ['Settings', 'settings'],
        ] as [$label, $icon])
        <a href="#intelligence" class="side-link">
            <x-icon :name="$icon" size="18" />
            <span>{{ $label }}</span>
        </a>
    @endforeach

    <div class="side-bottom">
        <div class="profile-mini">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="profile-info">
                <div class="profile-name">{{ auth()->user()->name }}</div>
                <div class="profile-role">Super Administrator</div>
            </div>
        </div>
    </div>

</aside>