<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title text-danger">
            <i class="bi bi-trash3-fill me-2"></i>
            Pembersihan &amp; Reset Data Uji Coba
        </h1>
        <p class="page-subtitle text-muted mb-0">
            Alat pembersihan terpusat khusus Administrator untuk menghapus berkas usulan, ketetapan, dan data uji coba per tahun anggaran atau per kategori modul dengan proteksi keamanan dinamis.
        </p>
    </div>
    <div>
        <a href="<?= site_url('ssh/penetapan') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Penetapan
        </a>
    </div>
</div>

<!-- Alert Banner: Peringatan & Panduan Keamanan Dinamis -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border-radius: 12px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-danger bg-opacity-25 text-danger p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 24px;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-white mb-1">Proteksi Keamanan Berlapis (Dynamic Sudo-Mode)</h5>
                        <p class="text-white-50 small mb-2">
                            Pembersihan data akan menghapus berkas usulan, riwayat log, serta berkas fisik lampiran secara <strong>permanen</strong> dari server. Untuk mencegah kesalahan eksekusi, sistem menerapkan 3 lapisan pengamanan:
                        </p>
                        <ul class="text-white-50 small mb-0 ps-3">
                            <li><strong>Hak Akses Tunggal:</strong> Hanya dapat dibuka dan dieksekusi oleh akun Administrator.</li>
                            <li><strong>Password Dinamis Waktu Server:</strong> Menggunakan kombinasi <code>[2-Digit Jam][2-Digit Bulan][2-Digit Tanggal]</code> sesuai waktu server saat ini.</li>
                            <li><strong>Konfirmasi Teks Eksplisit:</strong> Wajib mengetik kata kunci <code>HAPUS-[TAHUN]</code> sebelum tombol submit dapat diproses.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="bg-white bg-opacity-10 border border-white border-opacity-10 rounded-3 p-3 text-start">
                    <div class="text-white-50 small text-uppercase fw-semibold mb-1">
                        <i class="bi bi-clock-history text-warning me-1"></i> Waktu Server Saat Ini
                    </div>
                    <div class="h4 text-warning fw-bold font-monospace mb-1" id="liveServerClockPembersihan">
                        <?= date('H:i:s') ?> WITA
                    </div>
                    <div class="text-white small">
                        <?= date('d M Y') ?> (Asia/Singapore / WITA)
                    </div>
                    <hr class="border-white border-opacity-25 my-2">
                    <div class="small text-white-50">
                        Formula Password: <code class="text-warning fw-bold"><?= date('H') ?></code> (Jam) + <code class="text-warning fw-bold"><?= date('m') ?></code> (Bln) + <code class="text-warning fw-bold"><?= date('d') ?></code> (Tgl) 
                        &rarr; <span class="badge bg-warning text-dark font-monospace fw-bold"><?= date('Hmd') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="<?= site_url('master/pembersihan') ?>" method="POST" id="formPembersihanData">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

    <div class="row g-4">
        <!-- Kolom Kiri: Pilihan Target & Pratinjau Dampak -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-funnel-fill text-primary"></i> 1. Tentukan Parameter Target Pembersihan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <!-- Pilihan Tahun Anggaran -->
                        <div class="col-md-6">
                            <label for="selectTahun" class="form-label fw-bold small text-muted">
                                <i class="bi bi-calendar-event me-1 text-primary"></i> TAHUN ANGGARAN
                            </label>
                            <select class="form-select form-select-lg fw-bold" id="selectTahun" name="tahun">
                                <?php foreach ($allYears as $y): ?>
                                    <option value="<?= $y ?>" <?= ($y === (int)$tahun) ? 'selected' : '' ?>>
                                        Tahun Anggaran <?= $y ?> <?= ($y == 2028) ? '(Uji Coba)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Pilih tahun anggaran berkas yang akan dibersihkan.</small>
                        </div>

                        <!-- Pilihan Kategori / Modul -->
                        <div class="col-md-6">
                            <label for="selectTipe" class="form-label fw-bold small text-muted">
                                <i class="bi bi-tags-fill me-1 text-primary"></i> KATEGORI / MODUL
                            </label>
                            <select class="form-select form-select-lg fw-bold" id="selectTipe" name="tipe">
                                <option value="ALL" <?= ($tipe === 'ALL') ? 'selected' : '' ?>>
                                    Semua Kategori (SSH &amp; SBU) - Pembersihan Masal
                                </option>
                                <option value="SSH" <?= ($tipe === 'SSH') ? 'selected' : '' ?>>
                                    Standar Satuan Harga (SSH) Saja
                                </option>
                                <option value="SBU" <?= ($tipe === 'SBU') ? 'selected' : '' ?>>
                                    Standar Biaya Umum (SBU) Saja
                                </option>
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Pilih modul spesifik atau pembersihan massal.</small>
                        </div>
                    </div>

                    <!-- Statistik Dampak Real-Time (Live Metric Cards) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted mb-2 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-bar-chart-fill me-1 text-primary"></i> ESTIMASI BERKAS YANG AKAN DIHAPUS</span>
                            <span id="loadingStats" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                        </label>

                        <div class="row g-2">
                            <!-- Metric 1: Surat Usulan -->
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center border bg-light h-100">
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Surat Usulan</div>
                                    <div class="h3 fw-bold text-dark my-1 font-monospace" id="statSurat"><?= number_format($stats['surat'], 0, ',', '.') ?></div>
                                    <small class="text-muted" style="font-size: 11px;">Berkas SKPD</small>
                                </div>
                            </div>
                            <!-- Metric 2: Rincian Usulan -->
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center border bg-light h-100">
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Rincian Usulan</div>
                                    <div class="h3 fw-bold text-danger my-1 font-monospace" id="statUsulan"><?= number_format($stats['usulan'], 0, ',', '.') ?></div>
                                    <small class="text-muted" style="font-size: 11px;">Item Usulan</small>
                                </div>
                            </div>
                            <!-- Metric 3: Lampiran Fisik -->
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center border bg-light h-100">
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Lampiran Fisik</div>
                                    <div class="h3 fw-bold text-warning my-1 font-monospace" id="statFiles"><?= number_format($stats['files'], 0, ',', '.') ?></div>
                                    <small class="text-muted" style="font-size: 11px;">PDF &amp; Survey</small>
                                </div>
                            </div>
                            <!-- Metric 4: Master Katalog -->
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center border bg-light h-100">
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Master Katalog</div>
                                    <div class="h3 fw-bold text-primary my-1 font-monospace" id="statMaster"><?= number_format($stats['master'], 0, ',', '.') ?></div>
                                    <small class="text-muted" style="font-size: 11px;">Item Referensi</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist Cakupan Pembersihan -->
                    <div class="p-3 rounded-3 bg-light border">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="checkScopeUsulan" checked disabled>
                            <label class="form-check-label fw-bold text-dark small" for="checkScopeUsulan">
                                Hapus Berkas Usulan, Surat Pengantar, Lampiran Fisik &amp; Log Aktivitas (Wajib)
                            </label>
                            <div class="text-muted" style="font-size: 11px;">
                                Menghapus baris pada <code>standar_harga_usulan</code>, <code>standar_harga_surat</code>, <code>standar_harga_log</code>, dan seluruh berkas file fisik di folder <code>uploads/ssh_sbu/</code>.
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkHapusMaster" name="hapus_master" value="1">
                            <label class="form-check-label fw-bold text-danger small" for="checkHapusMaster">
                                Ikut Bersihkan Data di Katalog Master Standar Harga (ref_standar_harga) untuk Tahun ini
                            </label>
                            <div class="text-muted" style="font-size: 11px;">
                                Centang opsi ini jika item di Katalog Master untuk tahun yang dipilih juga merupakan data uji coba yang ingin dihapus kembali. Jika tidak dicentang, Katalog Master tetap utuh.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Otentikasi Password Dinamis & Konfirmasi -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm border-top border-4 border-danger h-100" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-danger mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-key-fill"></i> 2. Verifikasi Keamanan Dinamis
                    </h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Box Panduan Password Dinamis -->
                        <div class="alert alert-warning border-0 p-3 mb-3 small">
                            <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                                <i class="bi bi-info-circle-fill text-warning"></i> Aturan Password Dinamis:
                            </div>
                            <div class="text-muted">
                                Masukkan <strong>Jam</strong>, <strong>Bulan</strong>, dan <strong>Tanggal</strong> waktu server saat ini.
                                <ul class="mb-1 ps-3 mt-1">
                                    <li>Format: <code>[Jam 2 digit][Bulan 2 digit][Tanggal 2 digit]</code></li>
                                    <li>Contoh saat ini (pukul <span class="fw-bold font-monospace"><?= date('H:i') ?> WITA</span> tgl <span class="fw-bold"><?= date('d/m') ?></span>): 
                                        <code class="fw-bold text-danger fs-6 bg-white px-1.5 py-0.5 rounded border"><?= date('Hmd') ?></code>
                                    </li>
                                </ul>
                                <span class="badge bg-secondary-subtle text-secondary">Toleransi transisi &plusmn;1 jam aktif otomatis</span>
                            </div>
                        </div>

                        <!-- Input 1: Dynamic Password -->
                        <div class="mb-3">
                            <label for="inputDynamicPassword" class="form-label fw-bold small text-dark">
                                <i class="bi bi-lock-fill text-danger me-1"></i> PASSWORD KEAMANAN DINAMIS:
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg font-monospace fw-bold" 
                                       id="inputDynamicPassword" 
                                       name="security_password" 
                                       placeholder="Contoh: <?= date('Hmd') ?>" 
                                       autocomplete="off" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="btnTogglePassword" title="Lihat/Sembunyikan Password">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Lihat jam server di top bar pojok kanan atas layar.</small>
                        </div>

                        <!-- Input 2: Teks Konfirmasi Eksplisit -->
                        <div class="mb-4">
                            <label for="inputKonfirmasiTeks" class="form-label fw-bold small text-dark">
                                <i class="bi bi-check2-square text-danger me-1"></i> KETIK KONFIRMASI:
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg font-monospace fw-bold text-uppercase" 
                                   id="inputKonfirmasiTeks" 
                                   name="konfirmasi_teks" 
                                   placeholder="HAPUS-<?= (int)$tahun ?>" 
                                   autocomplete="off" 
                                   required>
                            <small class="text-muted" style="font-size: 11px;">
                                Ketik persis: <span class="badge bg-danger font-monospace" id="badgeKonfirmasiTeks">HAPUS-<?= (int)$tahun ?></span>
                            </small>
                        </div>
                    </div>

                    <!-- Tombol Eksekusi Merah -->
                    <div>
                        <div class="d-grid">
                            <button type="button" class="btn btn-danger btn-lg fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnEksekusiPembersihan">
                                <i class="bi bi-trash3-fill"></i>
                                <span>Eksekusi Pembersihan Permanen</span>
                            </button>
                        </div>
                        <p class="text-center text-muted small mt-2 mb-0" style="font-size: 11px;">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Data yang dihapus tidak dapat dipulihkan kembali.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectTahun = document.getElementById('selectTahun');
    const selectTipe = document.getElementById('selectTipe');
    const loadingStats = document.getElementById('loadingStats');
    const badgeKonfirmasiTeks = document.getElementById('badgeKonfirmasiTeks');
    const inputKonfirmasiTeks = document.getElementById('inputKonfirmasiTeks');
    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const inputDynamicPassword = document.getElementById('inputDynamicPassword');
    const btnEksekusi = document.getElementById('btnEksekusiPembersihan');
    const formPembersihan = document.getElementById('formPembersihanData');

    const statSurat = document.getElementById('statSurat');
    const statUsulan = document.getElementById('statUsulan');
    const statFiles = document.getElementById('statFiles');
    const statMaster = document.getElementById('statMaster');

    // Toggle Visibility Password
    btnTogglePassword.addEventListener('click', function () {
        const type = inputDynamicPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        inputDynamicPassword.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '<i class="bi bi-eye-fill"></i>' : '<i class="bi bi-eye-slash-fill"></i>';
    });

    // Update Placeholder & Badge Teks Konfirmasi saat Tahun berubah
    function updateKonfirmasiBadge() {
        const thn = selectTahun.value;
        const targetText = 'HAPUS-' + thn;
        badgeKonfirmasiTeks.textContent = targetText;
        inputKonfirmasiTeks.setAttribute('placeholder', targetText);
    }

    // Fetch Statistik Dampak via AJAX saat Tahun atau Tipe berubah
    function refreshStats() {
        const thn = selectTahun.value;
        const tipe = selectTipe.value;

        updateKonfirmasiBadge();
        loadingStats.classList.remove('d-none');

        fetch('<?= site_url("master/ajax_pembersihan_stats") ?>?tahun=' + encodeURIComponent(thn) + '&tipe=' + encodeURIComponent(tipe))
            .then(res => res.json())
            .then(data => {
                loadingStats.classList.add('d-none');
                if (data.success && data.stats) {
                    statSurat.textContent = Number(data.stats.surat).toLocaleString('id-ID');
                    statUsulan.textContent = Number(data.stats.usulan).toLocaleString('id-ID');
                    statFiles.textContent = Number(data.stats.files).toLocaleString('id-ID');
                    statMaster.textContent = Number(data.stats.master).toLocaleString('id-ID');
                }
            })
            .catch(err => {
                loadingStats.classList.add('d-none');
                console.error('Gagal mengambil statistik:', err);
            });
    }

    selectTahun.addEventListener('change', refreshStats);
    selectTipe.addEventListener('change', refreshStats);

    // Live Clock pada Banner Pembersihan
    const liveClockEl = document.getElementById('liveServerClockPembersihan');
    if (liveClockEl) {
        setInterval(function () {
            const topClock = document.getElementById('serverClockText');
            if (topClock) {
                liveClockEl.textContent = topClock.textContent + ' WITA';
            }
        }, 1000);
    }

    // Konfirmasi SweetAlert2 sebelum Submit
    btnEksekusi.addEventListener('click', function (e) {
        e.preventDefault();

        const pwd = inputDynamicPassword.value.trim();
        const konfirmasi = inputKonfirmasiTeks.value.trim().toUpperCase();
        const expected = 'HAPUS-' + selectTahun.value;

        if (!pwd) {
            Swal.fire({
                icon: 'warning',
                title: 'Password Belum Diisi',
                text: 'Harap masukkan password keamanan dinamis [Jam][Bulan][Tanggal] sesuai waktu server saat ini.',
                confirmButtonColor: '#3085d6'
            });
            inputDynamicPassword.focus();
            return;
        }

        if (konfirmasi !== expected) {
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi Teks Tidak Sesuai',
                html: `Harap ketik persis teks konfirmasi: <strong class="text-danger font-monospace">${expected}</strong>`,
                confirmButtonColor: '#3085d6'
            });
            inputKonfirmasiTeks.focus();
            return;
        }

        const thn = selectTahun.value;
        const tipeText = selectTipe.options[selectTipe.selectedIndex].text;
        const isMaster = document.getElementById('checkHapusMaster').checked;

        Swal.fire({
            title: 'Konfirmasi Pembersihan Permanen',
            html: `
                <div class="text-start small">
                    <p class="mb-2">Anda akan menghapus data berikut secara <strong>permanen</strong>:</p>
                    <ul class="mb-3">
                        <li><strong>Tahun Anggaran:</strong> ${thn}</li>
                        <li><strong>Cakupan Modul:</strong> ${tipeText}</li>
                        <li><strong>Katalog Master:</strong> ${isMaster ? '<span class="text-danger fw-bold">Ikut Dihapus</span>' : '<span class="text-success fw-bold">Tetap Utuh</span>'}</li>
                    </ul>
                    <div class="alert alert-danger p-2 mb-0 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Aksi ini tidak dapat dibatalkan! File surat dan lampiran fisik juga akan dihapus dari server.
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading saat proses eksekusi
                Swal.fire({
                    title: 'Memproses Pembersihan Data...',
                    html: 'Sedang menghapus rincian usulan, berkas fisik, dan log aktivitas...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                formPembersihan.submit();
            }
        });
    });
});
</script>
