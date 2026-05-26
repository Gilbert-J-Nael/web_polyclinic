<div class="content-page">
                <div class="content">

                <!-- @if (session('success'))
<div class="alert alert-success alert-dismissible fade show mx-3 mt-2" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif -->

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary rounded-pill px-4"
            data-bs-toggle="modal" data-bs-target="#modalTambahPasien">
        <i class="bi bi-plus-circle me-1"></i> Tambah Pasien
    </button>
</div>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-18 fw-semibold m-0">Dashboard</h4>
                            </div>
                        </div>
                        
                        <!-- Patient Table Start -->
                         <table id="patient" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Pasien</th>
                                    <th>Gender</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Nomor Telefon</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>{{ !empty($item->NIK) ? $item->NIK : '-' }}</td>
                                    <td>{{ !empty($item->PATIENT_NAME) ? $item->PATIENT_NAME : '-' }}</td>
                                    <td>
    @if($item->GENDER === 'Male')
        <span class="badge text-bg-primary fw-normal">Laki-laki</span>
    @elseif($item->GENDER === 'Female')
        <span class="badge text-bg-danger fw-normal">Perempuan</span>
    @else
        -
    @endif
</td>
                                    <td>
                                        {{ !empty($item->BIRTHDATE) 
                                            ? \Carbon\Carbon::parse($item->BIRTHDATE)->age . ' Tahun'
                                            : '-' 
                                        }}
                                    </td>
                                    <td>{{ !empty($item->PHONE) ? $item->PHONE : '-' }}</td>
                                    <td>{{ !empty($item->ADDRESS) ? $item->ADDRESS : '-' }}</td>
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
                        <!-- Patient Table End -->

                         <div class="modal fade" id="modalTambahPasien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h6 class="fw-semibold mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah Pasien Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/master-pasien/store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="PATIENT_NAME" required placeholder="Nama pasien">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK</label>
                            <input type="text" class="form-control" name="NIK" maxlength="16" placeholder="16 digit NIK">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" name="GENDER" required>
    <option value="" disabled selected>Pilih...</option>
    <option value="Male">Laki-laki</option>
    <option value="Female">Perempuan</option>
</select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="BIRTHDATE" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nomor Telepon</label>
                            <input type="text" class="form-control" name="PHONE" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea class="form-control" name="ADDRESS" rows="2" placeholder="Alamat lengkap"></textarea>
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

<div class="modal fade" id="modalEditPasien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h6 class="fw-semibold mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Data Pasien</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pasien.update') }}" method="POST">
                @csrf
                <input type="hidden" name="PATIENT_ID" id="edit_patient_id">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="PATIENT_NAME" id="edit_patient_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK</label>
                            <input type="text" class="form-control" name="NIK" id="edit_nik" maxlength="16">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" name="GENDER" id="edit_gender" required>
    <option value="Male">Laki-laki</option>
    <option value="Female">Perempuan</option>
</select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="BIRTHDATE" id="edit_birthdate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nomor Telepon</label>
                            <input type="text" class="form-control" name="PHONE" id="edit_phone">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea class="form-control" name="ADDRESS" id="edit_address" rows="2"></textarea>
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

<div class="modal fade" id="modalHapusPasien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h6 class="fw-semibold mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Pasien</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pasien.delete') }}" method="POST">
                @csrf
                <input type="hidden" name="PATIENT_ID" id="delete_patient_id">
                <div class="modal-body px-4 py-3">
                    <p class="mb-1">Anda akan menonaktifkan pasien:</p>
                    <p class="fw-semibold mb-0" id="delete_patient_name" style="font-size:1rem"></p>
                    <p class="text-muted small mt-2 mb-0">Data pasien tidak akan dihapus permanen, hanya dinonaktifkan.</p>
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

<script>
// Buka modal Edit — isi field dari data JSON pasien
function openviewModal(jsonStr) {
    const p = JSON.parse(jsonStr);
    document.getElementById('edit_patient_id').value    = p.PATIENT_ID   ?? '';
    document.getElementById('edit_patient_name').value  = p.PATIENT_NAME ?? '';
    document.getElementById('edit_nik').value           = p.NIK          ?? '';
    document.getElementById('edit_gender').value        = p.GENDER       ?? 'Male'; // ← fix
    document.getElementById('edit_birthdate').value     = p.BIRTHDATE    ?? '';
    document.getElementById('edit_phone').value         = p.PHONE        ?? '';
    document.getElementById('edit_address').value       = p.ADDRESS      ?? '';
    new bootstrap.Modal(document.getElementById('modalEditPasien')).show();
}

// Buka modal Delete
function opendeleteModal(jsonStr) {
    const p = JSON.parse(jsonStr);
    document.getElementById('delete_patient_id').value    = p.PATIENT_ID   ?? '';
    document.getElementById('delete_patient_name').textContent = p.PATIENT_NAME ?? '-';
    new bootstrap.Modal(document.getElementById('modalHapusPasien')).show();
}
</script>