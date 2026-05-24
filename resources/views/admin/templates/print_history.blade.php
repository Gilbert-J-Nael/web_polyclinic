<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #222;
            background: #fff;
            padding: 24px 32px;
        }

        /* ── Header ─────────────────────────────────── */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .report-header h2 {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
        }
        .report-header p { font-size: 11px; color: #555; margin-top: 3px; }

        /* ── Meta filter ─────────────────────────────── */
        .report-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 11px;
            color: #444;
        }

        /* ── Tabel ───────────────────────────────────── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        thead tr { background: #1a2035; color: #fff; }
        th, td { border: 1px solid #ccc; padding: 5px 7px; vertical-align: top; }
        th { font-size: 10.5px; text-align: center; white-space: nowrap; }
        td { font-size: 10.5px; }
        tbody tr:nth-child(even) { background: #f6f6f6; }
        .text-center { text-align: center; }
        .text-muted   { color: #777; }

        /* ── Badge status ────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 9.5px;
            font-weight: 600;
            color: #fff;
        }
        .bg-success   { background: #198754; }
        .bg-primary   { background: #0d6efd; }
        .bg-warning   { background: #ffc107; color: #333; }
        .bg-danger    { background: #dc3545; }
        .bg-secondary { background: #6c757d; }

        /* ── TTD ─────────────────────────────────────── */
        .report-footer { margin-top: 28px; display: flex; justify-content: flex-end; }
        .ttd-box { text-align: center; width: 180px; font-size: 11px; }
        .ttd-space { height: 56px; }

        /* ── Print button ────────────────────────────── */
        .no-print { text-align: right; margin-bottom: 14px; }
        .no-print button {
            padding: 6px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 6px;
        }
        .btn-print  { background: #1a2035; color: #fff; }
        .btn-close2 { background: #6c757d; color: #fff; }

        @media print {
            .no-print { display: none; }
            body { padding: 8px 12px; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>

    <!-- Tombol Cetak (tidak ikut tercetak) -->
    <div class="no-print">
        <button class="btn-print"  onclick="window.print()">🖨 Cetak / Simpan PDF</button>
        <button class="btn-close2" onclick="window.close()">✕ Tutup</button>
    </div>

    <!-- Header Laporan -->
    <div class="report-header">
        <h2>Laporan Riwayat Kunjungan Pasien</h2>
        <p>Sistem Informasi Manajemen Klinik</p>
    </div>

    <!-- Meta Filter -->
    <div class="report-meta">
        <div>
            Periode:
            <strong>
                {{ !empty($start_date)
                    ? \Carbon\Carbon::parse($start_date)->format('d/m/Y')
                    : '—' }}
            </strong>
            s/d
            <strong>
                {{ !empty($end_date)
                    ? \Carbon\Carbon::parse($end_date)->format('d/m/Y')
                    : '—' }}
            </strong>
        </div>
        <div>
            Dicetak: <strong>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB</strong>
            &nbsp;|&nbsp;
            Total: <strong>{{ count($history) }}</strong> data
        </div>
    </div>

    <!-- Tabel -->
    <table>
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th style="width:75px;">No. Antrian</th>
                <th style="width:80px;">Tgl Kunjungan</th>
                <th>Nama Pasien</th>
                <th style="width:120px;">NIK</th>
                <th>Dokter</th>
                <th style="width:90px;">Hari / Jam</th>
                <th style="width:70px;">T. Darah</th>
                <th>Keluhan</th>
                <th style="width:68px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $index => $item): ?>
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->QUEUE_NUMBER ?? '-' }}</td>
                <td class="text-center">
                    {{ !empty($item->REGISTRATION_DATE)
                        ? \Carbon\Carbon::parse($item->REGISTRATION_DATE)->format('d/m/Y')
                        : '-' }}
                    <br>
                    <span class="text-muted">{{ $item->REGISTRATION_TIME ?? '' }}</span>
                </td>
                <td>
                    {{ $item->PATIENT_NAME ?? '-' }}
                    <br>
                    <span class="text-muted">
                        {{ $item->GENDER === 'Male' ? 'L' : ($item->GENDER === 'Female' ? 'P' : '-') }}
                        &bull; {{ $item->PHONE ?? '-' }}
                    </span>
                </td>
                <td>{{ $item->NIK ?? '-' }}</td>
                <td>{{ $item->DOCTOR_NAME ?? '-' }}</td>
                <td class="text-center">
                    {{ $item->SCHEDULE_DAY ?? '-' }}
                    <br>
                    <span class="text-muted">
                        <?php
                            $ts = !empty($item->TIME_START) ? substr($item->TIME_START, 0, 5) : '';
                            $te = !empty($item->TIME_END)   ? substr($item->TIME_END,   0, 5) : '';
                            echo ($ts && $te) ? $ts . '–' . $te : '-';
                        ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php if (!empty($item->SYSTOLIC) && !empty($item->DIASTOLIC)): ?>
                        {{ $item->SYSTOLIC }}/{{ $item->DIASTOLIC }}
                        <br><span class="text-muted">mmHg</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>{{ $item->COMPLAINTS ?? '-' }}</td>
                <td class="text-center">
                    <?php
                        $statusColor = [
                            'Menunggu'   => 'bg-warning',
                            'Dilayani'   => 'bg-primary',
                            'Selesai'    => 'bg-success',
                            'Dibatalkan' => 'bg-danger',
                            'Missed'     => 'bg-secondary',
                        ];
                        $color = $statusColor[$item->QUEUE_STATUS] ?? 'bg-secondary';
                    ?>
                    <span class="badge {{ $color }}">{{ $item->QUEUE_STATUS ?? '-' }}</span>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (count($history) === 0): ?>
            <tr>
                <td colspan="10" class="text-center text-muted" style="padding:16px;">
                    Tidak ada data riwayat kunjungan.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="report-footer">
        <div class="ttd-box">
            <p>..............., {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
            <p style="margin-top:4px;">Petugas / Admin</p>
            <div class="ttd-space"></div>
            <p style="border-top:1px solid #333; padding-top:3px;">( .......................... )</p>
        </div>
    </div>

</body>
</html>