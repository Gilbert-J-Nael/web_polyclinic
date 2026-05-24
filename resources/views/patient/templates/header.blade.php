<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title ?? 'Antrean Pasien' }} — Klinik</title>

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & Base ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --pq-green:        #16a34a;
            --pq-green-light:  #dcfce7;
            --pq-green-dark:   #14532d;
            --pq-bg:           #f8fafc;
            --pq-surface:      #ffffff;
            --pq-border:       #e2e8f0;
            --pq-text:         #0f172a;
            --pq-muted:        #64748b;
            --pq-nav-h:        64px;
            --pq-radius:       12px;
            --pq-radius-lg:    16px;
        }

        html, body {
            height: 100%;
            margin: 0;
            background: var(--pq-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--pq-text);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Navbar ────────────────────────────────────────────────── */
        .pq-navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            height: var(--pq-nav-h);
            background: var(--pq-surface);
            border-bottom: 1px solid var(--pq-border);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            gap: 1rem;
        }

        /* Logo mark */
        .pq-logo-mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--pq-green);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pq-logo-mark i {
            color: #fff;
            font-size: 18px;
        }

        /* Clinic name */
        .pq-clinic-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--pq-text);
            letter-spacing: -.3px;
            line-height: 1.2;
        }
        .pq-clinic-tagline {
            font-size: 11px;
            font-weight: 400;
            color: var(--pq-muted);
        }

        /* Nav divider */
        .pq-nav-sep {
            width: 1px;
            height: 28px;
            background: var(--pq-border);
            margin: 0 .5rem;
        }

        /* Page title pill */
        .pq-page-pill {
            font-size: 12px;
            font-weight: 600;
            color: var(--pq-green);
            background: var(--pq-green-light);
            padding: 4px 12px;
            border-radius: 100px;
            letter-spacing: .02em;
        }

        /* Right side: date/clock */
        .pq-nav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .pq-nav-date {
            font-size: 12px;
            color: var(--pq-muted);
            font-weight: 500;
            text-align: right;
            line-height: 1.4;
        }
        .pq-nav-clock {
            font-size: 20px;
            font-weight: 700;
            color: var(--pq-text);
            letter-spacing: -.5px;
            font-variant-numeric: tabular-nums;
            min-width: 56px;
            text-align: right;
        }

        /* Live dot */
        .pq-live {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--pq-green);
            background: var(--pq-green-light);
            padding: 5px 10px;
            border-radius: 100px;
        }
        .pq-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--pq-green);
            animation: livepulse 1.6s ease-in-out infinite;
        }
        @keyframes livepulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .4; transform: scale(.8); }
        }

        /* ── Main wrapper ──────────────────────────────────────────── */
        .pq-main {
            min-height: calc(100vh - var(--pq-nav-h));
            display: flex;
            flex-direction: column;
        }
        .pq-container {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            flex: 1;
        }

        /* ── Card override ─────────────────────────────────────────── */
        .card {
            border-radius: var(--pq-radius-lg) !important;
            border-color: var(--pq-border) !important;
        }
        .card-header { border-color: var(--pq-border) !important; }
        .card-footer { border-color: var(--pq-border) !important; }

        /* ── Table tweak ───────────────────────────────────────────── */
        .table th { font-size: .65rem !important; }
    </style>
</head>
<body>

{{-- ── Navbar ────────────────────────────────────────────────────── --}}
<nav class="pq-navbar">

    {{-- Logo + nama klinik --}}
    <div class="pq-logo-mark" aria-hidden="true">
        <i class="bi bi-hospital"></i>
    </div>
    <div>
        <div class="pq-clinic-name">Klinik Sehat</div>
        <div class="pq-clinic-tagline">Sistem Antrean Elektronik</div>
    </div>

    <div class="pq-nav-sep" aria-hidden="true"></div>

    {{-- Halaman aktif --}}
    <span class="pq-page-pill">
        <i class="bi bi-list-ol me-1"></i>{{ $title ?? 'Antrean' }}
    </span>

    {{-- Kanan: live badge + tanggal + jam --}}
    <div class="pq-nav-right">
        <div class="pq-live">
            <span class="pq-live-dot" aria-hidden="true"></span>
            LIVE
        </div>
        <div>
            <div class="pq-nav-date" id="nav-date">—</div>
            <div class="pq-nav-clock" id="nav-clock" aria-live="polite" aria-atomic="true">--:--</div>
        </div>
    </div>
</nav>

{{-- ── Content wrapper ──────────────────────────────────────────── --}}
<main class="pq-main">
    <div class="pq-container">

{{-- ↑ content-page & content dari dashboard.blade.php masuk di sini --}}