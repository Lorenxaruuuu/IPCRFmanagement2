<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>4Ps-RPMS | Regional Performance Management System</title>
    <meta name="description" content="The 4Ps-RPMS project aims to digitize, streamline, and centralize the performance evaluation process for all 4Ps staff within the Davao Region.">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500&display=swap" rel="stylesheet">

    <style>
        /* ─────────────────────────────────────────
           RESET & TOKENS
        ───────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:        #0d47a1;
            --blue-dark:   #0a3580;
            --blue-mid:    #1565c0;
            --blue-light:  #e8f0fe;
            --blue-xlight: #f0f5ff;
            --red:         #c62828;
            --red-light:   #ffebee;
            --navy:        #0d1b3e;
            --navy-mid:    #132044;
            --green:       #2e7d32;
            --green-light: #e8f5e9;

            --text-primary:   #111827;
            --text-secondary: #4b5563;
            --text-muted:     #6b7280;
            --border:         #e5e7eb;
            --border-mid:     #d1d5db;
            --bg-page:        #eef2fb;
            --bg-card:        #f8faff;
            --white:          #ffffff;

            --shadow-xs: 0 1px 2px rgba(0,0,0,.06);
            --shadow-sm: 0 2px 8px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md: 0 6px 24px rgba(13,71,161,.09), 0 2px 6px rgba(0,0,0,.05);
            --shadow-lg: 0 16px 48px rgba(13,71,161,.12), 0 4px 12px rgba(0,0,0,.06);
            --shadow-blue: 0 8px 28px rgba(13,71,161,.28);

            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 18px;
            --r-xl: 24px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-page);
            color: var(--text-primary);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-page); }
        ::-webkit-scrollbar-thumb { background: #b8c8e8; border-radius: 6px; }
        ::-webkit-scrollbar-thumb:hover { background: #7a9cc8; }

        /* ── NAVBAR ── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 200;
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(229,231,235,0.8);
            padding: 0 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            box-shadow: 0 1px 0 rgba(0,0,0,.04), var(--shadow-xs);
            transition: background 0.3s, box-shadow 0.3s;
        }
        .navbar.scrolled {
            background: rgba(255,255,255,0.97);
            box-shadow: var(--shadow-sm);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
        }
        .navbar-brand-logos {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .navbar-brand-logos img {
            display: block;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        .navbar-brand-logos img:hover { transform: scale(1.04); }
        .logo-dswd  { height: 46px; width: auto; }
        .logo-4ps   { height: 36px; width: auto; }
        .logo-bagong{ height: 44px; width: auto; }
        .navbar-brand-sep {
            width: 1px;
            height: 32px;
            background: var(--border-mid);
            flex-shrink: 0;
        }
        .navbar-brand-text {
            display: flex;
            flex-direction: column;
        }
        .navbar-brand-text .brand-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .navbar-brand-text .brand-sub {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 2px;
        }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 2px;
            list-style: none;
        }
        .navbar-links a {
            display: inline-flex;
            align-items: center;
            padding: 7px 17px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            text-decoration: none;
            color: var(--text-muted);
            border-radius: var(--r-sm);
            transition: background 0.18s, color 0.18s;
        }
        .navbar-links a:hover { color: var(--blue); background: var(--blue-light); }
        .navbar-links a.active {
            background: var(--blue);
            color: #fff;
            box-shadow: 0 2px 10px rgba(13,71,161,.3);
        }

        /* ── HERO ── */
        .hero {
            position: relative;
            background: var(--white);
            overflow: hidden;
            min-height: 560px;
            display: flex;
            align-items: center;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.35;
            pointer-events: none;
        }
        .hero-deco {
            position: absolute;
            top: 0; right: 0;
            width: 420px; height: 200px;
            pointer-events: none;
            z-index: 1;
        }
        .hero-deco::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--blue);
            clip-path: polygon(100% 0, 100% 100%, 22% 0);
            opacity: 0.95;
        }
        .hero-deco::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 60%; height: 52%;
            background: var(--red);
            clip-path: polygon(100% 0, 100% 100%, 0 0);
        }
        .hero-deco-bl {
            position: absolute;
            bottom: 0; left: 0;
            width: 200px; height: 130px;
            pointer-events: none;
            z-index: 1;
        }
        .hero-deco-bl::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--blue);
            clip-path: polygon(0 100%, 100% 100%, 0 0);
            opacity: 0.04;
        }
        .hero-inner {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            padding: 72px 48px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 56px;
            align-items: center;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 6px 16px 6px 10px;
            border: 1.5px solid rgba(13,71,161,0.35);
            border-radius: 999px;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--blue);
            background: rgba(232,240,254,0.6);
            margin-bottom: 24px;
        }
        .hero-badge-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 3px rgba(46,125,50,.18);
            flex-shrink: 0;
            animation: pulse-dot 2.4s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { box-shadow: 0 0 0 3px rgba(46,125,50,.18); }
            50%       { box-shadow: 0 0 0 6px rgba(46,125,50,.08); }
        }
        .hero-title {
            font-size: 50px;
            font-weight: 900;
            line-height: 1.08;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            margin-bottom: 18px;
        }
        .hero-title span { color: var(--blue); }
        .hero-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.7;
            font-style: italic;
            margin-bottom: 36px;
            max-width: 390px;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            padding: 14px 30px;
            background: var(--blue);
            color: #fff;
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border: none;
            border-radius: var(--r-sm);
            text-decoration: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-blue);
            transition: background 0.2s, transform 0.18s, box-shadow 0.2s;
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255,255,255,0) 30%, rgba(255,255,255,0.12) 50%, rgba(255,255,255,0) 70%);
            transform: translateX(-100%);
            transition: transform 0.45s ease;
        }
        .btn-primary:hover::before { transform: translateX(100%); }
        .btn-primary:hover {
            background: var(--blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(13,71,161,.38);
        }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary .btn-icon {
            width: 18px; height: 18px;
            background: rgba(255,255,255,0.18);
            border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .btn-primary .btn-icon svg {
            width: 11px; height: 11px;
            stroke: #fff; fill: none;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        /* Spec Card */
        .spec-card {
            background: var(--white);
            border-radius: var(--r-xl);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: transform 0.28s cubic-bezier(.22,1,.36,1), box-shadow 0.28s;
        }
        .spec-card:hover {
            transform: translateY(-5px) scale(1.005);
            box-shadow: 0 24px 64px rgba(13,71,161,.15), 0 6px 16px rgba(0,0,0,.06);
        }
        .spec-card-top { padding: 28px 28px 20px; }
        .spec-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
        }
        .spec-icon-wrap {
            width: 38px; height: 38px;
            background: var(--blue-light);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .spec-icon-wrap svg {
            width: 20px; height: 20px;
            color: var(--blue-mid);
            stroke: currentColor; fill: none;
            stroke-width: 1.8;
        }
        .spec-card-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }
        .spec-card-body p {
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.72;
            margin-bottom: 13px;
        }
        .spec-card-body p:last-child { margin-bottom: 0; }
        .spec-card-focus {
            display: flex;
            align-items: center;
            gap: 11px;
            background: var(--red-light);
            border-top: 1px solid rgba(198,40,40,.12);
            padding: 14px 28px;
        }
        .spec-card-focus svg {
            width: 15px; height: 15px;
            color: var(--red);
            flex-shrink: 0;
            stroke: currentColor; fill: none;
            stroke-width: 2;
        }
        .spec-card-focus p {
            font-size: 11.5px;
            color: var(--red);
            font-weight: 600;
            line-height: 1.45;
            margin: 0;
        }

        /* ── PAGE DOTS ── */
        .page-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            padding: 18px 0;
            background: var(--white);
        }
        .page-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--border-mid);
            transition: all 0.25s;
        }
        .page-dot.active {
            background: var(--blue);
            width: 22px;
            border-radius: 4px;
        }
        .page-dots.on-bg { background: var(--bg-page); }

        /* ── SECTION SHARED ── */
        .section-label {
            display: inline-block;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--blue);
            padding: 4px 0 4px 14px;
            position: relative;
            margin-bottom: 6px;
        }
        .section-label::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--blue);
        }

        /* ── ABOUT ── */
        .about-section {
            background: var(--bg-page);
            padding: 80px 48px;
        }
        .about-inner {
            max-width: 820px;
            margin: 0 auto;
        }
        .section-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 36px;
            border-bottom: 1.5px solid var(--border);
        }
        .section-tab {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0 0 12px;
            margin-right: 28px;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -1.5px;
            transition: color 0.18s, border-color 0.18s;
        }
        .section-tab.active {
            color: var(--text-primary);
            border-bottom-color: var(--text-primary);
        }
        .section-title {
            font-size: 32px;
            font-weight: 900;
            color: var(--text-primary);
            letter-spacing: -0.025em;
            margin-bottom: 32px;
        }
        /* Logo strip */
        .logo-strip {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 32px;
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            background: #f8fafc;
            padding: 24px;
        }
        .logo-cards-wrapper {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }
        .logo-card-item {
            height: 110px; width: 110px;
            display: flex; align-items: center; justify-content: center;
            background: var(--white);
            border: 1.5px solid var(--navy-mid);
            border-radius: 18px;
            padding: 12px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .logo-card-item:hover {
            transform: translateY(-2.5px);
            box-shadow: var(--shadow-md);
        }
        .logo-card-item.logo-card-wide {
            width: 220px;
            padding: 12px 24px;
        }
        .logo-card-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .logo-strip-caption {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.6;
            flex: 1;
        }
        .about-body {
            font-size: 13.5px;
            color: var(--text-secondary);
            line-height: 1.78;
            margin-bottom: 36px;
        }
        .about-body strong { color: var(--text-primary); font-weight: 700; }
        .cap-label {
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: 14px;
        }
        .cap-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 30px;
        }
        .cap-item {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 16px 18px;
            border: 1.5px solid rgba(46,125,50,.25);
            border-radius: var(--r-md);
            background: var(--white);
            transition: border-color 0.18s, box-shadow 0.18s, transform 0.2s;
        }
        .cap-item:hover {
            border-color: var(--green);
            box-shadow: 0 4px 16px rgba(46,125,50,.1);
            transform: translateY(-1px);
        }
        .cap-check {
            width: 20px; height: 20px;
            background: var(--green-light);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .cap-check svg {
            width: 12px; height: 12px;
            color: var(--green);
            stroke: currentColor; fill: none;
            stroke-width: 2.5;
            stroke-linecap: round; stroke-linejoin: round;
        }
        .cap-item p {
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.5;
            font-weight: 500;
        }
        .regional-note {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: var(--blue-light);
            border: 1px solid rgba(13,71,161,.15);
            border-radius: var(--r-md);
            padding: 16px 20px;
        }
        .note-icon {
            width: 30px; height: 30px;
            background: var(--blue);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .note-icon svg {
            width: 15px; height: 15px;
            color: #fff;
            stroke: currentColor; fill: none;
            stroke-width: 2;
        }
        .regional-note p {
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.65;
            padding-top: 4px;
        }
        .regional-note p strong { color: var(--blue); font-weight: 700; }

        /* ── SCOPE ── */
        .scope-section {
            background: var(--bg-page);
            padding: 16px 48px 80px;
        }
        .scope-card {
            max-width: 820px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--r-xl);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        .scope-card-head {
            padding: 36px 40px 24px;
            border-bottom: 1px solid var(--border);
        }
        .scope-card-title {
            font-size: 28px;
            font-weight: 900;
            color: var(--text-primary);
            letter-spacing: -0.025em;
            margin-bottom: 5px;
        }
        .scope-card-subtitle {
            font-size: 12.5px;
            color: var(--text-muted);
        }
        .scope-card-body { padding: 32px 40px 36px; }
        .scope-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
        }
        .scope-col-label {
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .scope-col-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .province-list { list-style: none; }
        .province-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 12px;
            border-radius: var(--r-sm);
            font-size: 13px;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 4px;
            transition: background 0.15s;
        }
        .province-item:not(.primary):hover { background: var(--bg-page); }
        .province-item.primary {
            background: var(--red-light);
            border: 1px solid rgba(198,40,40,.15);
        }
        .province-item.primary .province-name { color: var(--red); font-weight: 700; }
        .province-hub {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--red);
            display: block;
            margin-top: 2px;
        }
        .province-tag {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--border-mid);
            background: var(--bg-page);
            padding: 3px 8px;
            border-radius: 4px;
        }
        .province-icon svg {
            width: 14px; height: 14px;
            color: var(--red);
            stroke: currentColor; fill: none;
            stroke-width: 2;
        }
        .contact-desc {
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.65;
            margin-bottom: 16px;
        }
        .contact-list { display: flex; flex-direction: column; gap: 8px; }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 16px;
            background: var(--bg-page);
            border-radius: var(--r-md);
            border: 1px solid var(--border);
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .contact-item:hover {
            border-color: rgba(13,71,161,.25);
            box-shadow: 0 2px 10px rgba(13,71,161,.07);
        }
        .contact-icon {
            width: 34px; height: 34px;
            background: var(--blue-light);
            border-radius: var(--r-sm);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .contact-icon svg {
            width: 15px; height: 15px;
            color: var(--blue);
            stroke: currentColor; fill: none;
            stroke-width: 2;
        }
        .contact-text {
            font-size: 12.5px;
            color: var(--text-secondary);
            font-weight: 500;
            line-height: 1.4;
        }
        .scope-disclaimer {
            font-size: 11px;
            color: var(--text-muted);
            font-style: italic;
            line-height: 1.6;
            margin-top: 18px;
        }

        /* ── FOOTER ── */
        footer {
            background: var(--navy);
            position: relative;
            overflow: hidden;
        }
        footer::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--navy-mid) 0%, var(--navy) 60%);
        }
        footer::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.08), transparent);
        }
        .footer-inner {
            position: relative;
            z-index: 1;
            max-width: 1180px;
            margin: 0 auto;
            padding: 32px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }
        .footer-brand { display: flex; align-items: center; gap: 16px; }
        .footer-logo-wrap {
            width: 42px; height: 42px;
            background: rgba(255,255,255,.08);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255,255,255,.1);
            overflow: hidden;
        }
        .footer-logo-wrap img {
            width: 30px; height: 30px;
            object-fit: contain;
            opacity: 0.9;
        }
        .footer-brand-text .title {
            font-size: 12px;
            font-weight: 800;
            color: var(--white);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .footer-brand-text .tagline {
            font-size: 11px;
            color: #7a9cc8;
            line-height: 1.5;
            max-width: 360px;
        }
        .footer-copy {
            font-size: 11px;
            color: #7a9cc8;
            text-align: right;
            flex-shrink: 0;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .h-anim-1 { animation: fadeUp 0.6s cubic-bezier(.22,1,.36,1) both; }
        .h-anim-2 { animation: fadeUp 0.6s cubic-bezier(.22,1,.36,1) 0.1s both; }
        .h-anim-3 { animation: fadeUp 0.6s cubic-bezier(.22,1,.36,1) 0.2s both; }
        .h-anim-4 { animation: fadeUp 0.6s cubic-bezier(.22,1,.36,1) 0.3s both; }
        .h-anim-5 { animation: fadeUp 0.65s cubic-bezier(.22,1,.36,1) 0.15s both; }
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s cubic-bezier(.22,1,.36,1), transform 0.6s cubic-bezier(.22,1,.36,1);
        }
        .reveal.visible { opacity: 1; transform: none; }
        .reveal-delay-1 { transition-delay: 0.08s; }
        .reveal-delay-2 { transition-delay: 0.16s; }
        .reveal-delay-3 { transition-delay: 0.24s; }
        .reveal-delay-4 { transition-delay: 0.32s; }

        /* ── RESPONSIVE ── */
        @media (max-width: 960px) {
            .navbar { padding: 0 24px; }
            .logo-dswd { height: 36px; }
            .logo-4ps { height: 28px; }
            .logo-bagong { height: 34px; }
            .navbar-brand-logos { gap: 8px; }
            .navbar-brand-sep { display: none; }
            .navbar-brand-text { display: none; }
            .hero-inner { grid-template-columns: 1fr; gap: 36px; padding: 52px 24px; }
            .hero-title { font-size: 38px; }
            .hero-deco { width: 240px; height: 120px; }
            .about-section, .scope-section { padding: 56px 24px; }
            .scope-section { padding-top: 16px; }
            .about-inner { max-width: 100%; }
            .cap-grid { grid-template-columns: 1fr; }
            .scope-grid { grid-template-columns: 1fr; gap: 32px; }
            .scope-card-head, .scope-card-body { padding-left: 24px; padding-right: 24px; }
            .logo-strip { flex-direction: column; align-items: stretch; text-align: center; gap: 16px; padding: 20px; }
            .logo-cards-wrapper { justify-content: center; flex-wrap: wrap; width: 100%; }
            .logo-card-item { height: 90px; width: 90px; border-radius: 14px; padding: 8px; }
            .logo-card-item.logo-card-wide { width: 180px; padding: 8px 16px; }
            .logo-strip-caption { border-top: 1px solid var(--border); padding-top: 16px; }
            .footer-inner { flex-direction: column; align-items: flex-start; padding: 28px 24px; }
            .footer-copy { text-align: left; }
        }
        @media (max-width: 600px) {
            .hero-title { font-size: 30px; }
            .section-title { font-size: 24px; }
            .scope-card-title { font-size: 22px; }
            .logo-dswd { height: 28px; }
            .logo-4ps { height: 22px; }
            .logo-bagong { height: 28px; }
            .navbar { padding: 0 16px; }
            .navbar-links a { padding: 6px 11px; font-size: 10.5px; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════ -->
<header>
    <nav class="navbar" id="main-navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-brand-logos">
                <img src="{{ asset('images/dswd.jpg') }}" alt="DSWD Logo" class="logo-dswd">
                <img src="{{ asset('images/pantawid.jpg') }}" alt="4Ps Logo" class="logo-4ps">
                <img src="{{ asset('images/bagong.jpg') }}" alt="Bagong Pilipinas" class="logo-bagong">
            </div>
            <span class="navbar-brand-sep"></span>
            <div class="navbar-brand-text">
                <span class="brand-title">4Ps-RPMS Portal</span>
                <span class="brand-sub">Region XI · Davao Region</span>
            </div>
        </a>
        <ul class="navbar-links">
            <li><a href="#home" class="active" id="nav-home">Home</a></li>
            <li><a href="#about" id="nav-about">About</a></li>
            <li><a href="#contact" id="nav-contact">Contact</a></li>
        </ul>
    </nav>
</header>

<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="hero" id="home">
    <div class="hero-deco" aria-hidden="true"></div>
    <div class="hero-deco-bl" aria-hidden="true"></div>

    <div class="hero-inner">
        <!-- Left column -->
        <div>
            <div class="hero-badge h-anim-1">
                <span class="hero-badge-dot"></span>
                Region XI Office Portal
            </div>

            <h1 class="hero-title h-anim-2">
                Regional Performance<br>
                <span>Management System</span>
            </h1>

            <p class="hero-desc h-anim-3">
                Centralize the performance evaluation process for all 4Ps staff within the Davao Region.
            </p>

            <a href="{{ route('login') }}" class="btn-primary h-anim-4" id="hero-login-btn">
                Log In To System
                <span class="btn-icon">
                    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </span>
            </a>
        </div>

        <!-- Right column: Spec card -->
        <div class="h-anim-5">
            <div class="spec-card">
                <div class="spec-card-top">
                    <div class="spec-card-header">
                        <div class="spec-icon-wrap">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </div>
                        <h2 class="spec-card-title">System Specification</h2>
                    </div>
                    <div class="spec-card-body">
                        <p>The 4Ps-RPMS project aims to digitize, streamline, and centralize the performance evaluation process for all 4Ps staff within the Davao Region.</p>
                        <p>This system will integrate key performance indicators (KPIs) specific to the 4Ps, automate the use of the Department of Social Welfare and Development Strategic Performance Management System (DSPMS) templates, facilitate staff rating, and serve as a repository for essential performance evaluation references and tools.</p>
                    </div>
                </div>
                <div class="spec-card-focus">
                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <p>Focus Area: Davao del Sur (with 5 Constituent Provinces)</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="page-dots">
    <span class="page-dot active"></span>
    <span class="page-dot"></span>
    <span class="page-dot"></span>
</div>

<!-- ══════════════════════════════════════════
     ABOUT
══════════════════════════════════════════ -->
<section class="about-section" id="about">
    <div class="about-inner">

        <div class="section-tabs reveal">
            <span class="section-tab active">System Overview</span>
        </div>

        <h2 class="section-title reveal">About 4Ps-RPMS Portal</h2>

        <!-- Logo strip -->
        <div class="logo-strip reveal reveal-delay-1">
            <div class="logo-cards-wrapper">
                <div class="logo-card-item">
                    <img src="{{ asset('images/bagong.jpg') }}" alt="Bagong Pilipinas Logo">
                </div>
                <div class="logo-card-item logo-card-wide">
                    <img src="{{ asset('images/pantawid.jpg') }}" alt="4Ps Logo">
                </div>
                <div class="logo-card-item">
                    <img src="{{ asset('images/dswd.jpg') }}" alt="DSWD FO XI Logo">
                </div>
            </div>
            <div class="logo-strip-caption">
                Official initiative of the Pantawid Pamilyang Pilipino Program (4Ps) — DSWD Field Office XI, Davao Region.
            </div>
        </div>

        <p class="about-body reveal reveal-delay-1">
            The <strong>4Ps-RPMS (Regional Performance Management System)</strong> project aims to digitize, streamline, and centralize the performance evaluation process for all 4Ps staff within the <strong>Davao Region</strong>. It establishes a single source of truth for tracking, managing, and evaluating human resources across the regional offices.
        </p>

        <div class="cap-label reveal reveal-delay-2">Key System Focus and Capabilities</div>
        <div class="cap-grid reveal reveal-delay-2">
            <div class="cap-item">
                <div class="cap-check">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <p>Digitize, streamline, and centralize performance evaluation processes</p>
            </div>
            <div class="cap-item">
                <div class="cap-check">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <p>Integrate Key Performance Indicators (KPIs) specific to 4Ps operations</p>
            </div>
            <div class="cap-item">
                <div class="cap-check">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <p>Automate department-standard DSPMS performance templates</p>
            </div>
            <div class="cap-item">
                <div class="cap-check">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <p>Facilitate automated and objective staff rating and approvals</p>
            </div>
        </div>

        <div class="regional-note reveal reveal-delay-3">
            <div class="note-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <p>
                <strong>Regional Project Scope:</strong> This initiative is focused strictly on the <strong>Davao Region</strong>, emphasizing <strong>Davao del Sur</strong> along with its <strong>5 primary constituent provinces</strong> to optimize conditional cash transfer service compliance.
            </p>
        </div>
    </div>
</section>

<div class="page-dots on-bg">
    <span class="page-dot"></span>
    <span class="page-dot active"></span>
    <span class="page-dot"></span>
</div>

<!-- ══════════════════════════════════════════
     SCOPE / CONTACT
══════════════════════════════════════════ -->
<section class="scope-section" id="contact">
    <div class="scope-card reveal">
        <div class="scope-card-head">
            <h2 class="scope-card-title">Davao Region Regional Scope</h2>
            <p class="scope-card-subtitle">Geographic scope detailing the 5 provinces of Region XI centered on Davao del Sur.</p>
        </div>
        <div class="scope-card-body">
            <div class="scope-grid">
                <!-- Provinces -->
                <div>
                    <div class="scope-col-label">Region XI 5 Provinces</div>
                    <ul class="province-list">
                        <li class="province-item primary">
                            <div>
                                <span class="province-name">1. Davao del Sur</span>
                                <span class="province-hub">Primary Coordination Hub</span>
                            </div>
                            <span class="province-icon">
                                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </span>
                        </li>
                        <li class="province-item">
                            <span>2. Davao del Norte</span>
                            <span class="province-tag">Province</span>
                        </li>
                        <li class="province-item">
                            <span>3. Davao de Oro</span>
                            <span class="province-tag">Province</span>
                        </li>
                        <li class="province-item">
                            <span>4. Davao Oriental</span>
                            <span class="province-tag">Province</span>
                        </li>
                        <li class="province-item">
                            <span>5. Davao Occidental</span>
                            <span class="province-tag">Province</span>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <div class="scope-col-label">DSWD Regional Coordination</div>
                    <p class="contact-desc">
                        For queries, information, and official system coordination within Region XI, the regional office may be contacted directly:
                    </p>
                    <div class="contact-list">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="contact-text">DSWD Field Office XI, Davao City, Philippines</div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="contact-text">fo11@dswd.gov.ph</div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.13.96.35 1.9.65 2.82a2 2 0 0 1-.45 2.11L7.91 8.72a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.92.3 1.86.52 2.82.65a2 2 0 0 1 1.72 2.02z"></path>
                                </svg>
                            </div>
                            <div class="contact-text">+63 (082) 227-1964</div>
                        </div>
                    </div>
                    <p class="scope-disclaimer">
                        This digital portal prototype is compiled in alignment with national DSWD strategic human resource targets.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="page-dots on-bg">
    <span class="page-dot"></span>
    <span class="page-dot"></span>
    <span class="page-dot active"></span>
</div>

<!-- ══════════════════════════════════════════
     FOOTER
══════════════════════════════════════════ -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="footer-logo-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="4Ps Logo">
            </div>
            <div class="footer-brand-text">
                <div class="title">4PS-RPMS Portal &bull; Region XI</div>
                <div class="tagline">Digitizing and streamlining staff performance evaluations for Davao del Sur and neighboring provinces.</div>
            </div>
        </div>
        <div class="footer-copy">
            &copy; {{ date('Y') }} DSWD Davao Region Initiative.<br>All Rights Reserved.
        </div>
    </div>
</footer>

<!-- ══════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════ -->
<script>
    /* Navbar scroll effect */
    const navbar = document.getElementById('main-navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    /* Active nav link on scroll */
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-links a');

    const navObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                navLinks.forEach(l => l.classList.remove('active'));
                const link = document.querySelector(`.navbar-links a[href="#${entry.target.id}"]`);
                if (link) link.classList.add('active');
            }
        });
    }, { threshold: 0.45 });

    sections.forEach(s => navObserver.observe(s));

    /* Scroll reveal */
    const revealEls = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealEls.forEach(el => revealObserver.observe(el));

    /* Page dots auto-update */
    const dots = document.querySelectorAll('.page-dots');
    // dots are purely decorative and match section scroll — they update via the section observer above
</script>
</body>
</html>
