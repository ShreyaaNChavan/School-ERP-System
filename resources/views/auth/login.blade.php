<x-guest-layout>
    <div class="auth-shell">

        {{-- LEFT: CASPA BRAND PANEL --}}

        <aside class="auth-brand">

            <div class="brand-top">
                <div class="brand-logo">
                    <img src="{{ asset('images/schoolnexa-logo.png') }}" alt="SchoolNexa logo">

                    <div>
                        <div class="brand-logo-name">CASPA</div>
                        <div class="brand-logo-caption">
                            A SchoolNexa Platform
                        </div>
                    </div>
                </div>
            </div>

            <div class="brand-main">

                <div class="brand-overline">
                    A smarter way to run your school
                </div>

                <h1>
                    Everything your school needs,
                    <span>working together.</span>
                </h1>

                <p class="brand-description">
                    Manage academics, student records, staff, communication
                    and school operations from one connected platform.
                    Give every member of your school community a workspace
                    built around their responsibilities.
                </p>

                <div class="brand-highlights">

                    <div class="brand-highlight">
                        <div class="brand-highlight-icon">▤</div>
                        <strong>Academic Management</strong>
                        <p>Classes, examinations, attendance and results.</p>
                    </div>

                    <div class="brand-highlight">
                        <div class="brand-highlight-icon">♧</div>
                        <strong>People & Community</strong>
                        <p>Students, teachers, parents and staff records.</p>
                    </div>

                    <div class="brand-highlight">
                        <div class="brand-highlight-icon">⚙</div>
                        <strong>School Operations</strong>
                        <p>Fees, transport, hostel and administration.</p>
                    </div>

                    <div class="brand-highlight">
                        <div class="brand-highlight-icon">↗</div>
                        <strong>Reports & Insights</strong>
                        <p>Clear visibility into institutional activities.</p>
                    </div>

                </div>
            </div>

            <div class="brand-bottom">
                <span>CASPA · SCHOOLNEXA</span>
                <span>One connected school experience</span>
            </div>

        </aside>



        {{-- RIGHT: LOGIN FORM --}}
        <main class="auth-content">

            <div class="auth-container">

                <div class="mobile-brand">
                    <img src="{{ asset('images/caspa_logo.png') }}" alt="CASPA logo">
                    <strong>CASPA</strong>
                </div>

                <div class="auth-intro">
                    <div class="auth-eyebrow">Your workspace awaits</div>
                    <h1 class="auth-title">Welcome back.</h1>
                    <p class="auth-subtitle">
                        Sign in with your school account to continue where you left off.
                    </p>
                </div>

                <div class="auth-card">

                    <x-auth-session-status class="auth-message success" :status="session('status')" />

                    @if ($errors->any())
                        <div class="auth-message error" role="alert">
                            <strong>Unable to sign in.</strong><br>
                            Please verify your email and password.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="auth-field">
                            <label for="email" class="auth-label">Email address</label>

                            <input id="email" class="auth-input" type="text" name="email" value="{{ old('email') }}"
                                placeholder="you@your-school.com" required autofocus autocomplete="username">

                            <x-input-error :messages="$errors->get('email')" class="auth-error" />
                        </div>

                        <div class="auth-field">
                            <label for="password" class="auth-label">Password</label>

                            <div class="auth-password-wrap">
                                <input id="password" class="auth-input" type="password" name="password"
                                    placeholder="Enter your password" required autocomplete="current-password">

                                <button type="button" class="password-toggle" id="togglePassword"
                                    aria-label="Show password">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>

                            <x-input-error :messages="$errors->get('password')" class="auth-error" />
                        </div>

                        <div class="auth-options">

                            <label class="auth-remember" for="remember">
                                <input id="remember" type="checkbox" name="remember">
                                <span>Keep me signed in</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="auth-link" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif

                        </div>

                        <button type="submit" class="auth-submit">
                            Sign in to your workspace
                        </button>

                    </form>


                    <div class="auth-actions">

                        <button type="button" class="auth-outline" id="otpButton">
                            Sign in with OTP
                        </button>

                        @if (Route::has('register'))
                            <a class="auth-outline" href="{{ route('register') }}">
                                Create account
                            </a>
                        @else
                            <button type="button" class="auth-outline" id="createAccountButton">
                                Request access
                            </button>
                        @endif

                    </div>


                    {{-- DEMO ACCOUNT SELECTOR --}}
                    <div class="demo-section">

                        <div class="demo-heading">
                            <strong>Try a demo workspace</strong>
                            <span>TEST ACCOUNTS</span>
                        </div>

                        <div class="demo-grid">

                            <button type="button" class="demo-role" data-role="Admin"
                                data-email="admin.test@caspa.local">
                                <span class="demo-role-icon">▦</span>
                                <strong>Admin</strong>
                            </button>

                            <button type="button" class="demo-role" data-role="Principal"
                                data-email="principal.test@caspa.local">
                                <span class="demo-role-icon">⌂</span>
                                <strong>Principal</strong>
                            </button>

                            <button type="button" class="demo-role" data-role="Teacher"
                                data-email="teacher.test@caspa.local">
                                <span class="demo-role-icon">▤</span>
                                <strong>Teacher</strong>
                            </button>

                            <button type="button" class="demo-role" data-role="Parent"
                                data-email="parent.test@caspa.local">
                                <span class="demo-role-icon">♡</span>
                                <strong>Parent</strong>
                            </button>

                            <button type="button" class="demo-role" data-role="Student"
                                data-email="student.test@caspa.local">
                                <span class="demo-role-icon">✎</span>
                                <strong>Student</strong>
                            </button>

                        </div>

                        <p class="demo-help" id="demoHelp">
                            Select a role to fill its demo email address. Enter the password
                            associated with that demo account. Your assigned permissions remain unchanged.
                        </p>

                    </div>

                </div>

                <div class="auth-footer">
                    © {{ date('Y') }} CASPA · A SchoolNexa platform
                </div>

            </div>
        </main>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Show / hide password
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');

            if (passwordInput && togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';

                    passwordInput.type = isPassword ? 'text' : 'password';

                    togglePassword.setAttribute(
                        'aria-label',
                        isPassword ? 'Hide password' : 'Show password'
                    );
                });
            }


            // Demo account selection
            document.querySelectorAll('.demo-role').forEach(function (button) {

                button.addEventListener('click', function () {

                    document.querySelectorAll('.demo-role').forEach(function (role) {
                        role.classList.remove('active');
                    });

                    button.classList.add('active');

                    const emailInput = document.getElementById('email');

                    if (emailInput) {
                        emailInput.value = button.dataset.email;
                        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    const help = document.getElementById('demoHelp');

                    if (help) {
                        help.textContent =
                            button.dataset.role +
                            ' demo account selected. Enter its password to continue. ' +
                            'This does not change your account role or permissions.';
                    }

                    if (emailInput) {
                        emailInput.focus();
                    }
                });

            });


            // OTP is not connected to a backend yet
            const otpButton = document.getElementById('otpButton');

            if (otpButton) {
                otpButton.addEventListener('click', function () {
                    alert('OTP sign-in is not configured yet. Please use your demo account password.');
                });
            }


            // Public registration is intentionally disabled
            const createAccountButton = document.getElementById('createAccountButton');

            if (createAccountButton) {
                createAccountButton.addEventListener('click', function () {
                    alert('CASPA accounts are currently created by authorized administrators. Please contact your school administrator to request access.');
                });
            }

        });
    </script>
</x-guest-layout>