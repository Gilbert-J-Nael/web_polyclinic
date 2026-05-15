<div class="content-page">
                <div class="content">

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
                                    <td>{{ !empty($item->JK) ? $item->JK : '-' }}</td>
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
                        
                    </div> <!-- container-fluid -->
                </div> <!-- content -->

