<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            <i class="bi bi-journal-code text-primary"></i>
            Dokumentasi & Pembaruan Sistem
        </h1>
        <p class="page-subtitle text-muted mb-0">
            Pusat informasi teknis, riwayat versi pembaruan aplikasi (release notes), panduan alur kedinasan, dan status lingkungan server SIPA.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6 rounded-pill d-flex align-items-center gap-1.5">
            <i class="bi bi-check-circle-fill"></i>
            <span>v<?= e($appVersion) ?> (Aktif)</span>
        </span>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Halaman
        </button>
    </div>
</div>

<!-- Header Statistik Mini -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-primary">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Versi Terpasang</small>
                <h4 class="mb-0 mt-1 fw-bold text-primary">v<?= e($appVersion) ?></h4>
                <small class="text-muted" style="font-size:11px;">Edisi Pemkab Tapin</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-info">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Riwayat Rilis</small>
                <h4 class="mb-0 mt-1 fw-bold text-info"><?= count($changelogList) ?> Versi</h4>
                <small class="text-muted" style="font-size:11px;">Terdokumentasi Lengkap</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-success">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Status Lingkungan</small>
                <h4 class="mb-0 mt-1 fw-bold text-success text-uppercase"><?= e($systemInfo['environment']) ?></h4>
                <small class="text-muted" style="font-size:11px;">PHP <?= PHP_VERSION ?></small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-warning">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Tahun Anggaran Aktif</small>
                <h4 class="mb-0 mt-1 fw-bold text-warning">TA <?= (int) $systemInfo['fiscal_year'] ?></h4>
                <small class="text-muted" style="font-size:11px;">Konteks Global Sistem</small>
            </div>
        </div>
    </div>
</div>

<!-- Navigasi Tab Dokumentasi -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom-0 pt-3 px-3 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="docTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'changelog') ? 'active fw-bold' : '' ?>" 
                        id="tab-changelog-btn" data-bs-toggle="tab" data-bs-target="#tab-changelog" type="button" role="tab">
                    <i class="bi bi-rocket-takeoff-fill text-primary me-1.5"></i>
                    Riwayat Pembaruan (Changelog)
                    <span class="badge bg-primary ms-1.5"><?= count($changelogList) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'panduan') ? 'active fw-bold' : '' ?>" 
                        id="tab-panduan-btn" data-bs-toggle="tab" data-bs-target="#tab-panduan" type="button" role="tab">
                    <i class="bi bi-book-half text-info me-1.5"></i>
                    Panduan Fitur & Alur Modul
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'sistem') ? 'active fw-bold' : '' ?>" 
                        id="tab-sistem-btn" data-bs-toggle="tab" data-bs-target="#tab-sistem" type="button" role="tab">
                    <i class="bi bi-cpu-fill text-success me-1.5"></i>
                    Diagnostik & Server Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'readme') ? 'active fw-bold' : '' ?>" 
                        id="tab-readme-btn" data-bs-toggle="tab" data-bs-target="#tab-readme" type="button" role="tab">
                    <i class="bi bi-file-earmark-text-fill text-secondary me-1.5"></i>
                    Manual Teknis (README)
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="docTabsContent">
            
            <!-- ============================================================= -->
            <!-- TAB 1: RIWAYAT PEMBARUAN (CHANGELOG)                          -->
            <!-- ============================================================= -->
            <div class="tab-pane fade <?= ($activeTab === 'changelog') ? 'show active' : '' ?>" id="tab-changelog" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Catatan Rilis & Log Pembaruan Aplikasi</h5>
                        <p class="text-muted small mb-0">Daftar penambahan fitur, peningkatan keamanan, dan perbaikan sistem secara kronologis.</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group input-group-sm" style="max-width: 250px;">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchChangelog" placeholder="Cari fitur, versi, keyword...">
                        </div>
                    </div>
                </div>

                <?php if (empty($changelogList)): ?>
                <div class="alert alert-warning">File CHANGELOG.md belum ditemukan di repositori aplikasi.</div>
                <?php else: ?>
                <div class="timeline-container">
                    <?php foreach ($changelogList as $rel): ?>
                    <div class="card border-0 shadow-sm mb-4 changelog-card <?= $rel['is_latest'] ? 'border-start border-4 border-primary' : 'bg-white' ?>" data-keywords="<?= strtolower(e($rel['version'] . ' ' . $rel['date'] . ' ' . strip_tags($rel['raw_body']))) ?>">
                        <div class="card-header <?= $rel['is_latest'] ? 'bg-primary-subtle text-primary' : 'bg-light text-dark' ?> py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge <?= $rel['is_latest'] ? 'bg-primary' : 'bg-dark' ?> fs-6 font-monospace py-1.5 px-2.5">
                                    v<?= e($rel['version']) ?>
                                </span>
                                <?php if ($rel['is_latest']): ?>
                                <span class="badge bg-success py-1 px-2"><i class="bi bi-check-circle me-1"></i>VERSI TERBARU SAAT INI</span>
                                <?php endif; ?>
                            </div>
                            <div class="small fw-semibold text-muted d-flex align-items-center gap-1">
                                <i class="bi bi-calendar-event"></i>
                                <span>Rilis: <?= date('d F Y', strtotime($rel['date'])) ?></span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <?= $rel['html_body'] ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: PANDUAN FITUR & ALUR MODUL                             -->
            <!-- ============================================================= -->
            <div class="tab-pane fade <?= ($activeTab === 'panduan') ? 'show active' : '' ?>" id="tab-panduan" role="tabpanel">
                <div class="mb-4">
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-diagram-3-fill text-info me-2"></i>Panduan Alur Kerja & Modul Kedinasan</h5>
                    <p class="text-muted small mb-0">Penjelasan alur proses bisnis antar role (Operator SKPD, Verifikator BPKAD, Penetap, Pimpinan, dan Administrator).</p>
                </div>

                <div class="row g-4">
                    <!-- Modul 1: SSH -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm border-top border-4 border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="p-2 rounded bg-primary-subtle text-primary fs-4"><i class="bi bi-box-seam-fill"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Modul Standar Satuan Harga (SSH)</h6>
                                        <small class="text-muted">Barang fisik, ATK, material bangunan, kendaraan, inventaris</small>
                                    </div>
                                </div>
                                <h6 class="small fw-bold text-uppercase text-muted">Alur 6 Tahapan Status:</h6>
                                <div class="d-flex flex-wrap gap-1.5 mb-3 align-items-center">
                                    <span class="badge bg-secondary">Draft</span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="badge bg-info">Diajukan</span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="badge bg-warning text-dark">Direvisi</span>
                                    <span class="badge bg-danger">Ditolak</span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="badge bg-primary">Diverifikasi</span>
                                    <i class="bi bi-arrow-right text-muted small"></i>
                                    <span class="badge bg-success">Ditetapkan</span>
                                </div>
                                <ul class="small text-secondary ps-3 mb-0">
                                    <li class="mb-1"><strong>Draft:</strong> SKPD menyusun spesifikasi, rekening belanja SIPD RI, dan mengunggah mandatori bukti survey harga.</li>
                                    <li class="mb-1"><strong>Diajukan:</strong> Berkas dikirim ke BPKAD, data otomatis terkunci (RLS Guard).</li>
                                    <li class="mb-1"><strong>Direvisi / Ditolak:</strong> Verifikator mengembalikan berkas perbaikan atau menolak usulan dengan wajib mengisi catatan alasan.</li>
                                    <li class="mb-1"><strong>Diverifikasi:</strong> Usulan disetujui tim telaah BPKAD dan siap disahkan menjadi SK Bupati.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Modul 2: SBU -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm border-top border-4 border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="p-2 rounded bg-info-subtle text-info fs-4"><i class="bi bi-receipt-cutoff"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Modul Standar Biaya Umum (SBU)</h6>
                                        <small class="text-muted">Honorarium, narasumber, tenaga ahli, uang harian perjadin, sewa gedung</small>
                                    </div>
                                </div>
                                <h6 class="small fw-bold text-uppercase text-muted">Karakteristik & Satuan:</h6>
                                <p class="small text-secondary mb-2">
                                    SBU mencakup batas tertinggi tarif operasional berbasis kegiatan dan durasi waktu kerja:
                                </p>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="badge bg-light text-dark border">OB (Orang/Bulan)</span>
                                    <span class="badge bg-light text-dark border">OH (Orang/Hari)</span>
                                    <span class="badge bg-light text-dark border">OJ (Orang/Jam)</span>
                                    <span class="badge bg-light text-dark border">OK (Orang/Kegiatan)</span>
                                    <span class="badge bg-light text-dark border">Paket</span>
                                </div>
                                <ul class="small text-secondary ps-3 mb-0">
                                    <li class="mb-1">Mendukung acuan tarif Perpres / PMK / Perbup Standar Biaya Daerah.</li>
                                    <li class="mb-1">Memiliki formulir usulan, antrean verifikasi, dan penetapan terpisah dari fisik.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Modul 3: RKBMD -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm border-top border-4 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="p-2 rounded bg-warning-subtle text-warning fs-4"><i class="bi bi-file-earmark-spreadsheet-fill"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Modul Perencanaan RKBMD (5 Instrumen)</h6>
                                        <small class="text-muted">Berdasarkan Permendagri No. 19 Tahun 2016 tentang Pedoman Pengelolaan BMD</small>
                                    </div>
                                </div>
                                <ul class="small text-secondary ps-3 mb-0">
                                    <li class="mb-1"><strong>1. Pengadaan:</strong> Usulan belanja aset baru berdasarkan analisis data BMD eksisting.</li>
                                    <li class="mb-1"><strong>2. Pemeliharaan:</strong> Rencana perawatan berkala kendaraan dinas, gedung, mesin.</li>
                                    <li class="mb-1"><strong>3. Pemanfaatan:</strong> Rencana sewa, pinjam pakai, KSP, BGS/BSG.</li>
                                    <li class="mb-1"><strong>4. Pemindahtanganan:</strong> Rencana penjualan, hibah, tukar-menukar aset daerah.</li>
                                    <li class="mb-1"><strong>5. Penghapusan:</strong> Rencana penghapusan barang rusak berat / kedaluwarsa.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Modul 4: Integrasi & Jadwal -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm border-top border-4 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="p-2 rounded bg-success-subtle text-success fs-4"><i class="bi bi-whatsapp"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Fitur Notifikasi WhatsApp & Penjadwalan</h6>
                                        <small class="text-muted">Komunikasi otomatis instan dan pengamanan jadwal periode</small>
                                    </div>
                                </div>
                                <ul class="small text-secondary ps-3 mb-0">
                                    <li class="mb-1"><strong>Notifikasi WA 1-Klik:</strong> Draf notifikasi resmi ber-kop Pemerintah Kab. Tapin dapat dikirim langsung ke nomor WhatsApp operator SKPD.</li>
                                    <li class="mb-1"><strong>Template Lengkap:</strong> Mendukung template revisi, disetujui, ditolak, dan penetapan SK.</li>
                                    <li class="mb-1"><strong>Jadwal Pengusulan BPKAD:</strong> Form tambah usulan otomatis terkunci jika jadwal belum dibuka atau telah berakhir.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 3: DIAGNOSTIK & SERVER INFO                               -->
            <!-- ============================================================= -->
            <div class="tab-pane fade <?= ($activeTab === 'sistem') ? 'show active' : '' ?>" id="tab-sistem" role="tabpanel">
                <div class="mb-4">
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-hdd-network-fill text-success me-2"></i>Informasi Lingkungan Sistem & Konfigurasi Server</h5>
                    <p class="text-muted small mb-0">Rincian status runtime PHP, database MySQL, batas alokasi memori, dan izin berkas.</p>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Kolom 1: Konfigurasi Aplikasi -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-2.5">
                                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-app-indicator me-1.5 text-primary"></i>Aplikasi & Framework</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0 align-middle">
                                    <tbody>
                                        <tr><th width="40%" class="text-muted ps-3">Nama Aplikasi</th><td class="fw-semibold text-dark"><?= e($systemInfo['app_name']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Versi Rilis</th><td><span class="badge bg-primary font-monospace">v<?= e($systemInfo['app_version']) ?></span></td></tr>
                                        <tr><th class="text-muted ps-3">Instansi Pengelola</th><td class="text-dark"><?= e($systemInfo['app_unit']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Pemerintah Daerah</th><td class="text-dark"><?= e($systemInfo['app_owner']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Framework Core</th><td class="font-monospace text-dark">CodeIgniter v<?= e($systemInfo['ci_version']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Environment</th><td><span class="badge bg-<?= ($systemInfo['environment'] === 'production') ? 'success' : 'warning text-dark' ?> text-uppercase"><?= e($systemInfo['environment']) ?></span></td></tr>
                                        <tr><th class="text-muted ps-3">URL Dasar</th><td class="font-monospace small text-primary"><?= e($systemInfo['base_url']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">TA Aktif Global</th><td><span class="badge bg-warning text-dark font-monospace">TA <?= (int) $systemInfo['fiscal_year'] ?></span></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2: Lingkungan Server PHP -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-2.5">
                                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-server me-1.5 text-info"></i>Server & PHP Runtime</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0 align-middle">
                                    <tbody>
                                        <tr><th width="40%" class="text-muted ps-3">Versi PHP</th><td class="fw-bold text-dark font-monospace">PHP <?= e($systemInfo['php_version']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">PHP Interface (SAPI)</th><td class="text-dark font-monospace"><?= e($systemInfo['php_sapi']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Sistem Operasi Server</th><td class="text-dark"><?= e($systemInfo['server_os']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Software Web Server</th><td class="text-dark small text-truncate" style="max-width:250px;"><?= e($systemInfo['server_software']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Memory Limit</th><td class="fw-semibold text-dark font-monospace"><?= e($systemInfo['memory_limit']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Max Execution Time</th><td class="text-dark font-monospace"><?= e($systemInfo['max_execution_time']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Upload Max Size</th><td class="text-dark font-monospace"><?= e($systemInfo['upload_max_filesize']) ?> (POST: <?= e($systemInfo['post_max_size']) ?>)</td></tr>
                                        <tr>
                                            <th class="text-muted ps-3">Folder Uploads (/uploads)</th>
                                            <td>
                                                <?php if ($systemInfo['uploads_writable']): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Writable (Bisa Upload)</span>
                                                <?php else: ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Not Writable (Izin Akses Ditolak)</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 3: Basis Data MySQL -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-2.5">
                                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-database-check me-1.5 text-success"></i>Basis Data MySQL</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0 align-middle">
                                    <tbody>
                                        <tr><th width="40%" class="text-muted ps-3">Database Engine</th><td class="text-dark font-monospace"><?= e($systemInfo['db_driver']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Versi MySQL</th><td class="fw-semibold text-dark font-monospace"><?= e($systemInfo['db_version']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Nama Database</th><td class="font-monospace text-primary"><?= e($systemInfo['db_name']) ?></td></tr>
                                        <tr><th class="text-muted ps-3">Charset / Collation</th><td class="font-monospace text-muted small">utf8mb4 / utf8mb4_unicode_ci</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 4: Statistik Data Sistem -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light py-2.5">
                                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-fill me-1.5 text-warning"></i>Statistik Data Master & Usulan</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0 align-middle">
                                    <tbody>
                                        <tr><th width="40%" class="text-muted ps-3">Total Akun Pengguna</th><td class="fw-bold text-dark"><?= number_format($systemInfo['counts']['users']) ?> Akun</td></tr>
                                        <tr><th class="text-muted ps-3">Total Unit Kerja SKPD</th><td class="fw-bold text-dark"><?= number_format($systemInfo['counts']['skpd']) ?> SKPD</td></tr>
                                        <tr><th class="text-muted ps-3">Usulan Standar Harga (SSH/SBU)</th><td class="fw-bold text-primary"><?= number_format($systemInfo['counts']['standar_harga']) ?> Usulan</td></tr>
                                        <tr><th class="text-muted ps-3">Usulan Perencanaan RKBMD</th><td class="fw-bold text-info"><?= number_format($systemInfo['counts']['rkbmd']) ?> Usulan</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 4: MANUAL TEKNIS (README)                                 -->
            <!-- ============================================================= -->
            <div class="tab-pane fade <?= ($activeTab === 'readme') ? 'show active' : '' ?>" id="tab-readme" role="tabpanel">
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-file-earmark-text text-secondary me-2"></i>Manual Teknis & Arsitektur Sistem (README.md)</h5>
                        <p class="text-muted small mb-0">Dokumentasi komprehensif struktur repositori, matriks RBAC/RLS, panduan deployment VPS, dan endpoint API.</p>
                    </div>
                    <a href="https://github.com/rullyperdhana/sipa" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-github me-1"></i> Buka di GitHub
                    </a>
                </div>

                <div class="p-4 bg-light rounded border markdown-body">
                    <?= $readmeHtml ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fitur Live Search Changelog
    const searchInput = document.getElementById('searchChangelog');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.changelog-card');
            
            cards.forEach(function(card) {
                const keywords = card.getAttribute('data-keywords') || '';
                if (keywords.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>
