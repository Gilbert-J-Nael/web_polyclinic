<div class="content-page">
                <div class="content">

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

                    </div> <!-- container-fluid -->
                </div> <!-- content -->