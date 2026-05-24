</div>{{-- /pq-container --}}
</main>{{-- /pq-main --}}

{{-- ── Footer ────────────────────────────────────────────────────── --}}
<footer style="
    background: #fff;
    border-top: 1px solid #e2e8f0;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
">

    {{-- Kiri: nama klinik + copy --}}
    <div style="display:flex; align-items:center; gap:.5rem;">
        <div style="
            width: 24px; height: 24px;
            border-radius: 7px;
            background: #16a34a;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        " aria-hidden="true">
            <i class="bi bi-hospital" style="color:#fff; font-size:12px;"></i>
        </div>
        <span style="font-size: .72rem; font-weight: 600; color: #0f172a; letter-spacing:-.1px;">
            Klinik Sehat
        </span>
        <span style="font-size: .68rem; color: #94a3b8; margin-left: .25rem;">
            &copy; {{ date('Y') }} Sistem Antrean Elektronik
        </span>
    </div>

    {{-- Tengah: info singkat --}}
    <div style="
        display: flex; align-items: center; gap: 1.25rem;
        font-size: .68rem; color: #64748b;
    ">
        <span>
            <i class="bi bi-clock me-1" style="font-size:.7rem;"></i>
            Jam Operasional: 07.00 – 17.00 WIB
        </span>
        <span style="color:#e2e8f0;">|</span>
        <span>
            <i class="bi bi-telephone me-1" style="font-size:.7rem;"></i>
            (0341) 000-0000
        </span>
    </div>

    {{-- Kanan: auto-refresh info --}}
    <div style="
        display: flex; align-items: center; gap: .4rem;
        font-size: .68rem; color: #94a3b8;
    ">
        <i class="bi bi-arrow-clockwise" style="font-size:.72rem;"></i>
        Halaman ini refresh otomatis setiap
        <strong style="color:#64748b;" id="countdown-display">60</strong> detik
    </div>

</footer>

{{-- ── Scripts ────────────────────────────────────────────────────── --}}
{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function () {
    /* ── Live clock & date ──────────────────────────────────────── */
    var DAYS   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var MONTHS = ['Januari','Februari','Maret','April','Mei','Juni',
                  'Juli','Agustus','September','Oktober','November','Desember'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function tick() {
        var now  = new Date();
        var h    = pad(now.getHours());
        var m    = pad(now.getMinutes());
        var s    = pad(now.getSeconds());
        var day  = DAYS[now.getDay()];
        var date = now.getDate();
        var mon  = MONTHS[now.getMonth()];
        var yr   = now.getFullYear();

        var clockEl = document.getElementById('nav-clock');
        var dateEl  = document.getElementById('nav-date');
        if (clockEl) clockEl.textContent = h + ':' + m + ':' + s;
        if (dateEl)  dateEl.textContent  = day + ', ' + date + ' ' + mon + ' ' + yr;
    }

    tick();
    setInterval(tick, 1000);

    /* ── Auto-refresh countdown (60 detik) ──────────────────────── */
    var REFRESH_SEC = 60;
    var remaining   = REFRESH_SEC;
    var cdEl        = document.getElementById('countdown-display');

    setInterval(function () {
        remaining--;
        if (cdEl) cdEl.textContent = remaining;
        if (remaining <= 0) {
            location.reload();
        }
    }, 1000);
})();
</script>

</body>
</html>