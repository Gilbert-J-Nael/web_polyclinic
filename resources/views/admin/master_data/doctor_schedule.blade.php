<div class="content-page">
                <div class="content">

                    {{-- Alert Notifikasi --}}
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-2" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-2" role="alert">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary rounded-pill px-4"
                    data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal
            </button>
        </div>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
                            </div>
                        </div>

                        <!-- Doctor Schedules Table Start -->
                         <table id="doctorschedule" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Dokter</th>
                                    <th>Hari</th>
                                    <th>Jam Praktek</th>
                                    <!-- <th>Jumlah Slot</th> -->
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>{{ !empty($item->DOCTOR_NAME) ? $item->DOCTOR_NAME : '-' }}</td>
                                    <td>{{ !empty($item->DAY) ? $item->DAY : '-' }}</td>
                                    <td>{{ !empty($item->TIME_START) ? $item->TIME_START : '-' }}</td>
                                    <!-- <td>{{ !empty($item->MAX_SLOT) ? $item->MAX_SLOT : '-' }}</td> -->
                                    <td>
                                        <?php if ($item->IS_ACTIVE == 1): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                onclick="openviewModal(`<?= htmlentities(json_encode($item)) ?>`)"
                                                class="btn btn-warning px-3 py-2 rounded">
                                                Edit <i class="bx bx-edit-alt"></i>
                                            </button>

                                            <button type="button"
                                                onclick="opendeleteModal(`<?= htmlentities(json_encode($item)) ?>`)"
                                                class="btn btn-danger px-3 py-2 rounded">
                                                Delete <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                         </table>
                        <!-- Doctor Schedules Table End -->

                        <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-calendar-plus me-2 text-primary"></i>Tambah Jadwal Dokter
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ url('/master-jadwal-dokter/store') }}" method="POST">
                            @csrf
                            <div class="modal-body px-4 py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Dokter <span class="text-danger">*</span></label>
                                        <select class="form-select" name="DOCTOR_ID" required>
                                            <option value="" disabled selected>Pilih dokter...</option>
                                            @foreach ($doctors as $d)
                                                <option value="{{ $d->DOCTOR_ID }}">
                                                    {{ $d->DOCTOR_NAME }} — {{ $d->SPECIALIZATION ?? '-' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Poliklinik <span class="text-danger">*</span></label>
                                        <select class="form-select" name="POLY_ID" required>
                                            <option value="" disabled selected>Pilih poliklinik...</option>
                                            @foreach ($polys as $p)
                                                <option value="{{ $p->POLY_ID }}">
                                                    {{ $p->POLY_NAME }} ({{ $p->ROOM_NAME ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                        <select class="form-select" name="DAY" required>
                                            <option value="" disabled selected>Pilih hari...</option>
                                            @foreach ($days as $day)
                                                <option value="{{ $day }}">{{ $day }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="TIME_START" id="add_time_start"
       required oninput="autoTimeEnd('add_time_start', 'add_time_end')">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="TIME_END" id="add_time_end" readonly>
                                    </div>
                                    <!-- <div class="col-md-4">
                                        <label class="form-label fw-semibold">Jumlah Slot <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="MAX_SLOT" min="1" placeholder="Contoh: 20">
                                    </div> -->
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 border-top">
                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-plus-circle me-1"></i>Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalEditJadwal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Jadwal Dokter
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ url('/master-jadwal-dokter/update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="SCHEDULE_ID" id="edit_schedule_id">
                            <div class="modal-body px-4 py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Dokter <span class="text-danger">*</span></label>
                                        <select class="form-select" name="DOCTOR_ID" id="edit_doctor_id" required>
                                            @foreach ($doctors as $d)
                                                <option value="{{ $d->DOCTOR_ID }}">
                                                    {{ $d->DOCTOR_NAME }} — {{ $d->SPECIALIZATION ?? '-' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Poliklinik <span class="text-danger">*</span></label>
                                        <select class="form-select" name="POLY_ID" id="edit_poly_id" required>
                                            @foreach ($polys as $p)
                                                <option value="{{ $p->POLY_ID }}">
                                                    {{ $p->POLY_NAME }} ({{ $p->ROOM_NAME ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                        <select class="form-select" name="DAY" id="edit_day" required>
                                            @foreach ($days as $day)
                                                <option value="{{ $day }}">{{ $day }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="TIME_START" id="edit_time_start"
       required oninput="autoTimeEnd('edit_time_start', 'edit_time_end')">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="TIME_END" id="edit_time_end" readonly>
                                    </div>
                                    <!-- <div class="col-md-4">
                                        <label class="form-label fw-semibold">Jumlah Slot <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="MAX_SLOT" id="edit_max_slot" min="1">
                                    </div> -->
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 border-top">
                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning rounded-pill px-4">
                                    <i class="bi bi-save me-1"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalHapusJadwal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h6 class="fw-semibold mb-0 text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>Nonaktifkan Jadwal
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ url('/master-jadwal-dokter/delete') }}" method="POST">
                            @csrf
                            <input type="hidden" name="SCHEDULE_ID" id="delete_schedule_id">
                            <div class="modal-body px-4 py-3">
                                <p class="mb-1">Anda akan menonaktifkan jadwal dokter:</p>
                                <p class="fw-semibold mb-0" id="delete_schedule_info" style="font-size:1rem"></p>
                                <p class="text-muted small mt-2 mb-0">Data tidak akan dihapus permanen, hanya dinonaktifkan.</p>
                            </div>
                            <div class="modal-footer px-4 py-3 border-top">
                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-4">
                                    <i class="bi bi-trash me-1"></i>Ya, Nonaktifkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

                    </div> <!-- container-fluid -->
                </div> <!-- content -->
            </div>

<script>
// Auto-dismiss alert setelah 4 detik
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 4000);
    });
});

// Buka modal Edit
function openviewModal(jsonStr) {
    const s = JSON.parse(jsonStr);
    document.getElementById('edit_schedule_id').value  = s.SCHEDULE_ID ?? '';
    document.getElementById('edit_doctor_id').value    = s.DOCTOR_ID   ?? '';
    document.getElementById('edit_poly_id').value      = s.POLY_ID     ?? '';
    document.getElementById('edit_day').value          = s.DAY         ?? '';
    document.getElementById('edit_time_start').value   = s.TIME_START  ?? '';
    document.getElementById('edit_time_end').value     = s.TIME_END    ?? '';
    new bootstrap.Modal(document.getElementById('modalEditJadwal')).show();
}

// Buka modal Delete
function opendeleteModal(jsonStr) {
    const s = JSON.parse(jsonStr);
    document.getElementById('delete_schedule_id').value        = s.SCHEDULE_ID ?? '';
    document.getElementById('delete_schedule_info').textContent =
        (s.DOCTOR_NAME ?? '-') + ' — ' + (s.DAY ?? '-') + ' ' + (s.TIME_START ?? '');
    new bootstrap.Modal(document.getElementById('modalHapusJadwal')).show();
}

// Auto-hitung TIME_END = TIME_START + 2 jam
function autoTimeEnd(startId, endId) {
    const startVal = document.getElementById(startId).value;
    if (!startVal) return;

    const [hours, minutes] = startVal.split(':').map(Number);
    const endDate = new Date();
    endDate.setHours(hours + 2, minutes, 0);

    const endHH = String(endDate.getHours()).padStart(2, '0');
    const endMM = String(endDate.getMinutes()).padStart(2, '0');
    document.getElementById(endId).value = `${endHH}:${endMM}`;
}
</script>