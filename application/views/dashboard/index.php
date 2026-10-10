<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
/* Dashboard Styles */
.dash-card {
    border-radius: 14px;
    border: none;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background: #ffffff;
}
.dash-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.kpi-gradient-primary {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: #ffffff;
}
.kpi-gradient-success {
    background: linear-gradient(135deg, #065f46 0%, #10b981 100%);
    color: #ffffff;
}
.kpi-gradient-warning {
    background: linear-gradient(135deg, #92400e 0%, #f59e0b 100%);
    color: #ffffff;
}
.kpi-gradient-info {
    background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%);
    color: #ffffff;
}
.kpi-icon-bubble {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(4px);
}
.quick-btn {
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.2s ease;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
.quick-btn:hover {
    transform: translateY(-1px);
}
.badge-soft {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    font-weight: 600;
}
.module-header-pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.chart-container-box {
    position: relative;
    height: 280px;
    width: 100%;
}
</style>

<!-- Header & Filter Bar -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Terpadu SIPA
        </h1>
        <p class="page-subtitle text-muted mb-0">
            Selamat datang, <strong><?= e($this->currentUser->nama_lengkap) ?></strong> (<?= e(ucfirst($this->currentUser->role)) ?>). 
            Ringkasan Pengelolaan Aset (RKBMD) dan Standar Harga (SSH & SBU) Tahun Anggaran <strong><?= (int)$tahun ?></strong>.
        </p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
        <div class="input-group input-group-sm" style="width: auto;">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-calendar3"></i></span>
            <select name="tahun" class="form-select form-select-sm border-start-0" onchange="this.form.submit()">
                <?php for ($y = (int)date('Y')+1; $y >= (int)date('Y')-3; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>>TA <?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <?php if ($this->currentUser->role !== 'skpd'): ?>
        <div class="input-group input-group-sm" style="width: auto;">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-building"></i></span>
            <select name="skpd_id" class="form-select form-select-sm border-start-0" onchange="this.form.submit()" style="max-width: 240px;">
                <option value="">Semua SKPD (Kab. Tapin)</option>
                <?php foreach ($skpdList as $s): ?>
                    <option value="<?= $s->id ?>" <?= $s->id == $skpdId ? 'selected' : '' ?>><?= e($s->nama_skpd) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <a href="<?= site_url('dashboard') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
            <i class="bi bi-arrow-clockwise"></i>
        </a>
    </form>
</div>

<!-- Banner Status Jadwal Pengusulan -->
<div class="row g-3 mb-4">
    <!-- Jadwal SSH -->
    <div class="col-md-6">
        <div class="card dash-card border-start border-4 <?= $isJadwalBukaSsh ? 'border-success' : 'border-warning' ?> py-1">
            <div class="card-body py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center <?= $isJadwalBukaSsh ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>" style="width:38px;height:38px;">
                        <i class="bi <?= $isJadwalBukaSsh ? 'bi-box-seam-fill' : 'bi-lock-fill' ?> fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">Jadwal Pengusulan SSH <?= $tahun ?></div>
                        <small class="text-muted">
                            <?= $isJadwalBukaSsh ? e($jadwalSsh->nama_jadwal) . ' (s.d ' . date('d M Y', strtotime($jadwalSsh->tanggal_selesai)) . ')' : 'Jadwal belum dibuka / telah berakhir oleh BPKAD.' ?>
                        </small>
                    </div>
                </div>
                <div>
                    <?php if ($isJadwalBukaSsh): ?>
                        <span class="badge bg-success"><i class="bi bi-unlock-fill me-1"></i>DIBUKA</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>DITUTUP</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal SBU -->
    <div class="col-md-6">
        <div class="card dash-card border-start border-4 <?= $isJadwalBukaSbu ? 'border-info' : 'border-warning' ?> py-1">
            <div class="card-body py-2.5 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center <?= $isJadwalBukaSbu ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' ?>" style="width:38px;height:38px;">
                        <i class="bi <?= $isJadwalBukaSbu ? 'bi-receipt-cutoff' : 'bi-lock-fill' ?> fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">Jadwal Pengusulan SBU <?= $tahun ?></div>
                        <small class="text-muted">
                            <?= $isJadwalBukaSbu ? e($jadwalSbu->nama_jadwal) . ' (s.d ' . date('d M Y', strtotime($jadwalSbu->tanggal_selesai)) . ')' : 'Jadwal belum dibuka / telah berakhir oleh BPKAD.' ?>
                        </small>
                    </div>
                </div>
                <div>
                    <?php if ($isJadwalBukaSbu): ?>
                        <span class="badge bg-info text-white"><i class="bi bi-unlock-fill me-1"></i>DIBUKA</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>DITUTUP</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts Bar -->
<div class="card dash-card mb-4 bg-light-subtle border border-light-subtle">
    <div class="card-body py-2.5 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2 text-muted small fw-semibold">
            <i class="bi bi-lightning-charge-fill text-warning fs-6"></i>
            <span>Akses Cepat (Quick Actions):</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (in_array($this->currentUser->role, ['operator_skpd', 'skpd', 'admin'])): ?>
                <?php if (can_access('rkbmd_pengadaan')): ?>
                <a href="<?= site_url('rkbmd/pengadaan/create') ?>" class="btn btn-sm btn-outline-primary quick-btn">
                    <i class="bi bi-plus-circle me-1"></i> Usul RKBMD
                </a>
                <?php endif; ?>
                <?php if (can_access('ssh')): ?>
                <a href="<?= site_url('ssh/tambah') ?>" class="btn btn-sm btn-outline-success quick-btn <?= (!$isJadwalBukaSsh && $this->currentUser->role !== 'admin') ? 'disabled' : '' ?>">
                    <i class="bi bi-plus-circle me-1"></i> Usul SSH Baru
                </a>
                <?php endif; ?>
                <?php if (can_access('sbu')): ?>
                <a href="<?= site_url('sbu/tambah') ?>" class="btn btn-sm btn-outline-info quick-btn <?= (!$isJadwalBukaSbu && $this->currentUser->role !== 'admin') ? 'disabled' : '' ?>">
                    <i class="bi bi-plus-circle me-1"></i> Usul SBU Baru
                </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array($this->currentUser->role, ['verifikator', 'admin'])): ?>
                <?php if (can_access('verifikasi_rkbmd')): ?>
                <a href="<?= site_url('verifikasi') ?>" class="btn btn-sm btn-outline-warning quick-btn">
                    <i class="bi bi-check2-square me-1"></i> Verifikasi RKBMD
                </a>
                <?php endif; ?>
                <?php if (can_access('verifikasi_standar')): ?>
                <a href="<?= site_url('ssh/verifikasi') ?>" class="btn btn-sm btn-outline-primary quick-btn">
                    <i class="bi bi-patch-check me-1"></i> Verifikasi SSH
                </a>
                <a href="<?= site_url('sbu/verifikasi') ?>" class="btn btn-sm btn-outline-info quick-btn">
                    <i class="bi bi-check2-circle me-1"></i> Verifikasi SBU
                </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (can_access('laporan')): ?>
            <a href="<?= site_url('laporan') ?>" class="btn btn-sm btn-primary quick-btn text-white shadow-sm">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Pusat Laporan & Rekap Eksekutif
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 4 Kartu KPI Eksekutif Modern -->
<div class="row g-3 mb-4">
    <!-- KPI 1: Total Usulan Masuk -->
    <div class="col-lg-3 col-md-6">
        <div class="card dash-card kpi-gradient-primary h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-white-50 text-uppercase fw-semibold small d-block">Total Seluruh Usulan</span>
                    <h2 class="display-6 fw-bold mb-1 mt-1 text-white"><?= number_format($executiveKpi['total_usulan']) ?></h2>
                    <small class="text-white-50">
                        RKBMD: <strong><?= number_format($executiveKpi['rkbmd_total']) ?></strong> &bull; Standar: <strong><?= number_format($executiveKpi['ssh_sbu_total']) ?></strong>
                    </small>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-folder2-open text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2: Total Nilai Pagu -->
    <div class="col-lg-3 col-md-6">
        <div class="card dash-card kpi-gradient-info h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-white-50 text-uppercase fw-semibold small d-block">Total Pagu Usulan Terpadu</span>
                    <h3 class="fw-bold mb-1 mt-1 text-white" style="font-size: 1.45rem;"><?= rupiah($executiveKpi['total_nilai']) ?></h3>
                    <small class="text-white-50">
                        Disetujui: <strong><?= rupiah($executiveKpi['nilai_disetujui']) ?></strong>
                    </small>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-cash-stack text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3: Antrean Verifikasi -->
    <div class="col-lg-3 col-md-6">
        <div class="card dash-card kpi-gradient-warning h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-white-50 text-uppercase fw-semibold small d-block">Menunggu Verifikasi BPKAD</span>
                    <h2 class="display-6 fw-bold mb-1 mt-1 text-white"><?= number_format($totalAntreanVerif) ?></h2>
                    <small class="text-white-50">
                        RKBMD: <strong><?= number_format(array_sum(array_column($rekap,'diajukan'))) ?></strong> &bull; SSH/SBU: <strong><?= number_format($rekapStandar['SSH']['diajukan'] + $rekapStandar['SBU']['diajukan']) ?></strong>
                    </small>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-hourglass-split text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 4: Kepatuhan SKPD -->
    <div class="col-lg-3 col-md-6">
        <div class="card dash-card kpi-gradient-success h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-white-50 text-uppercase fw-semibold small d-block">Kepatuhan Partisipasi SKPD</span>
                    <h2 class="display-6 fw-bold mb-1 mt-1 text-white"><?= $executiveKpi['kepatuhan_rate'] ?>%</h2>
                    <small class="text-white-50">
                        <strong><?= $executiveKpi['skpd_aktif'] ?></strong> dari <strong><?= $executiveKpi['total_skpd'] ?> SKPD</strong> telah input
                    </small>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-shield-check text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bagian Visualisasi Grafik Interaktif Chart.js -->
<div class="row g-3 mb-4">
    <!-- Grafik 1: Alokasi Anggaran Terpadu -->
    <div class="col-lg-8">
        <div class="card dash-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Distribusi Alokasi Anggaran per Modul (TA <?= $tahun ?>)</h6>
                    <small class="text-muted">Perbandingan nilai rupiah antara 5 instrumen RKBMD dan usulan Standar Harga (SSH & SBU).</small>
                </div>
                <span class="badge bg-light text-dark border">Chart.js Engine</span>
            </div>
            <div class="card-body">
                <div class="chart-container-box">
                    <canvas id="chartAnggaranCanvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik 2: Donut Status Usulan Terpadu -->
    <div class="col-lg-4">
        <div class="card dash-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill text-info me-2"></i>Status Persetujuan Usulan</h6>
                    <small class="text-muted">Proporsi siklus proses seluruh berkas.</small>
                </div>
                <span class="badge bg-primary-subtle text-primary fw-semibold"><?= number_format($executiveKpi['total_usulan']) ?> total</span>
            </div>
            <div class="card-body">
                <div class="chart-container-box" style="height: 280px;">
                    <canvas id="chartStatusCanvas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- RINGKASAN MODUL TERPADU: RKBMD (5 INSTRUMEN) & STANDAR HARGA (SSH & SBU) -->
<!-- ========================================================================= -->

<!-- Modul 1: Standar Satuan Harga & Biaya (SSH & SBU) -->
<?php if (can_access('ssh') || can_access('sbu')): ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <span class="module-header-pill bg-primary-subtle text-primary">
            <i class="bi bi-tags-fill"></i> Modul Standar Harga Daerah
        </span>
        <span class="text-muted small">Peraturan Bupati Tapin tentang Standar Satuan Harga & Biaya</span>
    </div>
    <a href="<?= site_url('laporan') ?>" class="btn btn-sm btn-link text-decoration-none">Lihat Rekap Standar <i class="bi bi-arrow-right"></i></a>
</div>

<div class="row g-3 mb-4">
    <!-- Card SSH -->
    <?php if (can_access('ssh')): ?>
    <div class="<?= can_access('sbu') ? 'col-md-6' : 'col-12' ?>">
        <div class="card dash-card h-100 border-top border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-3 p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-size:20px;">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Standar Satuan Harga (SSH)</h6>
                            <small class="text-muted">Barang fisik, material bangunan, inventaris, ATK</small>
                        </div>
                    </div>
                    <a href="<?= site_url('ssh/usulan') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i></a>
                </div>
                
                <div class="row g-2 text-center mb-3">
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Draft</small>
                            <span class="fw-bold fs-6"><?= (int)$rekapStandar['SSH']['draft'] ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Diajukan</small>
                            <span class="fw-bold fs-6 text-info"><?= (int)$rekapStandar['SSH']['diajukan'] ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Diverifikasi</small>
                            <span class="fw-bold fs-6 text-primary"><?= (int)$rekapStandar['SSH']['diverifikasi'] ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Ditetapkan</small>
                            <span class="fw-bold fs-6 text-success"><?= (int)$rekapStandar['SSH']['ditetapkan'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <div>
                        <span class="text-muted small">Total Item Usulan:</span>
                        <strong class="ms-1"><?= number_format($rekapStandar['SSH']['total']) ?> item</strong>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small">Total Pagu:</span>
                        <strong class="text-primary ms-1"><?= rupiah($rekapStandar['SSH']['nilai']) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Card SBU -->
    <?php if (can_access('sbu')): ?>
    <div class="<?= can_access('ssh') ? 'col-md-6' : 'col-12' ?>">
        <div class="card dash-card h-100 border-top border-4 border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-3 p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:42px;height:42px;font-size:20px;">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Standar Biaya Umum (SBU)</h6>
                            <small class="text-muted">Honorarium narasumber, sewa gedung, jasa tenaga ahli</small>
                        </div>
                    </div>
                    <a href="<?= site_url('sbu/usulan') ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-arrow-right"></i></a>
                </div>
                
                <div class="row g-2 text-center mb-3">
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Draft</small>
                            <span class="fw-bold fs-6"><?= (int)$rekapStandar['SBU']['draft'] ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Diajukan</small>
                            <span class="fw-bold fs-6 text-info"><?= (int)$rekapStandar['SBU']['diajukan'] ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Diverifikasi</small>
                            <span class="fw-bold fs-6 text-primary"><?= (int)$rekapStandar['SBU']['diverifikasi'] ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 rounded bg-light">
                            <small class="text-muted d-block" style="font-size: 11px;">Ditetapkan</small>
                            <span class="fw-bold fs-6 text-success"><?= (int)$rekapStandar['SBU']['ditetapkan'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <div>
                        <span class="text-muted small">Total Item Usulan:</span>
                        <strong class="ms-1"><?= number_format($rekapStandar['SBU']['total']) ?> item</strong>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small">Total Pagu:</span>
                        <strong class="text-info ms-1"><?= rupiah($rekapStandar['SBU']['nilai']) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modul 2: Rencana Kebutuhan Barang Milik Daerah (RKBMD 5 Instrumen) -->
<?php 
$hasRkbmdDash = can_access('rkbmd_pengadaan') || can_access('rkbmd_pemeliharaan') || can_access('rkbmd_pemanfaatan') || can_access('rkbmd_pemindahtanganan') || can_access('rkbmd_penghapusan');
if ($hasRkbmdDash): 
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <span class="module-header-pill bg-success-subtle text-success">
            <i class="bi bi-diagram-3-fill"></i> Modul Perencanaan Aset (RKBMD)
        </span>
        <span class="text-muted small">5 Instrumen Siklus Pengelolaan BMD Sesuai Permendagri No. 19/2016</span>
    </div>
    <span class="badge bg-secondary-subtle text-secondary"><?= number_format(array_sum(array_column($rekap,'total'))) ?> total usulan</span>
</div>

<div class="row g-3 mb-4">
<?php
$jenisConfig = [
    'pengadaan'        => ['icon' => 'cart-plus-fill',   'color' => '#1e40af', 'badge' => 'primary', 'desc' => 'Rencana perolehan barang modal & operasional baru'],
    'pemeliharaan'     => ['icon' => 'tools',             'color' => '#0891b2', 'badge' => 'info',    'desc' => 'Perawatan rutin & servis berkala aset BMD'],
    'pemanfaatan'      => ['icon' => 'share-fill',        'color' => '#7c3aed', 'badge' => 'purple',  'desc' => 'Sewa, pinjam pakai, KSP dengan pihak ketiga'],
    'pemindahtanganan' => ['icon' => 'arrow-left-right',  'color' => '#ea580c', 'badge' => 'warning', 'desc' => 'Penjualan, hibah, tukar menukar aset daerah'],
    'penghapusan'      => ['icon' => 'trash3-fill',       'color' => '#dc2626', 'badge' => 'danger',  'desc' => 'Pemusnahan & pembebasan aset rusak berat/hilang']
];

foreach ($rekap as $jKey => $jData):
    if (!can_access('rkbmd_' . $jKey)) continue;
    $cfg = $jenisConfig[$jKey];
?>
<div class="col-lg-4 col-md-6">
    <div class="card dash-card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:<?= $cfg['color'] ?>18;color:<?= $cfg['color'] ?>;width:40px;height:40px;font-size:18px;">
                        <i class="bi bi-<?= $cfg['icon'] ?>"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= label_jenis($jKey) ?></h6>
                        <small class="text-muted" style="font-size:11px;"><?= $cfg['desc'] ?></small>
                    </div>
                </div>
                <a href="<?= site_url('rkbmd/'.$jKey) ?>" class="btn btn-sm btn-outline-secondary" title="Buka Modul"><i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-1 text-center my-2">
                <div class="col-3"><div class="small text-muted" style="font-size:11px;">Draft</div><div class="fw-bold"><?= $jData['draft'] ?></div></div>
                <div class="col-3"><div class="small text-muted" style="font-size:11px;">Ajukan</div><div class="fw-bold text-info"><?= $jData['diajukan'] ?></div></div>
                <div class="col-3"><div class="small text-muted" style="font-size:11px;">Setuju</div><div class="fw-bold text-success"><?= $jData['disetujui'] ?></div></div>
                <div class="col-3"><div class="small text-muted" style="font-size:11px;">Tolak</div><div class="fw-bold text-danger"><?= $jData['ditolak'] ?></div></div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <small class="text-muted">Total Usulan: <strong><?= $jData['total'] ?></strong></small>
                <strong class="text-dark"><?= rupiah($jData['nilai']) ?></strong>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Kartu Ringkasan Rekap RKBMD -->
<div class="col-lg-4 col-md-6">
    <div class="card dash-card h-100 bg-primary-subtle border border-primary-subtle d-flex flex-column justify-content-center text-center p-3">
        <div class="mb-2">
            <i class="bi bi-layers-fill fs-2 text-primary"></i>
        </div>
        <h6 class="fw-bold text-primary mb-1">Total Pagu RKBMD</h6>
        <h4 class="fw-bold text-dark mb-2"><?= rupiah($totalNilai) ?></h4>
        <p class="text-muted small mb-3">Instrumen perencanaan BMD Kabupaten Tapin TA <?= $tahun ?>.</p>
        <div>
            <?php if (can_access('laporan')): ?>
            <a href="<?= site_url('laporan') ?>" class="btn btn-sm btn-primary shadow-sm px-3">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Buka Rekapitulasi Lengkap
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MONITORING SKPD & USULAN TERBARU                                          -->
<!-- ========================================================================= -->

<div class="row g-4 mb-4">
    <!-- Tabel Monitoring SKPD -->
    <div class="col-lg-7">
        <div class="card dash-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-eye-fill text-primary me-2"></i>Monitoring Progres SKPD</h6>
                    <small class="text-muted">Status usulan RKBMD jenis <strong><?= e(label_jenis($jenis)) ?></strong>.</small>
                </div>
                <div class="d-flex gap-2">
                    <form method="GET" class="d-flex gap-1">
                        <input type="hidden" name="tahun" value="<?= $tahun ?>">
                        <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                            <?php foreach (['pengadaan'=>'Pengadaan','pemeliharaan'=>'Pemeliharaan','pemanfaatan'=>'Pemanfaatan','pemindahtanganan'=>'Pemindahtanganan','penghapusan'=>'Penghapusan'] as $k=>$v): ?>
                                <option value="<?= $k ?>" <?= $k === $jenis ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>SKPD</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Draft</th>
                            <th class="text-center">Diajukan</th>
                            <th class="text-center">Disetujui</th>
                            <th class="text-center">Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monitoring)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada data monitoring.</td></tr>
                        <?php else: 
                            $countRows = 0;
                            foreach ($monitoring as $row): 
                                if ($this->currentUser->role !== 'skpd' && $row['total'] === 0 && $countRows > 10) continue;
                                $countRows++;
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold small text-truncate" style="max-width: 200px;" title="<?= e($row['nama_skpd']) ?>">
                                    <?= e($row['nama_skpd']) ?>
                                </div>
                                <span class="badge bg-light text-muted border" style="font-size: 10px;"><?= e($row['kode_skpd'] ?: 'SKPD') ?></span>
                            </td>
                            <td class="text-center"><strong><?= number_format($row['total']) ?></strong></td>
                            <td class="text-center text-secondary"><?= number_format($row['counts']['draft']) ?></td>
                            <td class="text-center text-info"><?= number_format($row['counts']['diajukan']) ?></td>
                            <td class="text-center text-success fw-bold"><?= number_format($row['counts']['disetujui']) ?></td>
                            <td class="text-center small text-muted"><?= $row['last_update'] ? date('d/m/y', strtotime($row['last_update'])) : '-' ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-2 text-center">
                <a href="<?= site_url('laporan') ?>?tab=matriks" class="text-decoration-none small fw-semibold text-primary">
                    Lihat Matriks Lengkap 66 SKPD Kabupaten Tapin <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Usulan Terbaru Terpadu -->
    <div class="col-lg-5">
        <div class="card dash-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-info me-2"></i>Aktivitas Usulan Terbaru</h6>
                    <small class="text-muted">Gabungan usulan RKBMD & Standar Harga terkini.</small>
                </div>
            </div>
            
            <ul class="nav nav-tabs px-3 border-bottom-0" id="recentTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active small py-1.5 px-3" id="rkbmd-tab" data-bs-toggle="tab" data-bs-target="#recent-rkbmd" type="button">RKBMD</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link small py-1.5 px-3" id="standar-tab" data-bs-toggle="tab" data-bs-target="#recent-standar" type="button">SSH & SBU</button>
                </li>
            </ul>

            <div class="tab-content border-top">
                <!-- Tab RKBMD -->
                <div class="tab-pane fade show active p-0" id="recent-rkbmd">
                    <div class="list-group list-group-flush">
                        <?php if (empty($usulanTerbaru)): ?>
                            <div class="text-center py-4 text-muted small">Belum ada aktivitas usulan RKBMD.</div>
                        <?php else: foreach ($usulanTerbaru as $u): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2.5 px-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary-subtle text-primary border" style="font-size: 10px;"><?= label_jenis($u->jenis_usulan) ?></span>
                                        <strong class="small"><?= e($u->nomor_usulan) ?></strong>
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;">
                                        <?= e($u->nama_skpd ?? '') ?> &bull; <?= rupiah($u->total_nilai) ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="mb-1"><?= badge_status($u->status) ?></div>
                                    <a href="<?= site_url('rkbmd/'.$u->jenis_usulan.'/detail/'.$u->id) ?>" class="btn btn-sm btn-outline-primary py-0 px-1.5" style="font-size: 11px;">
                                        Lihat
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Tab SSH & SBU -->
                <div class="tab-pane fade p-0" id="recent-standar">
                    <div class="list-group list-group-flush">
                        <?php if (empty($usulanStandarTerbaru)): ?>
                            <div class="text-center py-4 text-muted small">Belum ada usulan Standar Harga terbaru.</div>
                        <?php else: foreach ($usulanStandarTerbaru as $s): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2.5 px-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge <?= $s->tipe === 'SSH' ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info' ?> border" style="font-size: 10px;"><?= e($s->tipe) ?></span>
                                        <strong class="small"><?= e($s->kode_usulan) ?></strong>
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 220px;" title="<?= e($s->uraian) ?>">
                                        <?= e($s->uraian) ?> (<?= rupiah($s->harga_usulan) ?>)
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="mb-1">
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size:10px;"><?= e($s->status_proses) ?></span>
                                    </div>
                                    <a href="<?= site_url(strtolower($s->tipe).'/detail/'.$s->id) ?>" class="btn btn-sm btn-outline-info py-0 px-1.5" style="font-size: 11px;">
                                        Lihat
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-2 text-center">
                <a href="<?= site_url('laporan') ?>" class="text-decoration-none small fw-semibold text-primary">
                    Buka Pusat Laporan Terpadu <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Inisialisasi Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Grafik Alokasi Anggaran (Bar Chart)
    const ctxAnggaran = document.getElementById('chartAnggaranCanvas');
    if (ctxAnggaran) {
        const dataAnggaran = <?= json_encode($chartAnggaran['data']) ?>;
        const labelsAnggaran = <?= json_encode($chartAnggaran['labels']) ?>;

        new Chart(ctxAnggaran, {
            type: 'bar',
            data: {
                labels: labelsAnggaran,
                datasets: [{
                    label: 'Alokasi Pagu (Rp)',
                    data: dataAnggaran,
                    backgroundColor: [
                        'rgba(30, 64, 175, 0.8)',
                        'rgba(8, 145, 178, 0.8)',
                        'rgba(124, 58, 237, 0.8)',
                        'rgba(234, 88, 12, 0.8)',
                        'rgba(220, 38, 38, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(6, 182, 212, 0.8)'
                    ],
                    borderColor: [
                        '#1e40af',
                        '#0891b2',
                        '#7c3aed',
                        '#ea580c',
                        '#dc2626',
                        '#10b981',
                        '#06b6d4'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.parsed.y || 0;
                                return ' Alokasi: Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1e9) return (value / 1e9).toFixed(1) + ' M';
                                if (value >= 1e6) return (value / 1e6).toFixed(0) + ' Jt';
                                return value;
                            }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Grafik Proporsi Status (Doughnut Chart)
    const ctxStatus = document.getElementById('chartStatusCanvas');
    if (ctxStatus) {
        const dataStatus = <?= json_encode($chartStatus['data']) ?>;
        const labelsStatus = <?= json_encode($chartStatus['labels']) ?>;

        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: labelsStatus,
                datasets: [{
                    data: dataStatus,
                    backgroundColor: [
                        '#94a3b8', // Draft
                        '#f59e0b', // Diajukan / Verifikasi
                        '#10b981', // Disetujui / Ditetapkan
                        '#ef4444'  // Ditolak / Revisi
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                return ` ${label}: ${value} usulan (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }
});
</script>
