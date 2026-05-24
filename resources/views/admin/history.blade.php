<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 fw-semibold m-0">{{ $title }}</h4>
                </div>
                <div class="text-end">
                    <ol class="breadcrumb m-0 py-0">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item active">Riwayat Kunjungan</li>
                    </ol>
                </div>
            </div>

            <!-- Card -->
            <div class="card">
                <div class="card-body">

                    <!-- Filter -->
                    <form method="GET" action="{{ url('/riwayat-kunjungan') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control form-control-sm"
                                value="{{ $start_date ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control form-control-sm"
                                value="{{ $end_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold mb-1">Cari Pasien (Nama / NIK)</label>
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Ketik nama atau NIK..."
                                value="{{ $search ?? '' }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i data-feather="search" style="width:14px;height:14px;"></i> Filter
                            </button>
                            <a href="{{ url('/riwayat-kunjungan') }}" class="btn btn-secondary btn-sm">
                                <i data-feather="rotate-ccw" style="width:14px;height:14px;"></i>
                            </a>
                        </div>
                    </form>

                    <!-- Tombol Cetak -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">
                            Total: <strong>{{ count($history) }}</strong> data kunjungan
                        </small>
                        <a href="{{ url('/riwayat-kunjungan/print') }}?start_date={{ $start_date ?? '' }}&end_date={{ $end_date ?? '' }}&search={{ $search ?? '' }}"
                           target="_blank"
                           class="btn btn-success btn-sm">
                            <i data-feather="printer" style="width:14px;height:14px;"></i> Cetak Laporan
                        </a>
                    </div>

                    <!-- Tabel Riwayat Kunjungan -->
                    <table id="history" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Antrian</th>
                                <th>Tanggal Kunjungan</th>
                                <th>Nama Pasien</th>
                                <th>NIK</th>
                                <th>Dokter</th>
                                <th>Hari / Jam Praktik</th>
                                <th>Tekanan Darah</th>
                                <th>Keluhan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>{{ !empty($item->QUEUE_NUMBER) ? $item->QUEUE_NUMBER : '-' }}</td>
                                <td>
                                    {{ !empty($item->REGISTRATION_DATE)
                                        ? \Carbon\Carbon::parse($item->REGISTRATION_DATE)->format('d/m/Y')
                                        : '-' }}
                                    <br>
                                    <small class="text-muted">{{ $item->REGISTRATION_TIME ?? '' }}</small>
                                </td>
                                <td>
                                    {{ !empty($item->PATIENT_NAME) ? $item->PATIENT_NAME : '-' }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $item->GENDER === 'Male' ? 'L' : ($item->GENDER === 'Female' ? 'P' : '-') }}
                                        &bull; {{ $item->PHONE ?? '-' }}
                                    </small>
                                </td>
                                <td>{{ !empty($item->NIK) ? $item->NIK : '-' }}</td>
                                <td>{{ !empty($item->DOCTOR_NAME) ? $item->DOCTOR_NAME : '-' }}</td>
                                <td>
                                    {{ !empty($item->SCHEDULE_DAY) ? $item->SCHEDULE_DAY : '-' }}
                                    <br>
                                    <small class="text-muted">
                                        <?php
                                            $ts = !empty($item->TIME_START) ? substr($item->TIME_START, 0, 5) : '';
                                            $te = !empty($item->TIME_END)   ? substr($item->TIME_END,   0, 5) : '';
                                        ?>
                                        {{ $ts && $te ? $ts . ' – ' . $te : '-' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($item->SYSTOLIC) && !empty($item->DIASTOLIC)): ?>
                                        <span class="badge bg-info text-dark">
                                            {{ $item->SYSTOLIC }}/{{ $item->DIASTOLIC }}
                                        </span>
                                        <br><small class="text-muted">mmHg</small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($item->COMPLAINTS)): ?>
                                        <?php $keluhan = $item->COMPLAINTS; ?>
                                        <?php if (strlen($keluhan) > 50): ?>
                                            {{ substr($keluhan, 0, 50) }}...
                                            <br>
                                            <a href="#" class="small text-primary"
                                               data-bs-toggle="modal"
                                               data-bs-target="#keluhanModal"
                                               data-patient="{{ $item->PATIENT_NAME }}"
                                               data-keluhan="{{ $item->COMPLAINTS }}">
                                               Selengkapnya
                                            </a>
                                        <?php else: ?>
                                            {{ $keluhan }}
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $statusColor = [
                                            'Menunggu'   => 'warning',
                                            'Dilayani'   => 'primary',
                                            'Selesai'    => 'success',
                                            'Dibatalkan' => 'danger',
                                            'Missed'     => 'secondary',
                                        ];
                                        $color = $statusColor[$item->QUEUE_STATUS] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-{{ $color }}">
                                        {{ !empty($item->QUEUE_STATUS) ? $item->QUEUE_STATUS : '-' }}
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <!-- Tabel End -->

                </div><!-- /.card-body -->
            </div><!-- /.card -->

        </div><!-- /.container-fluid -->
    </div><!-- /.content -->
</div><!-- /.content-page -->

<!-- Modal Keluhan Lengkap -->
<div class="modal fade" id="keluhanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Keluhan &mdash; <span id="keluhanModalPatient" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="keluhanModalText" class="mb-0" style="white-space:pre-wrap;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>