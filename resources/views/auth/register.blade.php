<x-guest-layout>
<div class="caspa-shell">
    <aside class="caspa-brand-panel">
        <div class="caspa-brand-content">
            <div class="caspa-brand">
                <img src="https://caspa-school-os-xi.vercel.app/assets/aistudio/caspa_logo_7f187f6a.png" alt="CASPA logo">
                <div>
                    <div class="caspa-brand-name">CASPA School OS</div>
                    <div class="caspa-brand-sub">Unified Institutional Cloud</div>
                </div>
            </div>
            <span class="caspa-version">VERSION 4.8 · ENTERPRISE</span>
            <h1>Your institution, connected from day one.</h1>
            <p class="caspa-brand-lead">
                CASPA brings academic operations, people, communication and governance into
                one coordinated school management platform.
            </p>
            <div class="caspa-feature-grid">
                <div class="caspa-feature">
                    <div class="caspa-feature-kicker">One Platform</div>
                    <strong>Connected Workspaces</strong>
                    <p>Bring authorized school teams together with role-based access.</p>
                </div>
                <div class="caspa-feature">
                    <div class="caspa-feature-kicker">Account Security</div>
                    <strong>Administrator Managed</strong>
                    <p>Accounts and permissions are controlled by your institution.</p>
                </div>
            </div>
        </div>
        <div class="caspa-brand-footer">
            <span>POWERED BY ERP COMMUNITY</span>
            <span>SECURE CAMPUS CLOUD</span>
        </div>
    </aside>

    <main class="caspa-auth-panel">
        <div class="caspa-auth-wrap">
            <div class="caspa-mobile-brand">
                <img src="https://caspa-school-os-xi.vercel.app/assets/aistudio/caspa_logo_7f187f6a.png" alt="CASPA logo">
                <strong>CASPA School OS</strong>
            </div>
            <div class="caspa-auth-card">
                <div class="caspa-eyebrow">Create your account</div>
                <h2 class="caspa-auth-heading">Join your CASPA workspace</h2>
                <p class="caspa-auth-subtitle">Use the account details provided by your school administrator.</p>

                <div class="caspa-alert caspa-alert-success">
                    <strong>Controlled account creation</strong><br>
                    Public self-registration is currently disabled. Ask your authorized administrator
                    for an invitation or account activation link.
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="caspa-field">
                        <label class="caspa-label" for="name">Full Name</label>
                        <input class="caspa-input" id="name" type="text" name="name" value="{{ old('name') }}"
                               required autocomplete="name" placeholder="Enter your full name">
                        <x-input-error :messages="$errors->get('name')" class="caspa-error" />
                    </div>
                    <div class="caspa-field">
                        <label class="caspa-label" for="email">Email Address</label>
                        <input class="caspa-input" id="email" type="email" name="email" value="{{ old('email') }}"
                               required autocomplete="username" placeholder="name@school.com">
                        <x-input-error :messages="$errors->get('email')" class="caspa-error" />
                    </div>
                    <div class="caspa-field">
                        <label class="caspa-label" for="password">Password</label>
                        <input class="caspa-input" id="password" type="password" name="password"
                               required autocomplete="new-password" placeholder="Create a password">
                        <x-input-error :messages="$errors->get('password')" class="caspa-error" />
                    </div>
                    <div class="caspa-field">
                        <label class="caspa-label" for="password_confirmation">Confirm Password</label>
                        <input class="caspa-input" id="password_confirmation" type="password"
                               name="password_confirmation" required autocomplete="new-password"
                               placeholder="Re-enter your password">
                        <x-input-error :messages="$errors->get('password_confirmation')" class="caspa-error" />
                    </div>
                    <button class="caspa-primary" type="submit" style="margin-top:23px">Create Account</button>
                </form>
                <div class="caspa-auth-bottom">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
                </div>
            </div>
            <div class="caspa-copyright">© {{ date('Y') }} CASPA School OS · Unified Institutional Cloud</div>
        </div>
    </main>
</div>
</x-guest-layout>
