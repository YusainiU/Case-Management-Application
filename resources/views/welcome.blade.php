<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f9fc">
    <meta name="description" content="SimplyDigital brings case management, engineering workflows, documents, calendars and notifications together in one workspace.">

    <title>SimplyDigital — Case & Engineering Management</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    <style>
        :root {
            color-scheme: light;
            --ink: #14213d;
            --muted: #64748b;
            --primary: #315efb;
            --primary-dark: #2448d8;
            --line: #e5eaf2;
            --surface: #ffffff;
            --canvas: #f7f9fc;
            --soft-blue: #edf2ff;
            --radius: 20px;
            --shadow: 0 18px 50px rgba(24, 43, 84, .08);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background:
                radial-gradient(ellipse at 10% 0%, rgba(49, 94, 251, .08), transparent 34rem),
                var(--canvas);
            color: var(--ink);
            font-family: "Instrument Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button, a { -webkit-tap-highlight-color: transparent; }
        a:focus-visible { outline: 3px solid rgba(49, 94, 251, .4); outline-offset: 4px; }
        .container { width: min(1120px, calc(100% - 48px)); margin-inline: auto; }

        .site-header { padding: 22px 0; }
        .nav { display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .brand { display: inline-flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.08rem; letter-spacing: -.035em; }
        .brand-mark {
            display: grid; place-items: center; width: 42px; height: 42px; flex: 0 0 42px;
            border-radius: 13px; background: var(--primary); color: white;
            box-shadow: 0 7px 16px rgba(49, 94, 251, .24);
        }
        .brand-mark svg { width: 23px; height: 23px; }
        .brand small { display: block; margin-top: 2px; color: var(--muted); font-size: .72rem; font-weight: 500; letter-spacing: .01em; }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .button {
            display: inline-flex; justify-content: center; align-items: center; gap: 9px;
            min-height: 44px; padding: 0 18px; border: 1px solid transparent;
            border-radius: 12px; font-size: .91rem; font-weight: 600;
            transition: transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .button:hover { transform: translateY(-1px); }
        .button-secondary { background: #fff; border-color: var(--line); color: var(--ink); }
        .button-secondary:hover { border-color: #c8d2e2; background: #fbfcff; }
        .button-primary { color: #fff; background: var(--primary); box-shadow: 0 7px 16px rgba(49, 94, 251, .18); }
        .button-primary:hover { background: var(--primary-dark); box-shadow: 0 10px 20px rgba(49, 94, 251, .23); }
        .button svg { width: 17px; height: 17px; }
        .login-entry { margin: 0; }
        .login-entry .button { cursor: pointer; font-family: inherit; }

        .hero { padding: 74px 0 60px; text-align: center; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px; padding: 7px 12px;
            border: 1px solid #dfe7ff; border-radius: 999px; background: rgba(255,255,255,.8);
            color: #3151c7; font-size: .78rem; font-weight: 700; letter-spacing: .055em; text-transform: uppercase;
        }
        .eyebrow-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c58b; box-shadow: 0 0 0 4px #e0fbf0; }
        h1 { max-width: 790px; margin: 23px auto 18px; font-size: clamp(2.5rem, 6vw, 4.65rem); line-height: 1.04; letter-spacing: -.065em; font-weight: 700; }
        h1 span { color: var(--primary); }
        .hero-copy { max-width: 650px; margin: 0 auto; color: var(--muted); font-size: clamp(1rem, 2vw, 1.13rem); line-height: 1.8; }
        .hero-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 30px; }
        .hero-actions .button { min-height: 50px; padding-inline: 22px; }
        .trust-note { display: flex; justify-content: center; align-items: center; gap: 9px; margin-top: 20px; color: #7a879b; font-size: .82rem; }
        .trust-note svg { width: 16px; height: 16px; color: #22a879; }

        .preview-wrap { position: relative; max-width: 940px; margin: 4px auto 0; padding: 0 0 24px; }
        .preview-glow { position: absolute; inset: 14% 9% 0; z-index: 0; border-radius: 50%; background: rgba(49,94,251,.13); filter: blur(55px); }
        .preview {
            position: relative; z-index: 1; overflow: hidden; border: 1px solid #e1e7f0;
            border-radius: 20px; background: #fff; box-shadow: 0 26px 70px rgba(28, 47, 88, .13);
            text-align: left;
        }
        .preview-topbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 20px; border-bottom: 1px solid #edf0f5; background: #fff; }
        .window-dots { display: flex; gap: 6px; }
        .window-dots i { width: 8px; height: 8px; display: block; border-radius: 50%; background: #dce3ed; }
        .window-dots i:first-child { background: #ff8d8d; }
        .window-dots i:nth-child(2) { background: #ffcf70; }
        .window-dots i:nth-child(3) { background: #6ed8a4; }
        .preview-label { color: #8190a6; font-size: .76rem; font-weight: 600; }
        .preview-user { display: flex; align-items: center; gap: 8px; color: #53627a; font-size: .75rem; font-weight: 600; }
        .avatar { display: grid; place-items: center; width: 27px; height: 27px; border-radius: 9px; background: #e8edff; color: #315efb; font-size: .68rem; font-weight: 700; }
        .preview-body { display: grid; grid-template-columns: 190px 1fr; min-height: 300px; }
        .preview-sidebar { padding: 22px 14px; border-right: 1px solid #edf0f5; background: #fbfcfe; }
        .side-brand { display: flex; align-items: center; gap: 8px; padding: 0 9px 22px; font-size: .8rem; font-weight: 700; }
        .side-brand-mark { display: grid; place-items: center; width: 25px; height: 25px; border-radius: 8px; background: var(--primary); color: #fff; font-size: .8rem; }
        .side-caption { padding: 0 10px 9px; color: #a0aabd; font-size: .62rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .side-item { display: flex; align-items: center; gap: 10px; padding: 10px; margin-bottom: 4px; border-radius: 9px; color: #6c7a90; font-size: .74rem; font-weight: 600; }
        .side-item svg { width: 15px; height: 15px; }
        .side-item.active { background: #eaf0ff; color: #3151c7; }
        .preview-main { padding: 24px; min-width: 0; }
        .preview-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 20px; }
        .preview-heading h2 { margin: 0 0 5px; font-size: 1.12rem; letter-spacing: -.035em; }
        .preview-heading p { margin: 0; color: #8995a8; font-size: .72rem; }
        .preview-date { padding: 8px 10px; border: 1px solid #e7ebf2; border-radius: 9px; color: #68768c; font-size: .67rem; white-space: nowrap; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; }
        .stat { padding: 15px; border: 1px solid #edf0f5; border-radius: 13px; background: #fff; }
        .stat-label { color: #8793a6; font-size: .68rem; font-weight: 600; }
        .stat-value { margin-top: 8px; font-size: 1.55rem; font-weight: 700; letter-spacing: -.05em; }
        .stat-change { margin-top: 5px; color: #1ca779; font-size: .64rem; font-weight: 600; }
        .preview-lower { display: grid; grid-template-columns: 1.25fr 1fr; gap: 12px; margin-top: 12px; }
        .panel { padding: 15px; border: 1px solid #edf0f5; border-radius: 13px; }
        .panel-title { margin: 0 0 13px; font-size: .76rem; font-weight: 700; }
        .case-row { display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; padding: 10px 0; border-top: 1px solid #f0f2f6; }
        .case-name { font-size: .7rem; font-weight: 600; }
        .case-id { margin-top: 4px; color: #9aa4b4; font-size: .62rem; }
        .status { padding: 5px 7px; border-radius: 6px; background: #e7f9f0; color: #16865f; font-size: .59rem; font-weight: 700; white-space: nowrap; }
        .status.review { background: #fff4dc; color: #a76b08; }
        .activity { display: flex; gap: 9px; padding: 8px 0; border-top: 1px solid #f0f2f6; }
        .activity-icon { display: grid; place-items: center; width: 26px; height: 26px; flex: 0 0 26px; border-radius: 8px; background: #eef2ff; color: #315efb; }
        .activity-icon svg { width: 13px; height: 13px; }
        .activity-text { color: #66748a; font-size: .65rem; line-height: 1.5; }
        .activity-text strong { color: #27354c; font-weight: 600; }
        .activity-time { margin-top: 3px; color: #a0aabd; font-size: .59rem; }

        .features { padding: 58px 0 84px; }
        .section-heading { max-width: 620px; margin: 0 auto 34px; text-align: center; }
        .section-heading .kicker { color: var(--primary); font-size: .77rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .section-heading h2 { margin: 12px 0 10px; font-size: clamp(1.8rem, 4vw, 2.6rem); letter-spacing: -.05em; line-height: 1.15; }
        .section-heading p { margin: 0; color: var(--muted); line-height: 1.75; }
        .feature-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 16px; }
        .feature-card { min-height: 210px; padding: 25px; border: 1px solid var(--line); border-radius: var(--radius); background: rgba(255,255,255,.86); box-shadow: 0 5px 18px rgba(24,43,84,.025); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .feature-card:hover { transform: translateY(-4px); border-color: #cdd8ff; box-shadow: var(--shadow); }
        .feature-icon { display: grid; place-items: center; width: 48px; height: 48px; margin-bottom: 20px; border-radius: 14px; background: var(--soft-blue); color: var(--primary); }
        .feature-icon svg { width: 23px; height: 23px; }
        .feature-card h3 { margin: 0 0 9px; font-size: 1.02rem; letter-spacing: -.025em; }
        .feature-card p { margin: 0; color: var(--muted); font-size: .91rem; line-height: 1.7; }
        .cta { display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 72px; padding: 34px 38px; border-radius: 22px; background: #172747; color: #fff; box-shadow: 0 18px 40px rgba(23,39,71,.14); }
        .cta h2 { margin: 0 0 8px; font-size: clamp(1.4rem, 3vw, 1.9rem); letter-spacing: -.04em; }
        .cta p { margin: 0; color: #c0cbe0; line-height: 1.7; }
        .cta .button { flex: 0 0 auto; background: #fff; color: #172747; }
        .cta .button:hover { background: #edf2ff; }
        footer { padding: 24px 0 30px; border-top: 1px solid var(--line); color: #8a96a8; font-size: .8rem; }
        .footer-inner { display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .footer-brand { color: #53627a; font-weight: 700; }

        @media (max-width: 760px) {
            .container { width: min(100% - 32px, 560px); }
            .site-header { padding: 16px 0; }
            .brand { font-size: .98rem; gap: 9px; }
            .brand-mark { width: 38px; height: 38px; flex-basis: 38px; }
            .nav-actions { gap: 7px; }
            .nav-actions .button { min-height: 40px; padding-inline: 12px; font-size: .82rem; }
            .nav-actions .login-entry .button { padding-inline: 10px; }
            .hero { padding: 54px 0 42px; }
            h1 { font-size: clamp(2.45rem, 10vw, 3.6rem); }
            .hero-copy { font-size: 1rem; }
            .preview-body { grid-template-columns: 1fr; }
            .preview-sidebar { display: none; }
            .preview-main { padding: 17px; }
            .stat-grid { gap: 8px; }
            .stat { padding: 11px; }
            .stat-value { font-size: 1.25rem; }
            .preview-lower { grid-template-columns: 1fr; }
            .features { padding: 44px 0 60px; }
            .feature-grid { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
            .feature-card { min-height: 0; padding: 19px; }
            .feature-icon { margin-bottom: 15px; }
            .cta { align-items: flex-start; flex-direction: column; padding: 27px 24px; margin-bottom: 48px; }
        }
        @media (max-width: 460px) {
            .brand small { display: none; }
            .brand-mark { width: 35px; height: 35px; flex-basis: 35px; }
            .nav-actions .button { padding-inline: 10px; }
            .nav-actions .login-entry .button-secondary { display: inline-flex; }
            .hero { padding-top: 45px; }
            .eyebrow { font-size: .68rem; }
            .hero-actions { flex-direction: column; }
            .hero-actions .button, .hero-actions .login-entry { width: 100%; }
            .hero-actions .login-entry .button { width: 100%; }
            .preview-topbar { padding: 12px; }
            .preview-label { font-size: .68rem; }
            .preview-user { display: none; }
            .preview-heading h2 { font-size: 1rem; }
            .preview-date { font-size: .6rem; }
            .stat-grid { grid-template-columns: 1fr; }
            .stat { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
            .stat-value { margin: 0; font-size: 1.15rem; }
            .stat-change { display: none; }
            .feature-grid { grid-template-columns: 1fr; }
            .feature-card { padding: 22px; }
            .footer-inner { flex-direction: column; align-items: flex-start; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="{{ url('/') }}" aria-label="SimplyDigital home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 7.5 12 4l7 3.5v9L12 20l-7-3.5v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m8.5 12 2.2 2.2 4.8-5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>SimplyDigital<small>Case &amp; Engineering Management</small></span>
            </a>

            @if (Route::has('login'))
                <nav class="nav-actions" aria-label="Account navigation">
                    @auth
                        <a class="button button-primary" href="{{ url('/dashboard') }}">
                            Go to dashboard
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @endauth
                    <form class="login-entry" method="POST" action="{{ route('login.session-reset', ['destination' => 'internal']) }}">
                        @csrf
                        <button class="button button-secondary" type="submit">Internal login</button>
                    </form>
                    <form class="login-entry" method="POST" action="{{ route('login.session-reset', ['destination' => 'customer']) }}">
                        @csrf
                        <button class="button button-primary" type="submit">Customer login</button>
                    </form>
                    @guest
                        @if (Route::has('register'))
                            <a class="button button-primary" href="{{ route('register') }}">Get started
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        @endif
                    @endguest
                </nav>
            @endif
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="eyebrow"><span class="eyebrow-dot"></span> One workspace. Better workflows.</div>
                <h1>Bring every case<br>and project <span>into focus.</span></h1>
                <p class="hero-copy">
                    SimplyDigital brings your casework, engineering operations, documents and team activity together in one organised workspace—so everyone can stay aligned and keep work moving.
                </p>
                <div class="hero-actions">
                    @if (Route::has('login'))
                        @auth
                            <a class="button button-primary" href="{{ url('/dashboard') }}">Open your dashboard
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        @endauth
                        <form class="login-entry" method="POST" action="{{ route('login.session-reset', ['destination' => 'internal']) }}">
                            @csrf
                            <button class="button button-primary" type="submit">Internal team login</button>
                        </form>
                        <form class="login-entry" method="POST" action="{{ route('login.session-reset', ['destination' => 'customer']) }}">
                            @csrf
                            <button class="button button-secondary" type="submit">Customer portal login</button>
                        </form>
                        @guest
                            @if (Route::has('register'))
                                <a class="button button-secondary" href="{{ route('register') }}">Create an account</a>
                            @endif
                        @endguest
                    @endif
                </div>
                <div class="trust-note">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    A clearer view of your cases, people and priorities
                </div>
            </div>
        </section>

        <section class="container preview-wrap" aria-label="Illustration of the SimplyDigital workspace">
            <div class="preview-glow"></div>
            <div class="preview">
                <div class="preview-topbar">
                    <div class="window-dots" aria-hidden="true"><i></i><i></i><i></i></div>
                    <div class="preview-label">SIMPLYDIGITAL WORKSPACE</div>
                    <div class="preview-user"><span class="avatar">SD</span> Team workspace</div>
                </div>
                <div class="preview-body">
                    <aside class="preview-sidebar" aria-hidden="true">
                        <div class="side-brand"><span class="side-brand-mark">S</span> SimplyDigital</div>
                        <div class="side-caption">Workspace</div>
                        <div class="side-item active"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg> Overview</div>
                        <div class="side-item"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3.5h7l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7"/><path d="M14 4v4h4M8 13h8M8 16.5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg> Cases &amp; tickets</div>
                        <div class="side-item"><svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 20v-1.5a6 6 0 0 1 12 0V20M17 5a3.5 3.5 0 0 1 0 6.8M18 14a5 5 0 0 1 3 4.5V20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg> Team</div>
                        <div class="side-item"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 3v4m10-4v4M3 10h18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg> Calendar</div>
                        <div class="side-item"><svg viewBox="0 0 24 24" fill="none"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg> Documents</div>
                    </aside>
                    <div class="preview-main">
                        <div class="preview-heading">
                            <div><h2>Workspace overview</h2><p>A snapshot of your team's activity</p></div>
                            <div class="preview-date">Today&nbsp; · &nbsp;Overview</div>
                        </div>
                        <div class="stat-grid">
                            <div class="stat"><div class="stat-label">Open cases</div><div class="stat-value">128</div><div class="stat-change">Across your team</div></div>
                            <div class="stat"><div class="stat-label">In progress</div><div class="stat-value">36</div><div class="stat-change">Work moving forward</div></div>
                            <div class="stat"><div class="stat-label">Upcoming events</div><div class="stat-value">08</div><div class="stat-change">On your calendar</div></div>
                        </div>
                        <div class="preview-lower">
                            <div class="panel">
                                <h3 class="panel-title">Recent case activity</h3>
                                <div class="case-row"><div><div class="case-name">Engineering review</div><div class="case-id">CASE-0248 · Updated recently</div></div><span class="status">In progress</span></div>
                                <div class="case-row"><div><div class="case-name">Equipment assessment</div><div class="case-id">CASE-0247 · Awaiting review</div></div><span class="status review">In review</span></div>
                            </div>
                            <div class="panel">
                                <h3 class="panel-title">Team updates</h3>
                                <div class="activity"><span class="activity-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3h7l4 4v14H6V4a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.7"/><path d="M14 3v5h4" stroke="currentColor" stroke-width="1.7"/></svg></span><div><div class="activity-text"><strong>Document updated</strong><br>Case notes are ready to review</div><div class="activity-time">Recently</div></div></div>
                                <div class="activity"><span class="activity-icon"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 3v4m10-4v4M3 10h18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><div><div class="activity-text"><strong>Case event scheduled</strong><br>Upcoming work in one view</div><div class="activity-time">Recently</div></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="features" id="features">
            <div class="container">
                <div class="section-heading">
                    <div class="kicker">Everything connected</div>
                    <h2>The tools your team needs,<br>all in one place.</h2>
                    <p>Keep important information organised, make activity easier to follow, and give your team a clearer way to manage everyday work.</p>
                </div>
                <div class="feature-grid">
                    <article class="feature-card">
                        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3.5h7l4 4V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 4v4h4M8 13h8M8 16.5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div>
                        <h3>Case &amp; ticket management</h3>
                        <p>Keep cases, tickets and their progress organised, with key information easy for your team to find.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="8" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 20v-1.5a6 6 0 0 1 12 0V20M17 5a3.5 3.5 0 0 1 0 6.8M18 14a5 5 0 0 1 3 4.5V20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div>
                        <h3>Engineering &amp; user management</h3>
                        <p>Bring team members and engineering-related work into a shared, coordinated workspace.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <h3>File &amp; document management</h3>
                        <p>Keep supporting files and important documentation alongside the work they relate to.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 3v4m10-4v4M3 10h18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="m8 15 2 2 5-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <h3>Case event calendar</h3>
                        <p>See important case events and upcoming activities in one convenient place.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <h3>Case event notifications</h3>
                        <p>Help your team stay informed about relevant case activity and scheduled events.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h16M8 15l3-4 3 2 5-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <h3>A clearer view of the work</h3>
                        <p>Bring your key workflows together to make it easier to understand what's happening and what needs attention.</p>
                    </article>
                </div>
            </div>
        </section>

        @if (Route::has('login'))
            <section class="container">
                <div class="cta">
                    <div>
                        <h2>Ready to get to work?</h2>
                        <p>Sign in to continue to your SimplyDigital workspace.</p>
                    </div>
                    @auth
                        <a class="button" href="{{ url('/dashboard') }}">Go to dashboard
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @else
                        <div class="hero-actions">
                            <form class="login-entry" method="POST" action="{{ route('login.session-reset', ['destination' => 'internal']) }}">
                                @csrf
                                <button class="button button-primary" type="submit">Internal team login</button>
                            </form>
                            <form class="login-entry" method="POST" action="{{ route('login.session-reset', ['destination' => 'customer']) }}">
                                @csrf
                                <button class="button button-secondary" type="submit">Customer portal login</button>
                            </form>
                        </div>
                    @endauth
                </div>
            </section>
        @endif
    </main>

    <footer>
        <div class="container footer-inner">
            <div><span class="footer-brand">SimplyDigital</span> <span>· Case &amp; Engineering Management</span></div>
            <div>&copy; {{ date('Y') }} SimplyDigital. All rights reserved.</div>
        </div>
    </footer>
</body>
</html>
