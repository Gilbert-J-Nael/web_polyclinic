<div class="content-page">
    <div class="content">

        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
    </div>
    <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
    </button>
</div>

{{-- Keluhan Modal --}}
<div class="modal fade" id="keluhanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <div>
                    <h6 class="fw-semibold mb-0">Keluhan Pasien</h6>
                    <p class="text-muted mb-0 small" id="keluhanModalPatient"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="mb-0" id="keluhanModalText" style="line-height:1.7"></p>
            </div>
        </div>
    </div>
</div>

{{-- ✅ Confirm Modal (Panggil & Selesai) --}}
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-bottom px-4 py-3">
                        <h6 class="fw-semibold mb-0" id="confirmModalTitle"></h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <p class="mb-0" id="confirmModalText"></p>
                    </div>
                    <div class="modal-footer px-4 py-3">
                        {{-- Buttons injected dynamically by JS --}}
                    </div>
                </div>
            </div>
        </div>

{{-- Queue Tables --}}
<div style="display:flex; width:100%; gap:1rem;">

    {{-- Fixed Queue --}}
    <div style="flex:1; min-width:0;">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-bottom px-3 py-3">
                <h6 class="fw-semibold mb-0">Fixed Queue <span class="badge text-bg-primary fw-normal ms-1" style="font-size:.65rem">Visible</span></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.78rem">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-3 py-2 text-uppercase text-muted fw-semibold" style="font-size:.65rem">No. Antrean / Nama</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Kondisi Khusus</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Tensi (S/D)</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Waiting</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Skor</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Keluhan</th>
                                <th class="pe-3 py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fixed_queue as $i => $q)
                            <tr>
                                <td class="ps-3">
                                    <div style="line-height:1.3">
                                        <div class="text-muted" style="font-size:.68rem">{{ $q->QUEUE_NUMBER }}</div>
                                        <div class="fw-semibold" style="font-size:.8rem">{{ $q->PATIENT_NAME ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($q->SPECIAL_CONDITION_SCORE)
                                        <span class="badge text-bg-danger fw-normal rounded-pill px-2" style="font-size:.65rem">Prioritas</span>
                                    @else
                                        <span class="text-muted" style="font-size:.72rem">—</span>
                                    @endif
                                </td>
                                <td class="text-center text-muted" style="font-size:.72rem">
                                    {{ $q->SYSTOLIC }}/{{ $q->DIASTOLIC }}
                                    <div style="font-size:.65rem; color:var(--bs-secondary)">
                                        skor: {{ number_format($q->tensi_score, 2) }}
                                    </div>
                                </td>
                                <td class="text-center text-muted" style="font-size:.72rem">
                                    {{ $q->waiting_minutes }} menit
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold" style="font-size:.78rem">
                                        {{ number_format($q->dynamic_score, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if (!empty($q->COMPLAINTS))
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0"
                                        style="font-size:.68rem"
                                        data-bs-toggle="modal"
                                        data-bs-target="#keluhanModal"
                                        data-keluhan="{{ $q->COMPLAINTS }}"
                                        data-patient="{{ $q->PATIENT_NAME ?? '-' }}">
                                        <i class="bi bi-chat-left-text me-1"></i>Lihat
                                    </button>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-center">
                                            @if ($q->QUEUE_STATUS === 'Dilayani')
                                                {{-- ✅ Sudah dipanggil → tampilkan Selesai --}}
                                                <button class="btn btn-sm btn-success rounded-pill px-3"
                                                    style="font-size:.72rem"
                                                    onclick="confirmSelesai({{ $q->QUEUE_ID }})">
                                                    <i class="bi bi-check-circle me-1"></i>Selesai
                                                </button>
                                            @elseif ($i === 0)
                                                {{-- ✅ Antrean pertama → tampilkan Panggil --}}
                                                <button class="btn btn-sm btn-primary rounded-pill px-3"
                                                    style="font-size:.72rem"
                                                    onclick="confirmPanggil({{ $q->QUEUE_ID }})">
                                                    <i class="bi bi-megaphone me-1"></i>Panggil
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox d-block mb-1"></i>Tidak ada antrean.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top px-3 py-2">
                <span class="text-muted" style="font-size:.72rem">
                    Menampilkan <strong>{{ $fixed_queue->count() }}</strong> dari
                    <strong>{{ $remaining_queue_count }}</strong> antrean
                </span>
            </div>
        </div>
    </div>

    {{-- Shadow Queue --}}
    <div style="flex:1; min-width:0;">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-bottom px-3 py-3">
                <h6 class="fw-semibold mb-0">Shadow Queue
                    <span class="badge text-bg-secondary fw-normal ms-1" style="font-size:.65rem">Admin</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.78rem">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-3 py-2 text-uppercase text-muted fw-semibold" style="font-size:.65rem">No. Antrean / Nama</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Kondisi Khusus</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Tensi (S/D)</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Waiting</th>
                                <th class="py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Skor</th>
                                <th class="pe-3 py-2 text-uppercase text-muted fw-semibold text-center" style="font-size:.65rem">Keluhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($shadow_queue as $q)
                            <tr>
                                <td class="ps-3">
                                    <div style="line-height:1.3">
                                        <div class="text-muted" style="font-size:.68rem">{{ $q->QUEUE_NUMBER }}</div>
                                        <div class="fw-semibold" style="font-size:.8rem">{{ $q->PATIENT_NAME ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($q->SPECIAL_CONDITION_SCORE)
                                        <span class="badge text-bg-danger fw-normal rounded-pill px-2" style="font-size:.65rem">Prioritas</span>
                                    @else
                                        <span class="text-muted" style="font-size:.72rem">—</span>
                                    @endif
                                </td>
                                <td class="text-center text-muted" style="font-size:.72rem">
                                    {{ $q->SYSTOLIC }}/{{ $q->DIASTOLIC }}
                                    <div style="font-size:.65rem; color:var(--bs-secondary)">
                                        skor: {{ number_format($q->tensi_score, 2) }}
                                    </div>
                                </td>
                                <td class="text-center text-muted" style="font-size:.72rem">
                                    {{ $q->waiting_minutes }} menit
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold" style="font-size:.78rem">
                                        {{ number_format($q->dynamic_score, 2) }}
                                    </span>
                                </td>
                                <td class="pe-3 text-center">
                                    @if (!empty($q->COMPLAINTS))
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0"
                                        style="font-size:.68rem"
                                        data-bs-toggle="modal"
                                        data-bs-target="#keluhanModal"
                                        data-keluhan="{{ $q->COMPLAINTS }}"
                                        data-patient="{{ $q->PATIENT_NAME ?? '-' }}">
                                        <i class="bi bi-chat-left-text me-1"></i>Lihat
                                    </button>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox d-block mb-1"></i>Tidak ada antrean.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top px-3 py-2">
                <span class="text-muted" style="font-size:.72rem">
                    Total: <strong>{{ $shadow_queue->count() }}</strong> antrean
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Form Daftar Pasien --}}
<div class="row g-3 mb-3 mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Data Pasien</h5>
            </div>
            <div class="card-body">
                <form action="/polyclinic-queue/create" method="POST">
                @csrf
                <div class="row g-3">

                    {{-- Nama --}}
                    <div class="col-md-4">
                        <label for="nama_input" class="form-label fw-semibold">Nama</label>
                        <input type="hidden" id="patient_id" name="patient_id">
                        <input type="hidden" id="tanggal_lahir_val" name="tanggal_lahir">
                        <div class="position-relative">
                            <input type="text" class="form-control" id="nama_input"
                                placeholder="Nama (E.g. Sulis)" autocomplete="off"
                                value="{{ old('nama') }}">
                            <ul class="list-group shadow-sm position-absolute w-100 z-3 mt-1 d-none"
                                id="nama_dropdown"
                                style="max-height:220px; overflow-y:auto; border-radius:.5rem;"></ul>
                        </div>
                    </div>

                    {{-- Tekanan Darah --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tekanan Darah</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" class="form-control text-center"
                                id="tekanan_sistolik" name="tekanan_sistolik"
                                placeholder="—" style="width:70px;"
                                value="{{ old('tekanan_sistolik') }}">
                            <span class="text-muted small">mmHg /</span>
                            <input type="number" class="form-control text-center"
                                id="tekanan_diastolik" name="tekanan_diastolik"
                                placeholder="—" style="width:70px;"
                                value="{{ old('tekanan_diastolik') }}">
                            <span class="text-muted small">mmHg</span>
                        </div>
                    </div>

                    {{-- Umur --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Umur</label>
                        <div class="d-flex align-items-center" style="height:38px;">
                            <span class="text-dark fw-semibold" id="tanggal_lahir_display">—</span>
                            <span class="text-muted ms-1 small" id="age_unit" style="display:none">tahun</span>
                        </div>
                    </div>

                    {{-- Jadwal --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Jadwal Tersedia Hari Ini</label>
                        <input type="hidden" id="schedule_id" name="schedule_id">
                        @if ($available_schedules->isEmpty())
                            <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:.82rem">
                                <i class="bi bi-calendar-x me-1"></i>Tidak ada jadwal tersedia saat ini.
                            </div>
                        @else
                            <div class="row g-2" id="schedule_list">
                                @foreach ($available_schedules as $s)
                                <div class="col-md-4">
                                    <div class="schedule-card card border rounded-3 px-3 py-2 h-100"
                                        style="cursor:pointer; transition:all .15s;"
                                        data-schedule-id="{{ $s->SCHEDULE_ID }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-person-badge text-primary mt-1" style="font-size:1rem"></i>
                                            <div>
                                                <div class="fw-semibold" style="font-size:.82rem">{{ $s->DOCTOR_NAME ?? '-' }}</div>
                                                <div class="text-muted" style="font-size:.72rem">
                                                    {{ $s->POLY_NAME ?? '-' }}
                                                    @if($s->ROOM_NAME) · {{ $s->ROOM_NAME }} @endif
                                                </div>
                                                <span class="badge text-bg-primary fw-normal mt-1" style="font-size:.65rem">
                                                    {{ substr($s->TIME_START,0,5) }} – {{ substr($s->TIME_END,0,5) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Kondisi Khusus --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <div class="form-check" style="height:38px; display:flex; align-items:center;">
                            <input class="form-check-input" type="checkbox"
                                id="prioritas_check" name="is_prioritas" value="1"
                                {{ old('is_prioritas') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold ms-2" for="prioritas_check">
                                Kondisi Khusus / Prioritas
                            </label>
                        </div>
                    </div>

                    {{-- Keluhan Score Slider --}}
                    <div class="col-md-4">
                        <label for="prioritas_level" class="form-label fw-semibold">
                            Tingkat Keluhan <span class="text-muted fw-normal">(0–10)</span>
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold" id="prioritas_value" style="min-width:20px;">0</span>
                            <input type="range" class="form-range"
                                id="prioritas_level" name="prioritas_level"
                                min="0" max="10" step="1"
                                value="{{ old('prioritas_level', 0) }}"
                                oninput="document.getElementById('prioritas_value').textContent = this.value">
                        </div>
                    </div>

                    {{-- Keluhan Text --}}
                    <div class="col-12">
                        <label for="keluhan" class="form-label fw-semibold">Keluhan</label>
                        <textarea class="form-control" id="keluhan" name="keluhan" rows="4"
                            placeholder="Tuliskan keluhan pasien di sini...">{{ old('keluhan') }}</textarea>
                    </div>

                    {{-- Score Preview --}}
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted small fw-semibold">
                                Skor Prioritas (Preview): <span id="score_value" class="text-dark fw-bold">0.00</span>
                            </span>
                            <span class="text-muted small fw-semibold">
                                Skor Tensi: <span id="tensi_value" class="text-dark">0.00</span>
                            </span>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-plus-circle me-1"></i>Daftarkan Antrean
                        </button>
                    </div>

                </div>
                </form>
            </div>
        </div>
    </div>
</div>

        </div> <!-- container-fluid -->
    </div> <!-- content -->