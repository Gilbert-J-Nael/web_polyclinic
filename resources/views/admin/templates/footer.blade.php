<!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col fs-13 text-muted text-center">
                                &copy; <script>document.write(new Date().getFullYear())</script> - Made with <span class="mdi mdi-heart text-danger"></span> by <a href="#!" class="text-reset fw-semibold">Zoyothemes</a> 
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->

            </div>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

        <!-- Vendor -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>
        <script src="assets/libs/waypoints/lib/jquery.waypoints.min.js"></script>
        <script src="assets/libs/jquery.counterup/jquery.counterup.min.js"></script>
        <script src="assets/libs/feather-icons/feather.min.js"></script>

        <!-- Apexcharts JS -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- Add Ons -->
        <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>

        <!-- Widgets Init Js -->
        <script src="assets/js/pages/crm-dashboard.init.js"></script>

        <!-- App js-->
        <script src="assets/js/app.js"></script>
        
        <script>
          new DataTable('#example');
new DataTable('#patient');
new DataTable('#doctor');
new DataTable('#doctorschedule');
new DataTable('#history');

document.addEventListener('DOMContentLoaded', function () {

    // ── Keluhan Modal ─────────────────────────────────────────────────
    const keluhanModal = document.getElementById('keluhanModal');
    if (keluhanModal) {
        keluhanModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('keluhanModalText').textContent    = btn.dataset.keluhan;
            document.getElementById('keluhanModalPatient').textContent = btn.dataset.patient;
        });
    }

    // ── Score Calculator ──────────────────────────────────────────────
    (function () {
        const sysInput   = document.getElementById('tekanan_sistolik');
        const diasInput  = document.getElementById('tekanan_diastolik');
        const ageDisplay = document.getElementById('tanggal_lahir_display');
        const slider     = document.getElementById('prioritas_level');
        const checkbox   = document.getElementById('prioritas_check');
        const scoreEl    = document.getElementById('score_value');
        const tensiEl    = document.getElementById('tensi_value');

        if (!sysInput || !diasInput || !ageDisplay || !slider || !checkbox || !scoreEl) return;

        function getReference(age) {
            age = parseInt(age);
            if (age <= 9)  return [95,  112, 56, 71];
            if (age <= 18) return [112, 128, 66, 80];
            if (age <= 59) return [90,  120, 60, 80];
            return [150, 150, 90, 90];
        }

        function calculateScore() {
            const sys     = parseFloat(sysInput.value);
            const dias    = parseFloat(diasInput.value);
            const age     = parseFloat(ageDisplay.textContent);
            const keluhan = parseFloat(slider.value);

            if (!sys || !dias || !age) {
                scoreEl.textContent = '0.00';
                if (tensiEl) tensiEl.textContent = '0.00';
                return;
            }

            const [sysMin, sysMax, diasMin, diasMax] = getReference(age);
            const sysRef  = (sysMin + sysMax) / 2;
            const diasRef = (diasMin + diasMax) / 2;

            const devSys  = Math.abs((sys  - sysRef)  / sysRef  * 100);
            const devDias = Math.abs((dias - diasRef) / diasRef * 100);
            const tensiAdjusted = Math.min((devSys + devDias) / 2 / 100 * 5, 5);

            const keluhanScore = (keluhan / 10) * 5;
            const waitingScore = 0;
            const kondisi      = checkbox.checked ? 1 : 0;

            const finalScore =
                (tensiAdjusted * 0.40) +
                (keluhanScore  * 0.35) +
                (waitingScore  * 0.15) +
                (kondisi       * 0.10);

            scoreEl.textContent = finalScore.toFixed(2);
            if (tensiEl) tensiEl.textContent = tensiAdjusted.toFixed(2);
        }

        [sysInput, diasInput, slider, checkbox].forEach(el => {
            el.addEventListener('input',  calculateScore);
            el.addEventListener('change', calculateScore);
        });
    })();

    // ── Patient Search ────────────────────────────────────────────────
    (function () {
        const input      = document.getElementById('nama_input');
        const dropdown   = document.getElementById('nama_dropdown');
        const hiddenId   = document.getElementById('patient_id');
        const hiddenDOB  = document.getElementById('tanggal_lahir_val');
        const displayDOB = document.getElementById('tanggal_lahir_display');
        const ageUnit    = document.getElementById('age_unit');

        if (!input || !dropdown || !hiddenId || !hiddenDOB || !displayDOB) return;

        let debounceTimer = null;

        input.addEventListener('input', function () {
            const q = this.value.trim();
            clearTimeout(debounceTimer);
            resetSelection();
            if (q.length < 1) { closeDropdown(); return; }
            debounceTimer = setTimeout(() => fetchPatients(q), 300);
        });

        function fetchPatients(q) {
            fetch('/patients/search?q=' + encodeURIComponent(q), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                }
            })
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(renderDropdown)
            .catch(() => showMessage(dropdown, 'Gagal memuat data.', 'danger'));
        }

        function renderDropdown(patients) {
            dropdown.innerHTML = '';
            if (!Array.isArray(patients) || patients.length === 0) {
                showMessage(dropdown, 'Pasien tidak ditemukan.', 'muted');
                return;
            }
            patients.forEach(function (p) {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action py-2 px-3';
                li.style.cursor = 'pointer';
                li.tabIndex = 0;
                li.innerHTML =
                    '<div class="fw-semibold" style="font-size:.85rem">' + escapeHtml(p.PATIENT_NAME) + '</div>' +
                    '<div class="text-muted" style="font-size:.72rem">ID: ' + escapeHtml(String(p.PATIENT_ID)) + '</div>';
                li.addEventListener('mousedown', e => { e.preventDefault(); selectPatient(p); });
                li.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectPatient(p); }
                });
                dropdown.appendChild(li);
            });
            dropdown.classList.remove('d-none');
        }

        function selectPatient(p) {
            input.value     = p.PATIENT_NAME;
            hiddenId.value  = p.PATIENT_ID;
            hiddenDOB.value = p.AGE ?? '';
            if (p.AGE !== null && p.AGE !== undefined) {
                displayDOB.textContent = p.AGE;
                if (ageUnit) ageUnit.style.display = 'inline';
            } else {
                displayDOB.textContent = '—';
                if (ageUnit) ageUnit.style.display = 'none';
            }
            closeDropdown();
            input.focus();
            document.getElementById('tekanan_sistolik')?.dispatchEvent(new Event('input'));
        }

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeDropdown(); return; }
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                navigateDropdown(dropdown, input, e.key);
            }
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) closeDropdown();
        });

        function resetSelection() {
            hiddenId.value = ''; hiddenDOB.value = '';
            displayDOB.textContent = '—';
            if (ageUnit) ageUnit.style.display = 'none';
        }

        function closeDropdown() {
            dropdown.classList.add('d-none');
            dropdown.innerHTML = '';
        }
    })();

    // ── Schedule Card Selection ───────────────────────────────────────
    (function () {
        const hiddenId = document.getElementById('schedule_id');
        const cards    = document.querySelectorAll('.schedule-card');
        if (!hiddenId || !cards.length) return;

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                cards.forEach(c => c.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10'));
                this.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                hiddenId.value = this.dataset.scheduleId;
            });
        });
    })();

    // ── Queue Actions ─────────────────────────────────────────────────
    function post(url, body, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(body)
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(callback)
    .catch(err => {
        console.error('Queue action error:', err);
        alert('Terjadi kesalahan. Silakan refresh halaman.');
    });
}

    // ── Unified 2-button confirm modal ────────────────────────────────
    // Modal HTML must have EXACTLY this structure in footer:
    // <button class="btn-action-left ...">  ← left action button
    // <button class="btn-action-right ..."> ← right action button (cancel or secondary)
    // ── Confirm Modal ─────────────────────────────────────────────────
let _confirmModal = null;

function showConfirmModal(title, text, buttons) {
    // buttons = [{label, class, action}, ...]
    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalText').textContent  = text;

    const footer = document.querySelector('#confirmModal .modal-footer');
    footer.innerHTML = '';

    buttons.forEach(function(btn) {
        const el = document.createElement('button');
        el.type = 'button';
        el.className = 'btn btn-sm ' + btn.class + ' rounded-pill px-3';
        el.textContent = btn.label;
        el.addEventListener('click', function() {
            if (_confirmModal) _confirmModal.hide();
            if (btn.action) btn.action();
        });
        footer.appendChild(el);
    });

    if (_confirmModal) {
        _confirmModal.dispose();
    }
    _confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'), {
        backdrop: true,
        keyboard: true
    });
    _confirmModal.show();
}

// ── Panggil ───────────────────────────────────────────────────────
window.confirmPanggil = function(id) {
    showConfirmModal(
        'Konfirmasi Kehadiran',
        'Apakah pasien hadir?',
        [
            {
                label: 'Hadir',
                class: 'btn-primary',
                action: function() {
                    post('/queue/panggil', { queue_id: id }, () => location.reload());
                }
            },
            {
                label: 'Tidak Hadir',
                class: 'btn-warning',
                action: function() {
                    post('/queue/missed', { queue_id: id }, () => location.reload());
                }
            },
            {
                label: 'Batal',
                class: 'btn-secondary',
                action: null
            }
        ]
    );
};

// ── Selesai ───────────────────────────────────────────────────────
window.confirmSelesai = function(id) {
    showConfirmModal(
        'Konfirmasi Selesai',
        'Konfirmasi Antrean Selesai?',
        [
            {
                label: 'Ya, Selesai',
                class: 'btn-success',
                action: function() {
                    post('/queue/selesai', { queue_id: id }, () => location.reload());
                }
            },
            {
                label: 'Batal',
                class: 'btn-secondary',
                action: null
            }
        ]
    );
};

    // ── Shared Helpers ────────────────────────────────────────────────
    function navigateDropdown(dropdown, input, key) {
        const items = [...dropdown.querySelectorAll('.list-group-item-action')];
        if (!items.length) return;
        const idx = items.indexOf(document.activeElement);
        if (idx === -1) { items[key === 'ArrowDown' ? 0 : items.length - 1].focus(); }
        else {
            const next = key === 'ArrowDown' ? idx + 1 : idx - 1;
            if (items[next]) items[next].focus(); else input.focus();
        }
    }

    function showMessage(dropdown, msg, type) {
        dropdown.innerHTML = `<li class="list-group-item text-${type} small py-2 px-3">${escapeHtml(msg)}</li>`;
        dropdown.classList.remove('d-none');
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

}); // end DOMContentLoaded
        </script>

    </body>

</html>