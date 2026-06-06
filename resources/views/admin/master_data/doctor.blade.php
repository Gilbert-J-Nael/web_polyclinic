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
                    data-bs-toggle="modal" data-bs-target="#modalTambahDokter">
                <i class="bi bi-plus-circle me-1"></i> Tambah Dokter
            </button>
        </div>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
                            </div>
                        </div>

                        <!-- Doctors Table Start -->
                         <table id="doctor" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Dokter</th>
                                    <th>Spesialisasi</th>
                                    <th>Nomor Telefon</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <!-- Fix it when you open -->
                            <tbody>
                                <?php foreach ($doctors as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>{{ !empty($item->DOCTOR_NAME) ? $item->DOCTOR_NAME : '-' }}</td>
                                    <td>{{ !empty($item->SPECIALIZATION) ? $item->SPECIALIZATION : '-' }}</td>
                                    <td>{{ !empty($item->DOCTOR_PHONE) ? $item->DOCTOR_PHONE : '-' }}</td>
                                    <td>{{ !empty($item->DOCTOR_ADDRESS) ? $item->DOCTOR_ADDRESS : '-' }}</td>
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
                        <!-- Doctors Table End -->

                        <div class="modal fade" id="modalTambahDokter" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-person-plus me-2 text-primary"></i>Tambah Dokter Baru
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ url('/master-dokter/store') }}" method="POST">
                            @csrf
                            <div class="modal-body px-4 py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="DOCTOR_NAME" required placeholder="Nama dokter">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Spesialisasi <span class="text-danger">*</span></label>
                                        <select class="form-select" name="SPECIALIZATION_ID" required>
                                            <option value="" disabled selected>Pilih spesialisasi...</option>
                                            @foreach ($specialization as $s)
                                                <option value="{{ $s->SPECIALIZATION_ID }}">{{ $s->SPECIALIZATION }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="DOCTOR_PHONE" required placeholder="08xxxxxxxxxx">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Alamat <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="DOCTOR_ADDRESS" rows="2" required placeholder="Alamat lengkap"></textarea>
                                    </div>
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

            <div class="modal fade" id="modalEditDokter" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Data Dokter
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ url('/master-dokter/update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="DOCTOR_ID" id="edit_doctor_id">
                            <div class="modal-body px-4 py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">ID Dokter</label>
                                        {{-- Read-only: PK tidak boleh diubah --}}
                                        <input type="text" class="form-control" id="edit_doctor_id_display" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="DOCTOR_NAME" id="edit_doctor_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Spesialisasi <span class="text-danger">*</span></label>
                                        <select class="form-select" name="SPECIALIZATION_ID" id="edit_specialization_id" required>
                                            @foreach ($specialization as $s)
                                                <option value="{{ $s->SPECIALIZATION_ID }}">{{ $s->SPECIALIZATION }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="DOCTOR_PHONE" id="edit_doctor_phone" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Alamat <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="DOCTOR_ADDRESS" id="edit_doctor_address" rows="2" required></textarea>
                                    </div>
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

            <div class="modal fade" id="modalHapusDokter" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-bottom px-4 py-3">
                            <h6 class="fw-semibold mb-0 text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>Nonaktifkan Dokter
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ url('/master-dokter/delete') }}" method="POST">
                            @csrf
                            <input type="hidden" name="DOCTOR_ID" id="delete_doctor_id">
                            <div class="modal-body px-4 py-3">
                                <p class="mb-1">Anda akan menonaktifkan dokter:</p>
                                <p class="fw-semibold mb-0" id="delete_doctor_name" style="font-size:1rem"></p>
                                <p class="text-muted small mt-2 mb-0">Data dokter tidak akan dihapus permanen, hanya dinonaktifkan.</p>
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

// Buka modal Edit — HARUS di luar DOMContentLoaded agar bisa dipanggil onclick
function openviewModal(jsonStr) {
    const d = JSON.parse(jsonStr);
    document.getElementById('edit_doctor_id').value         = d.DOCTOR_ID         ?? '';
    document.getElementById('edit_doctor_id_display').value = d.DOCTOR_ID         ?? '';
    document.getElementById('edit_doctor_name').value       = d.DOCTOR_NAME       ?? '';
    document.getElementById('edit_specialization_id').value = d.SPECIALIZATION_ID ?? '';
    document.getElementById('edit_doctor_phone').value      = d.DOCTOR_PHONE      ?? '';
    document.getElementById('edit_doctor_address').value    = d.DOCTOR_ADDRESS    ?? '';
    new bootstrap.Modal(document.getElementById('modalEditDokter')).show();
}

// Buka modal Delete — HARUS di luar DOMContentLoaded
function opendeleteModal(jsonStr) {
    const d = JSON.parse(jsonStr);
    document.getElementById('delete_doctor_id').value         = d.DOCTOR_ID   ?? '';
    document.getElementById('delete_doctor_name').textContent = d.DOCTOR_NAME ?? '-';
    new bootstrap.Modal(document.getElementById('modalHapusDokter')).show();
}
</script>