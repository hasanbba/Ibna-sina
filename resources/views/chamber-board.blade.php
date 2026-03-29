<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="refresh" content="30">

        <title>Doctor In Chamber</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            :root {
                --bg-start: #f6efe4;
                --bg-end: #dbeafe;
                --panel: rgba(255, 255, 255, 0.9);
                --line: rgba(148, 163, 184, 0.35);
                --text: #172554;
                --muted: #475569;
                --accent: #0f766e;
                --accent-soft: #ccfbf1;
                --warn: #9a3412;
                --warn-soft: #ffedd5;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(255, 255, 255, 0.9), transparent 30%),
                    linear-gradient(135deg, var(--bg-start), var(--bg-end));
                color: var(--text);
            }

            .page {
                max-width: 1400px;
                margin: 0 auto;
                padding: 32px 20px 48px;
            }

            .topbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 16px;
                margin-bottom: 18px;
                padding: 14px 18px;
                border: 1px solid var(--line);
                border-radius: 20px;
                background: rgba(255, 255, 255, 0.72);
                backdrop-filter: blur(10px);
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            }

            .brand {
                font-size: 0.95rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            .nav-menu {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .nav-link {
                display: inline-flex;
                align-items: center;
                padding: 10px 14px;
                border-radius: 999px;
                border: 1px solid rgba(15, 118, 110, 0.15);
                background: rgba(255, 255, 255, 0.8);
                color: var(--text);
                text-decoration: none;
                font-size: 0.92rem;
                font-weight: 600;
                transition: transform 180ms ease, background-color 180ms ease, border-color 180ms ease;
            }

            .nav-link:hover {
                transform: translateY(-1px);
                background: var(--accent-soft);
                border-color: rgba(15, 118, 110, 0.3);
            }

            .hero {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 24px;
                padding: 24px;
                border: 1px solid var(--line);
                border-radius: 24px;
                background: var(--panel);
                backdrop-filter: blur(10px);
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            }

            .hero h1 {
                margin: 0;
                font-size: clamp(2rem, 4vw, 3.5rem);
                line-height: 1;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .hero p {
                margin: 8px 0 0;
                color: var(--muted);
                font-size: 1rem;
            }

            .stats {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: flex-start;
            }

            .stat {
                min-width: 180px;
                padding: 16px 18px;
                border-radius: 18px;
                background: linear-gradient(180deg, #ffffff, #eff6ff);
                border: 1px solid var(--line);
            }

            .stat-label {
                display: block;
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: var(--muted);
            }

            .stat-value {
                display: block;
                margin-top: 8px;
                font-size: 1.8rem;
                font-weight: 700;
            }

            .section {
                margin-top: 24px;
                padding: 22px;
                border-radius: 24px;
                border: 1px solid var(--line);
                background: var(--panel);
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            }

            .section-title {
                margin: 0 0 16px;
                font-size: 1.35rem;
                font-weight: 700;
            }

            .department-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 16px;
            }

            .directory-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 16px;
            }

            .department-card {
                padding: 18px;
                border-radius: 20px;
                border: 1px solid var(--line);
                background: linear-gradient(180deg, rgba(240, 253, 250, 0.95), rgba(255, 255, 255, 0.95));
            }

            .directory-card {
                padding: 18px;
                border-radius: 20px;
                border: 1px solid var(--line);
                background: linear-gradient(180deg, rgba(239, 246, 255, 0.96), rgba(255, 255, 255, 0.96));
            }

            .directory-card h3 {
                margin: 0 0 8px;
                font-size: 1.02rem;
            }

            .directory-meta {
                display: grid;
                gap: 8px;
                margin-top: 14px;
            }

            .directory-row {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 12px;
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.82);
                border: 1px solid rgba(148, 163, 184, 0.2);
                font-size: 0.92rem;
            }

            .directory-row strong {
                color: var(--muted);
                font-weight: 600;
            }

            .department-card h3 {
                margin: 0 0 10px;
                font-size: 1.05rem;
            }

            .badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 0.82rem;
                background: var(--accent-soft);
                color: var(--accent);
            }

            .doctor-list {
                margin: 14px 0 0;
                padding: 0;
                list-style: none;
                display: grid;
                gap: 10px;
            }

            .doctor-item {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 12px 14px;
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.85);
                border: 1px solid rgba(15, 118, 110, 0.15);
            }

            .doctor-name {
                font-weight: 600;
            }

            .doctor-time {
                color: var(--muted);
                white-space: nowrap;
            }

            .empty {
                padding: 18px;
                border-radius: 18px;
                background: var(--warn-soft);
                color: var(--warn);
                border: 1px dashed rgba(154, 52, 18, 0.25);
            }

            .table-wrap {
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                min-width: 720px;
            }

            th,
            td {
                padding: 14px 12px;
                text-align: left;
                border-bottom: 1px solid var(--line);
            }

            th {
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: var(--muted);
            }

            tbody tr:hover {
                background: rgba(255, 255, 255, 0.65);
            }

            .status {
                display: inline-flex;
                align-items: center;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 0.82rem;
                font-weight: 600;
            }

            .status.in {
                background: var(--accent-soft);
                color: var(--accent);
            }

            .status.out {
                background: #e2e8f0;
                color: #334155;
            }

            @media (max-width: 768px) {
                .page {
                    padding: 20px 14px 32px;
                }

                .topbar {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .hero,
                .section {
                    padding: 18px;
                    border-radius: 20px;
                }

                .stat {
                    min-width: 140px;
                    flex: 1 1 140px;
                }
            }
        </style>
    </head>
    <body>
        @php
            $inChamberCount = $departments->sum(fn ($department) => $department['consultants']->count());
        @endphp

        <div class="page">
            <div class="topbar">
                <div class="brand">Ibna Sina Chamber Board</div>
                <nav class="nav-menu">
                    <a class="nav-link" href="#in-chamber">In Chamber</a>
                    <a class="nav-link" href="#doctors-list">Doctors List</a>
                    <a class="nav-link" href="#activity">Today Activity</a>
                </nav>
            </div>

            <section class="hero">
                <div>
                    <h1>Doctor In Chamber</h1>
                    <p>Live chamber board for {{ $today->format('d M Y') }}. Doctors appear here after `In Time` is saved and disappear after `Out Time` is saved.</p>
                </div>

                <div class="stats">
                    <div class="stat">
                        <span class="stat-label">Current Date</span>
                        <span class="stat-value">{{ $today->format('d M Y') }}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">In Chamber</span>
                        <span class="stat-value">{{ $inChamberCount }}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Today Records</span>
                        <span class="stat-value">{{ $todayInOuts->count() }}</span>
                    </div>
                </div>
            </section>

            <section class="section" id="in-chamber">
                <h2 class="section-title">Doctors Currently In Chamber</h2>

                @if ($departments->isEmpty())
                    <div class="empty">No doctors are currently in chamber for today.</div>
                @else
                    <div class="department-grid">
                        @foreach ($departments as $department)
                            <article class="department-card">
                                <h3>{{ $department['name'] }}</h3>
                                <span class="badge">{{ $department['consultants']->count() }} Doctor{{ $department['consultants']->count() === 1 ? '' : 's' }} Available</span>

                                <ul class="doctor-list">
                                    @foreach ($department['consultants'] as $consultant)
                                        <li class="doctor-item">
                                            <span class="doctor-name">{{ $consultant['name'] }}</span>
                                            <span class="doctor-time">In: {{ $consultant['in_time'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="section" id="doctors-list">
                <h2 class="section-title">Doctors List</h2>

                @if ($doctors->isEmpty())
                    <div class="empty">No doctors found.</div>
                @else
                    <div class="directory-grid">
                        @foreach ($doctors as $doctor)
                            <article class="directory-card">
                                <h3>{{ $doctor->name }}</h3>
                                <span class="badge">{{ $doctor->department?->name ?? 'No Department' }}</span>

                                <div class="directory-meta">
                                    <div class="directory-row">
                                        <strong>Designation</strong>
                                        <span>{{ $doctor->designation ?: '-' }}</span>
                                    </div>
                                    <div class="directory-row">
                                        <strong>Chamber Time</strong>
                                        <span>{{ $doctor->chamber_time ?: '-' }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="section" id="activity">
                <h2 class="section-title">Today&apos;s In &amp; Out Activity</h2>

                @if ($todayInOuts->isEmpty())
                    <div class="empty">No `In & Out` records found for {{ $today->format('d M Y') }}.</div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Consultant</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todayInOuts as $inOut)
                                    @php
                                        $isInChamber = filled($inOut->in_time) && blank($inOut->out_time);
                                    @endphp
                                    <tr>
                                        <td>{{ $inOut->department?->name ?? '-' }}</td>
                                        <td>{{ $inOut->consultant?->name ?? '-' }}</td>
                                        <td>{{ $inOut->in_time ? \Carbon\Carbon::parse($inOut->in_time)->format('h:i A') : '-' }}</td>
                                        <td>{{ $inOut->out_time ? \Carbon\Carbon::parse($inOut->out_time)->format('h:i A') : '-' }}</td>
                                        <td>
                                            <span class="status {{ $isInChamber ? 'in' : 'out' }}">
                                                {{ $isInChamber ? 'In Chamber' : 'Out / Closed' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </body>
</html>
