{{-- resources/views/patient/dashboard.blade.php --}}

<div class="content-page p-0"
     style="width:100vw; max-width:100vw; margin-left:calc(50% - 50vw); margin-right:calc(50% - 50vw); position:relative;">
    <div class="content d-flex flex-column p-3 p-md-4"
         style="min-height: 100vh; width:100%; max-width:100%; margin:0;">

        {{-- Header --}}
        <div class="py-2 d-flex align-items-sm-center flex-sm-row flex-column mb-2">
            <div class="flex-grow-1">
                <h2 class="fw-bold m-0" style="font-size:2rem;">Antrean Hari Ini</h2>
                <p class="text-muted mb-0" style="font-size:1.1rem;">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 px-4 py-2 rounded-pill bg-body-secondary"
                     style="font-size:1.3rem; font-weight:600;">
                    <span class="d-inline-block rounded-circle bg-success"
                          style="width:14px;height:14px;animation:pq-pulse 1.5s infinite;"></span>
                    <span id="pq-clock">--:--</span>
                </div>
                <button onclick="location.reload()"
                        class="btn btn-outline-secondary rounded-pill px-4 py-2" style="font-size:1.05rem;">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
        </div>

        {{-- Baris utama: Kiri = Profil Dokter, Kanan = Antrean Fixed --}}
        <div class="row g-3 flex-grow-1 mx-0">

            {{-- ============ KIRI: PROFIL DOKTER YANG MELAYANI ============ --}}
            <div class="col-lg-4 d-flex">
                <div class="card border-0 shadow-sm rounded-4 w-100">
                    <div class="card-header bg-transparent border-bottom px-4 py-3">
                        <h4 class="fw-bold mb-0" style="font-size:1.4rem;">
                            <i class="bi bi-person-badge me-2"></i>Dokter Bertugas
                        </h4>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center text-center px-4 py-5">
                        @if ($serving_queue && $serving_queue->DOCTOR_NAME)
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center mb-4"
                                 style="width:160px; height:160px;">
                                <i class="bi bi-person-fill text-success" style="font-size:5rem;"></i>
                            </div>

                            <div class="fw-bold" style="font-size:1.9rem; line-height:1.3;">
                                {{ $serving_queue->DOCTOR_NAME }}
                            </div>

                            @if ($serving_queue->POLY_NAME || $serving_queue->ROOM_NAME)
                            <div class="text-muted mb-3" style="font-size:1.25rem;">
                                {{ $serving_queue->POLY_NAME }}
                                @if($serving_queue->ROOM_NAME) · {{ $serving_queue->ROOM_NAME }} @endif
                            </div>
                            @endif

                            <span class="badge text-bg-success fw-semibold rounded-pill px-4 py-2 mb-4"
                                  style="font-size:1.1rem;">
                                <i class="bi bi-activity me-1"></i>Sedang Melayani
                            </span>

                            <hr class="w-100">

                            <div class="w-100">
                                <div class="text-muted text-uppercase mb-2"
                                     style="font-size:1rem; letter-spacing:.08em;">
                                    Pasien Saat Ini
                                </div>
                                <div class="fw-bold text-primary" style="font-size:3.5rem; line-height:1; letter-spacing:-1px;">
                                    {{ $serving_queue->QUEUE_NUMBER }}
                                </div>
                                <div class="fw-semibold mt-2" style="font-size:1.4rem;">
                                    {{ $serving_queue->PATIENT_NAME ?? '-' }}
                                </div>
                            </div>
                        @else
                            <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center mb-4"
                                 style="width:160px; height:160px;">
                                <i class="bi bi-person text-muted" style="font-size:5rem;"></i>
                            </div>
                            <div class="text-muted" style="font-size:1.3rem;">
                                Belum ada dokter yang sedang melayani.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============ KANAN: ANTREAN FIXED (MAKS 3) ============ --}}
            <div class="col-lg-8 d-flex">
                <div class="card border-0 shadow-sm rounded-4 w-100">
                    <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0" style="font-size:1.4rem;">Antrean Berikutnya</h4>
                        <span class="badge text-bg-primary fw-semibold ms-auto rounded-pill px-3 py-2" style="font-size:1.05rem;">
                            Total {{ $total_waiting }} menunggu
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size:1.15rem;">
                                <thead>
                                    <tr class="table-light">
                                        <th class="ps-4 py-3 text-uppercase text-muted fw-semibold"
                                            style="font-size:.95rem; width:60px;">#</th>
                                        <th class="py-3 text-uppercase text-muted fw-semibold"
                                            style="font-size:.95rem;">No. Antrean / Nama</th>
                                        <th class="py-3 text-uppercase text-muted fw-semibold text-center"
                                            style="font-size:.95rem;">Kondisi</th>
                                        <th class="py-3 text-uppercase text-muted fw-semibold text-center"
                                            style="font-size:.95rem;">Dokter / Poli</th>
                                        <th class="pe-4 py-3 text-uppercase text-muted fw-semibold text-center"
                                            style="font-size:.95rem;">Waktu Tunggu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($fixed_queue->take(3)->values() as $i => $q)
                                    <tr @if($q->QUEUE_STATUS === 'Dilayani') class="table-success" @endif>
                                        {{-- Nomor urut posisi --}}
                                        <td class="ps-4 py-3">
                                            @if ($q->QUEUE_STATUS === 'Dilayani')
                                                <span class="badge bg-success rounded-circle p-2"
                                                      style="font-size:1rem; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                                                    <i class="bi bi-activity"></i>
                                                </span>
                                            @elseif ($i === 0 || ($fixed_queue->where('QUEUE_STATUS','Dilayani')->count() > 0 && $i === 1))
                                                <span class="badge text-bg-primary rounded-circle"
                                                      style="font-size:1.05rem; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;">
                                                    {{ $i + 1 }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size:1.1rem; padding-left:6px;">
                                                    {{ $i + 1 }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Nomor & Nama --}}
                                        <td class="py-3">
                                            <div style="line-height:1.4;">
                                                <div class="text-muted" style="font-size:1rem;">{{ $q->QUEUE_NUMBER }}</div>
                                                <div class="fw-bold" style="font-size:1.3rem;">{{ $q->PATIENT_NAME ?? '-' }}</div>
                                            </div>
                                        </td>

                                        {{-- Kondisi Khusus --}}
                                        <td class="text-center py-3">
                                            @if ($q->SPECIAL_CONDITION_SCORE)
                                                <span class="badge text-bg-danger fw-semibold rounded-pill px-3 py-2"
                                                      style="font-size:1rem;">Prioritas</span>
                                            @elseif ($q->QUEUE_STATUS === 'Dilayani')
                                                <span class="badge text-bg-success fw-semibold rounded-pill px-3 py-2"
                                                      style="font-size:1rem;">Dilayani</span>
                                            @else
                                                <span class="text-muted" style="font-size:1.05rem;">—</span>
                                            @endif
                                        </td>

                                        {{-- Dokter / Poli --}}
                                        <td class="text-center text-muted py-3" style="font-size:1.05rem;">
                                            @if ($q->DOCTOR_NAME)
                                                <div class="fw-semibold text-body">{{ $q->DOCTOR_NAME }}</div>
                                                <div style="font-size:.95rem; color:var(--bs-secondary);">
                                                    {{ $q->POLY_NAME ?? '' }}
                                                    @if($q->ROOM_NAME) · {{ $q->ROOM_NAME }} @endif
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>

                                        {{-- Waktu Tunggu --}}
                                        <td class="pe-4 text-center text-muted py-3" style="font-size:1.05rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $q->waiting_minutes }} menit
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox d-block mb-3" style="font-size:2.5rem;"></i>
                                            <span style="font-size:1.2rem;">Tidak ada antrean aktif saat ini.</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top px-4 py-3">
                        <span class="text-muted" style="font-size:1.05rem;">
                            Menampilkan <strong>{{ min($fixed_queue->count(), 3) }}</strong> antrean teratas
                            dari <strong>{{ $total_waiting }}</strong> yang menunggu
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ BAWAH: SEDANG DILAYANI ============ --}}
        <div class="mt-3">
            @if ($serving_queue)
            <div class="card border-0 rounded-4"
                 style="border: 3px solid #22c55e !important; background: var(--bs-body-bg);">
                <div class="card-body px-5 py-4">
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <div>
                            <div class="text-success fw-bold mb-1"
                                 style="font-size:1.05rem; letter-spacing:.08em; text-transform:uppercase;">
                                <i class="bi bi-activity me-1"></i>Sedang Dilayani
                            </div>
                            <div class="fw-bold" style="font-size:4rem; line-height:1; letter-spacing:-1px;">
                                {{ $serving_queue->QUEUE_NUMBER }}
                            </div>
                        </div>
                        <div class="vr d-none d-sm-block" style="height:70px;"></div>
                        <div>
                            <div class="fw-bold" style="font-size:1.5rem;">
                                {{ $serving_queue->PATIENT_NAME ?? '-' }}
                            </div>
                            <div class="text-muted" style="font-size:1.15rem;">
                                Menunggu {{ $serving_queue->waiting_minutes }} menit
                            </div>
                            @if ($serving_queue->DOCTOR_NAME)
                            <div class="text-muted mt-1" style="font-size:1.1rem;">
                                <i class="bi bi-person-badge me-1"></i>
                                {{ $serving_queue->DOCTOR_NAME }}
                                @if ($serving_queue->POLY_NAME)
                                    · {{ $serving_queue->POLY_NAME }}
                                @endif
                                @if ($serving_queue->ROOM_NAME)
                                    · {{ $serving_queue->ROOM_NAME }}
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info rounded-4 border-0 px-5 py-4 mb-0" style="font-size:1.25rem;">
                <i class="bi bi-info-circle me-2"></i>Belum ada pasien yang sedang dilayani.
            </div>
            @endif
        </div>

    </div>{{-- /content --}}
</div>{{-- /content-page --}}

{{-- Jam real-time --}}
<style>
    @keyframes pq-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: .3; }
    }

    /* Full-bleed: tembus container/container-fluid/max-width bawaan tema apa pun */
    .content-page {
        overflow-x: hidden;
    }
    .content-page > .container,
    .content-page > .container-fluid,
    .content-page .content {
        max-width: 100% !important;
        width: 100% !important;
    }
    /* Cegah horizontal scroll akibat trik 100vw saat ada scrollbar vertikal */
    body {
        overflow-x: hidden;
    }
</style>
<script>
    (function () {
        function tick() {
            const el = document.getElementById('pq-clock');
            if (!el) return;
            el.textContent = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit'
            });
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>