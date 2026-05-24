{{-- resources/views/patient/dashboard.blade.php --}}

<div class="content-page">
    <div class="content">

        {{-- Header --}}
        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column mb-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Antrean Hari Ini</h4>
                <p class="text-muted mb-0 small">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-body-secondary"
                     style="font-size:.8rem; font-weight:500;">
                    <span class="d-inline-block rounded-circle bg-success"
                          style="width:8px;height:8px;animation:pq-pulse 1.5s infinite;"></span>
                    <span id="pq-clock">--:--</span>
                </div>
                <button onclick="location.reload()"
                        class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
        </div>

        {{-- Sedang Dilayani --}}
        @if ($serving_queue)
        <div class="card border-0 rounded-4 mb-3"
             style="border: 2px solid #22c55e !important; background: var(--bs-body-bg);">
            <div class="card-body px-4 py-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div>
                        <div class="text-success fw-semibold mb-1"
                             style="font-size:.68rem; letter-spacing:.08em; text-transform:uppercase;">
                            <i class="bi bi-activity me-1"></i>Sedang Dilayani
                        </div>
                        <div class="fw-bold" style="font-size:2.5rem; line-height:1; letter-spacing:-1px;">
                            {{ $serving_queue->QUEUE_NUMBER }}
                        </div>
                    </div>
                    <div class="vr d-none d-sm-block" style="height:50px;"></div>
                    <div>
                        <div class="fw-semibold" style="font-size:.9rem;">
                            {{ $serving_queue->PATIENT_NAME ?? '-' }}
                        </div>
                        <div class="text-muted" style="font-size:.75rem;">
                            Menunggu {{ $serving_queue->waiting_minutes }} menit
                        </div>
                        @if ($serving_queue->DOCTOR_NAME)
                        <div class="text-muted mt-1" style="font-size:.73rem;">
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
        <div class="alert alert-info rounded-4 border-0 px-4 py-3 mb-3" style="font-size:.82rem;">
            <i class="bi bi-info-circle me-1"></i>Belum ada pasien yang sedang dilayani.
        </div>
        @endif

        {{-- Fixed Queue List --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom px-3 py-3 d-flex align-items-center gap-2">
                <h6 class="fw-semibold mb-0">Antrean Berikutnya</h6>
                <span class="badge text-bg-primary fw-normal ms-auto" style="font-size:.65rem;">
                    Total {{ $total_waiting }} menunggu
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.78rem;">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-3 py-2 text-uppercase text-muted fw-semibold"
                                    style="font-size:.65rem; width:36px;">#</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold"
                                    style="font-size:.65rem;">No. Antrean / Nama</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center"
                                    style="font-size:.65rem;">Kondisi</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center"
                                    style="font-size:.65rem;">Dokter / Poli</th>
                                <th class="pe-3 py-2 text-uppercase text-muted fw-semibold text-center"
                                    style="font-size:.65rem;">Waktu Tunggu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fixed_queue as $i => $q)
                            <tr @if($q->QUEUE_STATUS === 'Dilayani') class="table-success" @endif>
                                {{-- Nomor urut posisi --}}
                                <td class="ps-3">
                                    @if ($q->QUEUE_STATUS === 'Dilayani')
                                        <span class="badge bg-success rounded-circle p-1"
                                              style="font-size:.6rem; width:20px; height:20px; display:inline-flex; align-items:center; justify-content:center;">
                                            <i class="bi bi-activity"></i>
                                        </span>
                                    @elseif ($i === 0 || ($fixed_queue->where('QUEUE_STATUS','Dilayani')->count() > 0 && $i === 1))
                                        <span class="badge text-bg-primary rounded-circle"
                                              style="font-size:.65rem; width:20px; height:20px; display:inline-flex; align-items:center; justify-content:center;">
                                            {{ $i + 1 }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:.75rem; padding-left:4px;">
                                            {{ $i + 1 }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Nomor & Nama --}}
                                <td>
                                    <div style="line-height:1.3;">
                                        <div class="text-muted" style="font-size:.68rem;">{{ $q->QUEUE_NUMBER }}</div>
                                        <div class="fw-semibold" style="font-size:.8rem;">{{ $q->PATIENT_NAME ?? '-' }}</div>
                                    </div>
                                </td>

                                {{-- Kondisi Khusus --}}
                                <td class="text-center">
                                    @if ($q->SPECIAL_CONDITION_SCORE)
                                        <span class="badge text-bg-danger fw-normal rounded-pill px-2"
                                              style="font-size:.65rem;">Prioritas</span>
                                    @elseif ($q->QUEUE_STATUS === 'Dilayani')
                                        <span class="badge text-bg-success fw-normal rounded-pill px-2"
                                              style="font-size:.65rem;">Dilayani</span>
                                    @else
                                        <span class="text-muted" style="font-size:.72rem;">—</span>
                                    @endif
                                </td>

                                {{-- Dokter / Poli --}}
                                <td class="text-center text-muted" style="font-size:.72rem;">
                                    @if ($q->DOCTOR_NAME)
                                        <div>{{ $q->DOCTOR_NAME }}</div>
                                        <div style="font-size:.65rem; color:var(--bs-secondary);">
                                            {{ $q->POLY_NAME ?? '' }}
                                            @if($q->ROOM_NAME) · {{ $q->ROOM_NAME }} @endif
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Waktu Tunggu --}}
                                <td class="pe-3 text-center text-muted" style="font-size:.72rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $q->waiting_minutes }} menit
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox d-block mb-2" style="font-size:1.5rem;"></i>
                                    Tidak ada antrean aktif saat ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top px-3 py-2">
                <span class="text-muted" style="font-size:.72rem;">
                    Menampilkan <strong>{{ $fixed_queue->count() }}</strong> antrean teratas
                    dari <strong>{{ $total_waiting }}</strong> yang menunggu
                </span>
            </div>
        </div>

    </div>{{-- /content --}}
</div>{{-- /content-page --}}

{{-- Jam real-time --}}
<style>
    @keyframes pq-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: .3; }
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