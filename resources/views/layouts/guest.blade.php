<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CASPA' }} | SchoolNexa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --caspa-navy: #102D46;
            --caspa-blue: #176B87;
            --caspa-teal: #20A4A3;
            --caspa-purple: #6652A3;
            --caspa-ink: #172B3A;
            --caspa-muted: #718096;
            --caspa-border: #E4EAF0;
            --caspa-surface: #F5F8FA;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: 'DM Sans', sans-serif;
            color: var(--caspa-ink);
        }

        body {
            background: #F5F8FA;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(390px, 0.88fr) minmax(530px, 1.12fr);
        }

        /* LEFT BRAND EXPERIENCE */


        .auth-brand {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
            padding: 48px clamp(34px, 5vw, 76px);
            color: #fff;

            /* Dark navy fallback */
            background: #102D46;
        }

        /* Blurred school photograph */
        .auth-brand::before {
            content: "";
            position: absolute;
            inset: -12px;
            z-index: -2;

            background-image:
                url("https://images.unsplash.com/photo-1562774053-701939374585?w=1600&auto=format&fit=crop&q=85");

            background-size: cover;
            background-position: center;
            filter: blur(6px);
            transform: scale(1.06);
        }

        /* Dark overlay for readable text */
        .auth-brand::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;

            background:
                linear-gradient(145deg,
                    rgba(10, 34, 58, .88),
                    rgba(15, 54, 82, .77) 55%,
                    rgba(23, 107, 135, .78));
        }

        .brand-top,
        .brand-main,
        .brand-bottom {
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-logo img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 13px;
            background: #fff;
            padding: 5px;
        }

        .brand-logo-name {
            font-family: 'Manrope', sans-serif;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -.8px;
        }

        .brand-logo-caption {
            color: #B7D2DE;
            font-size: 10px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .brand-main {
            max-width: 530px;
            margin: 70px 0;
        }

        .brand-overline {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #9EDDDC;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .brand-overline:before {
            content: "";
            width: 23px;
            height: 2px;
            background: var(--caspa-teal);
        }

        .brand-main h1 {
            font-family: 'Manrope', sans-serif;
            font-size: clamp(34px, 3.5vw, 49px);
            line-height: 1.16;
            letter-spacing: -2px;
            font-weight: 800;
            margin: 23px 0 19px;
        }

        .brand-main h1 span {
            color: #65D1C8;
        }

        .brand-description {
            max-width: 455px;
            color: #C2D5E0;
            font-size: 14px;
            line-height: 1.9;
        }

        .brand-highlights {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 38px;
        }

        .brand-highlight {
            padding: 17px;
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 13px;
            background: rgba(255, 255, 255, .055);
        }

        .brand-highlight-icon {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border-radius: 9px;
            background: rgba(32, 164, 163, .16);
            color: #73DDD2;
            font-size: 16px;
            margin-bottom: 13px;
        }

        .brand-highlight strong {
            display: block;
            font-size: 12px;
            color: #fff;
        }

        .brand-highlight p {
            color: #B5CBD7;
            font-size: 10px;
            line-height: 1.6;
            margin: 6px 0 0;
        }

        .brand-bottom {
            border-top: 1px solid rgba(255, 255, 255, .16);
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
            color: #AFC7D3;
            font-size: 10px;
        }

        /* RIGHT AUTH AREA */

        .auth-content {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px clamp(25px, 5vw, 80px);
            background:
                radial-gradient(circle at 95% 5%, rgba(23, 107, 135, .045), transparent 24%),
                #F8FAFC;
        }

        .auth-container {
            width: 100%;
            max-width: 455px;
        }

        .mobile-brand {
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .mobile-brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .mobile-brand strong {
            font-family: 'Manrope', sans-serif;
            color: var(--caspa-navy);
            font-size: 19px;
        }

        .auth-intro {
            margin-bottom: 25px;
        }

        .auth-eyebrow {
            color: var(--caspa-blue);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 11px;
        }

        .auth-title {
            font-family: 'Manrope', sans-serif;
            color: var(--caspa-navy);
            font-size: 30px;
            line-height: 1.25;
            letter-spacing: -1.2px;
            font-weight: 800;
            margin: 0;
        }

        .auth-subtitle {
            color: var(--caspa-muted);
            font-size: 13px;
            line-height: 1.7;
            margin: 9px 0 0;
        }

        .auth-card {
            background: #fff;
            border: 1px solid var(--caspa-border);
            border-radius: 17px;
            padding: 29px;
            box-shadow: 0 15px 45px rgba(16, 45, 70, .055);
        }

        .auth-field {
            margin-bottom: 18px;
        }

        .auth-label {
            display: block;
            color: #33485A;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .auth-input {
            width: 100%;
            height: 47px;
            border: 1px solid #DCE4EA;
            border-radius: 9px;
            padding: 0 13px;
            background: #fff;
            color: #23394A;
            outline: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            transition: .2s;
        }

        .auth-input::placeholder {
            color: #A4B0BA;
        }

        .auth-input:focus {
            border-color: var(--caspa-blue);
            box-shadow: 0 0 0 3px rgba(23, 107, 135, .09);
        }

        .auth-password-wrap {
            position: relative;
        }

        .auth-password-wrap .auth-input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            top: 6px;
            right: 7px;
            width: 34px;
            height: 34px;
            border: 0;
            background: transparent;
            color: #718096;
            cursor: pointer;
            border-radius: 7px;
        }

        .password-toggle:hover {
            background: #F1F5F9;
            color: var(--caspa-navy);
        }

        .auth-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin: 2px 0 21px;
        }

        .auth-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #718096;
            font-size: 11px;
        }

        .auth-remember input {
            width: 14px;
            height: 14px;
            accent-color: var(--caspa-blue);
        }

        .auth-link {
            color: var(--caspa-blue);
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .auth-submit {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 9px;
            background: var(--caspa-navy);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }

        .auth-submit:hover {
            background: var(--caspa-blue);
            box-shadow: 0 7px 17px rgba(23, 107, 135, .18);
            transform: translateY(-1px);
        }

        .auth-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 11px;
        }

        .auth-outline {
            min-height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #DCE5EB;
            border-radius: 9px;
            background: #fff;
            color: #40566A;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .auth-outline:hover {
            border-color: #9ABCC9;
            background: #F7FBFC;
        }

        .demo-section {
            margin-top: 25px;
        }

        .demo-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 11px;
        }

        .demo-heading strong {
            color: #34495A;
            font-size: 11px;
        }

        .demo-heading span {
            color: #91A0AC;
            font-size: 9px;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 7px;
        }

        .demo-role {
            min-width: 0;
            min-height: 67px;
            padding: 10px 5px;
            border: 1px solid #E3EAF0;
            border-radius: 9px;
            background: #fff;
            cursor: pointer;
            text-align: center;
            transition: .18s;
        }

        .demo-role:hover,
        .demo-role.active {
            border-color: var(--caspa-teal);
            background: #F0FAF9;
            box-shadow: 0 0 0 2px rgba(32, 164, 163, .08);
        }

        .demo-role-icon {
            display: block;
            font-size: 17px;
            margin-bottom: 5px;
        }

        .demo-role strong {
            display: block;
            color: #34495A;
            font-size: 9px;
        }

        .demo-help {
            color: #91A0AC;
            font-size: 9px;
            line-height: 1.6;
            margin: 10px 0 0;
        }

        .auth-message {
            padding: 11px 13px;
            border-radius: 8px;
            font-size: 11px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .auth-message.success {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
        }

        .auth-message.error {
            background: #FEF2F2;
            color: #B91C1C;
            border: 1px solid #FECACA;
        }

        .auth-error {
            color: #DC2626;
            font-size: 10px;
            margin-top: 6px;
        }

        .auth-footer {
            color: #9AA6B2;
            font-size: 10px;
            text-align: center;
            margin-top: 22px;
        }

        @media(max-width: 1000px) {
            .auth-shell {
                grid-template-columns: minmax(320px, .8fr) minmax(480px, 1.2fr);
            }

            .auth-brand {
                padding: 38px 30px;
            }

            .auth-content {
                padding: 35px 25px;
            }

            .demo-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width: 760px) {
            .auth-shell {
                display: block;
            }

            .auth-brand {
                display: none;
            }

            .auth-content {
                min-height: 100vh;
                padding: 28px 17px;
            }

            .mobile-brand {
                display: flex;
            }

            .auth-card {
                padding: 23px 19px;
            }

            .auth-title {
                font-size: 27px;
            }

            .demo-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media(max-width: 420px) {
            .demo-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .auth-options {
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    {{ $slot }}
</body>

</html>