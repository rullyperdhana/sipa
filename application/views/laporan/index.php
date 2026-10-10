<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
    /* Styling Kartu KPI Eksekutif Modern */
    .kpi-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.06);
        background: #ffffff;
        box-shadow: 0 4px 14px rgba(0,0,0,0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
        position: relative;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }
    .kpi-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .kpi-blue   { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0284c7; }
    .kpi-indigo { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #4338ca; }
    .kpi-green  { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #15803d; }
    .kpi-cyan   { background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%); color: #0f766e; }

    /* Pill Tabs */
    .nav-pills .nav-link {
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 600;
        color: #4b5563;
        background: #f3f4f6;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link.active {
        background: #1e6091 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(30, 96, 145, 0.25);
    }

    /* Kartu 5 Jenis Usulan RKBMD */
    .rkbmd-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        border-top: 4px solid #1e6091;
        background: #fff;
        transition: all 0.2s ease;
    }
    .rkbmd-card:hover { border-color: #1e6091; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
</style>

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">
            <i class="bi bi-pie-chart-fill text-primary me-2"></i>Pusat Laporan & Dashboard Eksekutif
        </h1>
        <p class="page-subtitle text-muted mb-0">
            Monitoring terpadu RKBMD, Standar Satuan Harga (SSH & SBU), dan kepatuhan SKPD Kabupaten Tapin.
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <!-- Tombol Cetak Dokumen Resmi -->
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><h6 class="dropdown-header">Pilih Jenis Cetak (Tahun <?= $tahun ?>)</h6></li>
                <li><a class="dropdown-item" href="<?= site_url("laporan/cetak?modul=rkbmd&tahun={$tahun}&skpd_id={$skpd_id}") ?>" target="_blank"><i class="bi bi-file-earmark-text me-2"></i>Rekapitulasi RKBMD</a></li>
                <li><a class="dropdown-item" href="<?= site_url("laporan/cetak?modul=ssh_sbu&tahun={$tahun}&skpd_id={$skpd_id}") ?>" target="_blank"><i class="bi bi-tags me-2"></i>Rekapitulasi SSH & SBU</a></li>
                <li><a class="dropdown-item" href="<?= site_url("laporan/cetak?modul=kepatuhan&tahun={$tahun}") ?>" target="_blank"><i class="bi bi-shield-check me-2"></i>Matriks Kepatuhan SKPD</a></li>
            </ul>
        </div>

        <!-- Tombol Ekspor Spreadsheet -->
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><h6 class="dropdown-header">Pilih Format Data (Tahun <?= $tahun ?>)</h6></li>
                <li><a class="dropdown-item" href="<?= site_url("laporan/export/rkbmd/excel?tahun={$tahun}&skpd_id={$skpd_id}") ?>"><i class="bi bi-download me-2"></i>Data Usulan RKBMD (.csv)</a></li>
                <li><a class="dropdown-item" href="<?= site_url("laporan/export/ssh_sbu/excel?tahun={$tahun}&skpd_id={$skpd_id}") ?>"><i class="bi bi-download me-2"></i>Data Usulan SSH & SBU (.csv)</a></li>
                <li><a class="dropdown-item" href="<?= site_url("laporan/export/kepatuhan/excel?tahun={$tahun}") ?>"><i class="bi bi-download me-2"></i>Matriks Kepatuhan SKPD (.csv)</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 1. EXECUTIVE KPI SUMMARY CARDS (4 CARDS)                                   -->
<!-- ========================================================================= -->
<div class="row g-3 mb-4">
    <!-- KPI 1: Total Usulan Terdata -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card p-3 d-flex align-items-center gap-3">
            <div class="kpi-icon-wrap kpi-blue">
                <i class="bi bi-collection"></i>
            </div>
            <div class="flex-grow-1">
                <div class="text-muted small fw-semibold">Total Seluruh Usulan</div>
                <div class="h3 fw-bold text-dark mb-0"><?= number_format($kpi['total_usulan']) ?></div>
                <div class="small text-muted" style="font-size: 0.75rem;">
                    <strong><?= $kpi['rkbmd_total'] ?></strong> RKBMD &bull; <strong><?= $kpi['ssh_sbu_total'] ?></strong> Standar Harga
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2: Total Pagu Diusulkan -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card p-3 d-flex align-items-center gap-3">
            <div class="kpi-icon-wrap kpi-indigo">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="flex-grow-1">
                <div class="text-muted small fw-semibold">Pagu Total Diusulkan</div>
                <div class="h4 fw-bold text-indigo mb-0 text-truncate" title="<?= rupiah($kpi['total_nilai']) ?>">
                    <?= rupiah($kpi['total_nilai']) ?>
                </div>
                <div class="small text-muted" style="font-size: 0.75rem;">
                    Berdasarkan usulan TA <?= (int)$tahun ?>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3: Total Nilai Disetujui / Ditetapkan -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card p-3 d-flex align-items-center gap-3">
            <div class="kpi-icon-wrap kpi-green">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="text-muted small fw-semibold">Disetujui / Ditetapkan</div>
                <div class="h4 fw-bold text-success mb-0 text-truncate" title="<?= rupiah($kpi['nilai_disetujui']) ?>">
                    <?= rupiah($kpi['nilai_disetujui']) ?>
                </div>
                <div class="small text-success fw-semibold" style="font-size: 0.75rem;">
                    <i class="bi bi-arrow-up-right"></i> Tingkat Approval: <?= $kpi['approval_rate'] ?>%
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 4: Kepatuhan SKPD -->
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card p-3 d-flex align-items-center gap-3">
            <div class="kpi-icon-wrap kpi-cyan">
                <i class="bi bi-buildings-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="text-muted small fw-semibold">Kepatuhan Partisipasi SKPD</div>
                <div class="h3 fw-bold text-dark mb-0">
                    <?= $kpi['skpd_aktif'] ?> <span class="fs-6 text-muted fw-normal">/ <?= $kpi['total_skpd'] ?> SKPD</span>
                </div>
                <div class="progress mt-1" style="height: 5px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $kpi['kepatuhan_rate'] ?>%;"></div>
                </div>
                <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                    <?= $kpi['kepatuhan_rate'] ?>% Satuan Kerja telah mengusulkan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. INTERACTIVE CHARTS (CHART.JS)                                          -->
<!-- ========================================================================= -->
<div class="row g-3 mb-4">
    <!-- Chart 1: Pagu Anggaran per Jenis Usulan RKBMD -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Distribusi Pagu Anggaran RKBMD per Jenis
                </h6>
                <span class="badge bg-light text-muted border">TA <?= (int)$tahun ?></span>
            </div>
            <div class="card-body p-3">
                <div style="height: 250px;">
                    <canvas id="chartRkbmdBar"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Proporsi Status Usulan (Donut Chart) -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-pie-chart-fill text-info me-2"></i>Proporsi Status Seluruh Usulan
                </h6>
                <span class="badge bg-light text-muted border">RKBMD + Standar Harga</span>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="chartStatusDonut"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 3. FILTER PANEL                                                           -->
<!-- ========================================================================= -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" class="row g-2 align-items-end" id="formFilterLaporan">
            <input type="hidden" name="tab" id="activeTabInput" value="<?= e($activeTab) ?>">

            <div class="col-lg-2 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-calendar3 me-1"></i>Tahun Anggaran</label>
                <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php for ($y = (int)date('Y')+2; $y >= (int)date('Y')-3; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <?php if ($this->currentUser->role !== 'skpd'): ?>
            <div class="col-lg-4 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-building me-1"></i>Satuan Kerja (SKPD)</label>
                <select name="skpd_id" class="form-select form-select-sm">
                    <option value="">-- Semua SKPD --</option>
                    <?php foreach ($skpd_list as $s): ?>
                    <option value="<?= (int)$s->id ?>" <?= (int)$skpd_id === (int)$s->id ? 'selected' : '' ?>>
                        <?= e($s->kode_skpd) ?> - <?= e($s->nama_skpd) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="col-lg-2 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-flag me-1"></i>Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="diajukan" <?= in_array($status, ['diajukan', 'Diajukan']) ? 'selected' : '' ?>>Diajukan</option>
                    <option value="disetujui" <?= in_array($status, ['disetujui', 'Ditetapkan', 'Diverifikasi']) ? 'selected' : '' ?>>Disetujui / Ditetapkan</option>
                    <option value="ditolak" <?= in_array($status, ['ditolak', 'Direvisi']) ? 'selected' : '' ?>>Ditolak / Direvisi</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-search me-1"></i>Kata Kunci</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($q) ?>" placeholder="Nomor / Uraian / SKPD">
            </div>

            <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-semibold">
                    <i class="bi bi-funnel me-1"></i>Terapkan
                </button>
                <a href="<?= site_url('laporan') ?>" class="btn btn-light btn-sm border" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 4. NAVIGATION PILL TABS                                                   -->
<!-- ========================================================================= -->
<ul class="nav nav-pills mb-3 gap-2" id="laporanTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'rkbmd' ? 'active' : '' ?>" id="tab-rkbmd" data-bs-toggle="pill" data-bs-target="#content-rkbmd" type="button" role="tab" onclick="setTab('rkbmd')">
            <i class="bi bi-box-seam me-1"></i> Rekapitulasi RKBMD
            <span class="badge bg-white text-dark ms-1"><?= count($usulanRkbmd) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'ssh_sbu' ? 'active' : '' ?>" id="tab-standar" data-bs-toggle="pill" data-bs-target="#content-standar" type="button" role="tab" onclick="setTab('ssh_sbu')">
            <i class="bi bi-tags-fill me-1"></i> Rekap Standar Harga (SSH & SBU)
            <span class="badge bg-white text-dark ms-1"><?= count($usulanStandar) ?></span>
        </button>
    </li>
    <?php if ($this->currentUser->role !== 'skpd'): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'kepatuhan' ? 'active' : '' ?>" id="tab-kepatuhan" data-bs-toggle="pill" data-bs-target="#content-kepatuhan" type="button" role="tab" onclick="setTab('kepatuhan')">
            <i class="bi bi-shield-check me-1"></i> Matriks Kepatuhan SKPD
            <span class="badge bg-white text-dark ms-1"><?= count($kepatuhanList) ?> SKPD</span>
        </button>
    </li>
    <?php endif; ?>
</ul>

<div class="tab-content" id="laporanTabsContent">

    <!-- ===================================================================== -->
    <!-- TAB 1: REKAPITULASI RKBMD                                             -->
    <!-- ===================================================================== -->
    <div class="tab-pane fade <?= $activeTab === 'rkbmd' ? 'show active' : '' ?>" id="content-rkbmd" role="tabpanel">
        
        <!-- 5 Cards Seimbang Instrumen RKBMD -->
        <div class="row g-3 mb-4">
            <?php
            $cards = [
                'pengadaan'        => ['title' => 'Pengadaan', 'color' => '#0d6efd', 'icon' => 'bi-bag-plus'],
                'pemeliharaan'     => ['title' => 'Pemeliharaan', 'color' => '#0dcaf0', 'icon' => 'bi-tools'],
                'pemanfaatan'      => ['title' => 'Pemanfaatan', 'color' => '#6f42c1', 'icon' => 'bi-building-gear'],
                'pemindahtanganan' => ['title' => 'Pemindahtanganan', 'color' => '#ffc107', 'icon' => 'bi-arrow-left-right'],
                'penghapusan'      => ['title' => 'Penghapusan', 'color' => '#dc3545', 'icon' => 'bi-trash3']
            ];
            foreach ($cards as $key => $cfg):
                $r = $rekapRkbmd[$key];
            ?>
            <div class="col-lg col-md-4 col-sm-6">
                <div class="rkbmd-card p-3 h-100" style="border-top-color: <?= $cfg['color'] ?>;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-dark"><i class="bi <?= $cfg['icon'] ?> me-1"></i><?= $cfg['title'] ?></span>
                        <span class="badge bg-light text-dark border"><?= $r['total'] ?></span>
                    </div>
                    <div class="text-muted small" style="font-size: 0.75rem;">Total Nilai Usulan:</div>
                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;" title="<?= rupiah($r['nilai']) ?>">
                        <?= rupiah($r['nilai']) ?>
                    </div>
                    <div class="d-flex justify-content-between mt-2 pt-2 border-top small" style="font-size: 0.75rem;">
                        <span class="text-success fw-semibold"><i class="bi bi-check-circle"></i> <?= $r['disetujui'] ?> Disetujui</span>
                        <span class="text-warning fw-semibold"><i class="bi bi-hourglass-split"></i> <?= $r['diajukan'] ?> Menunggu</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabel Usulan RKBMD -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-table text-primary me-2"></i>Daftar Usulan RKBMD (<?= count($usulanRkbmd) ?> Usulan)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="dt-rkbmd">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th>Nomor Usulan</th>
                                <th>Jenis</th>
                                <?php if ($this->currentUser->role !== 'skpd'): ?><th>SKPD Pengusul</th><?php endif; ?>
                                <th>Tanggal</th>
                                <th class="text-center">Item</th>
                                <th class="text-end">Total Nilai Usulan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="130">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usulanRkbmd as $i => $u): ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $i+1 ?></td>
                                <td>
                                    <strong class="font-monospace text-dark"><?= e($u->nomor_usulan) ?></strong>
                                    <?php if (!empty($u->keterangan)): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 250px;" title="<?= e($u->keterangan) ?>"><?= e($u->keterangan) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= label_jenis($u->jenis_usulan) ?></td>
                                <?php if ($this->currentUser->role !== 'skpd'): ?>
                                <td><small class="fw-semibold text-dark"><?= e($u->nama_skpd) ?></small></td>
                                <?php endif; ?>
                                <td class="small text-muted"><?= tanggal_id($u->tanggal_usulan) ?></td>
                                <td class="text-center fw-semibold"><?= (int)$u->total_item ?></td>
                                <td class="text-end fw-bold text-dark"><?= rupiah($u->total_nilai) ?></td>
                                <td class="text-center"><?= badge_status($u->status) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url("rkbmd/{$u->jenis_usulan}/detail/{$u->id}") ?>" class="btn btn-outline-secondary" title="Detail Usulan"><i class="bi bi-eye"></i></a>
                                        <a href="<?= site_url("rkbmd/{$u->jenis_usulan}/cetak/{$u->id}") ?>" target="_blank" class="btn btn-outline-info" title="Cetak"><i class="bi bi-printer"></i></a>
                                        <a href="<?= site_url("rkbmd/{$u->jenis_usulan}/excel/{$u->id}") ?>" class="btn btn-outline-success" title="Excel"><i class="bi bi-file-earmark-excel"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: REKAPITULASI STANDAR HARGA (SSH & SBU)                         -->
    <!-- ===================================================================== -->
    <div class="tab-pane fade <?= $activeTab === 'ssh_sbu' ? 'show active' : '' ?>" id="content-standar" role="tabpanel">
        
        <!-- Kartu Komparasi SSH vs SBU -->
        <div class="row g-3 mb-4">
            <!-- Kartu Modul SSH -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #0d6efd !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-box me-1"></i>Standar Satuan Harga (SSH)</h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= $rekapStandar['SSH']['total'] ?> Usulan</span>
                        </div>
                        <div class="row g-2 text-center my-2">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Ditetapkan</div>
                                    <div class="fw-bold text-success fs-5"><?= $rekapStandar['SSH']['ditetapkan'] ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Verifikasi</div>
                                    <div class="fw-bold text-warning fs-5"><?= $rekapStandar['SSH']['diajukan'] + $rekapStandar['SSH']['diverifikasi'] ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Draft/Revisi</div>
                                    <div class="fw-bold text-secondary fs-5"><?= $rekapStandar['SSH']['draft'] + $rekapStandar['SSH']['direvisi'] ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted pt-2 border-top">
                            <span>Total Pagu Usulan:</span>
                            <span class="fw-bold text-dark"><?= rupiah($rekapStandar['SSH']['nilai_usulan']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Modul SBU -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #0dcaf0 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-info"><i class="bi bi-briefcase me-1"></i>Standar Biaya Umum (SBU)</h6>
                            <span class="badge bg-info-subtle text-info border border-info-subtle"><?= $rekapStandar['SBU']['total'] ?> Usulan</span>
                        </div>
                        <div class="row g-2 text-center my-2">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Ditetapkan</div>
                                    <div class="fw-bold text-success fs-5"><?= $rekapStandar['SBU']['ditetapkan'] ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Verifikasi</div>
                                    <div class="fw-bold text-warning fs-5"><?= $rekapStandar['SBU']['diajukan'] + $rekapStandar['SBU']['diverifikasi'] ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Draft/Revisi</div>
                                    <div class="fw-bold text-secondary fs-5"><?= $rekapStandar['SBU']['draft'] + $rekapStandar['SBU']['direvisi'] ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted pt-2 border-top">
                            <span>Total Pagu Usulan:</span>
                            <span class="fw-bold text-dark"><?= rupiah($rekapStandar['SBU']['nilai_usulan']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Usulan Standar Harga -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-tags-fill text-primary me-2"></i>Daftar Usulan Standar Harga (<?= count($usulanStandar) ?> Item)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="dt-standar">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th>Kode Usulan</th>
                                <th>Tipe</th>
                                <?php if ($this->currentUser->role !== 'skpd'): ?><th>SKPD Pengusul</th><?php endif; ?>
                                <th>Nama Barang / Jasa & Spesifikasi</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-end">Harga Usulan</th>
                                <th class="text-center">Bukti Survey</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usulanStandar as $i => $s): 
                                $modUrl = strtolower($s->tipe);
                            ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $i+1 ?></td>
                                <td>
                                    <strong class="font-monospace text-dark"><?= e($s->kode_usulan) ?></strong>
                                    <div class="small text-muted"><?= tanggal_id($s->created_at) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $s->tipe === 'SSH' ? 'bg-primary' : 'bg-info text-white' ?>"><?= e($s->tipe) ?></span>
                                </td>
                                <?php if ($this->currentUser->role !== 'skpd'): ?>
                                <td><small class="fw-semibold text-dark"><?= e($s->nama_skpd) ?></small></td>
                                <?php endif; ?>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($s->uraian) ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 250px;" title="<?= e($s->spesifikasi) ?>">
                                        <?= e($s->spesifikasi) ?>
                                    </div>
                                </td>
                                <td class="text-center small"><?= e($s->satuan) ?></td>
                                <td class="text-end fw-bold text-dark">
                                    <?= rupiah($s->harga_usulan) ?>
                                    <?php if ($s->status_proses === 'Ditetapkan' && $s->harga_ditetapkan): ?>
                                    <div class="small text-success fw-normal">Tetap: <?= rupiah($s->harga_ditetapkan) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1 justify-content-center">
                                        <?php if (!empty($s->file_lampiran)): ?>
                                        <a href="<?= site_url("{$modUrl}/download/{$s->id}/1") ?>" class="btn btn-sm btn-outline-primary py-0 px-1" title="Survey 1"><i class="bi bi-paperclip"></i>1</a>
                                        <?php endif; ?>
                                        <?php if (!empty($s->file_lampiran_2)): ?>
                                        <a href="<?= site_url("{$modUrl}/download/{$s->id}/2") ?>" class="btn btn-sm btn-outline-primary py-0 px-1" title="Survey 2"><i class="bi bi-paperclip"></i>2</a>
                                        <?php endif; ?>
                                        <?php if (!empty($s->file_lampiran_3)): ?>
                                        <a href="<?= site_url("{$modUrl}/download/{$s->id}/3") ?>" class="btn btn-sm btn-outline-primary py-0 px-1" title="Survey 3"><i class="bi bi-paperclip"></i>3</a>
                                        <?php endif; ?>
                                        <?php if (empty($s->file_lampiran) && empty($s->file_lampiran_2) && empty($s->file_lampiran_3)): ?>
                                        <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center"><?= badge_status($s->status_proses) ?></td>
                                <td class="text-center">
                                    <a href="<?= site_url("{$modUrl}/detail/{$s->id}") ?>" class="btn btn-sm btn-outline-secondary" title="Detail Usulan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: MATRIKS KEPATUHAN PENGUSULAN SKPD                              -->
    <!-- ===================================================================== -->
    <?php if ($this->currentUser->role !== 'skpd'): ?>
    <div class="tab-pane fade <?= $activeTab === 'kepatuhan' ? 'show active' : '' ?>" id="content-kepatuhan" role="tabpanel">
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1 fw-bold text-dark">
                        <i class="bi bi-building-check text-success me-2"></i>Status Kepatuhan Pengusulan Seluruh SKPD
                    </h6>
                    <small class="text-muted">Monitoring apakah Organisasi Perangkat Daerah telah mengunggah usulan RKBMD & Standar Harga tahun <?= (int)$tahun ?>.</small>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5">
                    <?= $kpi['skpd_aktif'] ?> dari <?= $kpi['total_skpd'] ?> SKPD Aktif (<?= $kpi['kepatuhan_rate'] ?>%)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="dt-kepatuhan">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th>Kode</th>
                                <th>Nama Satuan Kerja (SKPD)</th>
                                <th class="text-center">Usulan RKBMD</th>
                                <th class="text-center">Usulan SSH/SBU</th>
                                <th class="text-center">Grand Total</th>
                                <th class="text-end">Total Nilai Usulan</th>
                                <th class="text-center" width="130">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kepatuhanList as $i => $row): 
                                $isLengkap = ($row->total_rkbmd > 0 && ($row->total_ssh > 0 || $row->total_sbu > 0));
                                $isSebagian = ($row->grand_total_usulan > 0 && !$isLengkap);
                            ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $i+1 ?></td>
                                <td class="font-monospace small"><?= e($row->kode_skpd) ?></td>
                                <td class="fw-semibold text-dark"><?= e($row->nama_skpd) ?></td>
                                <td class="text-center">
                                    <?php if ($row->total_rkbmd > 0): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= $row->total_rkbmd ?></span>
                                    <?php else: ?>
                                    <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (($row->total_ssh + $row->total_sbu) > 0): ?>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle"><?= $row->total_ssh + $row->total_sbu ?></span>
                                    <?php else: ?>
                                    <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    <?= (int)$row->grand_total_usulan ?>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    <?= rupiah($row->grand_total_nilai) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isLengkap): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>Lengkap</span>
                                    <?php elseif ($isSebagian): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-exclamation-circle-fill me-1"></i>Sebagian</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>Belum Ada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT INITIALIZATION: DATATABLES & CHART.JS                          -->
<!-- ========================================================================= -->
<script>
function setTab(tabName) {
    const inp = document.getElementById('activeTabInput');
    if (inp) inp.value = tabName;
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. DataTables Init
    const dtConfig = {
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada data yang ditemukan.</div>'
        }
    };

    if ($.fn.DataTable) {
        if ($('#dt-rkbmd').length) $('#dt-rkbmd').DataTable(dtConfig);
        if ($('#dt-standar').length) $('#dt-standar').DataTable(dtConfig);
        if ($('#dt-kepatuhan').length) $('#dt-kepatuhan').DataTable(dtConfig);
    }

    // 2. Chart.js: Bar Chart Pagu RKBMD
    const ctxBar = document.getElementById('chartRkbmdBar');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartData['rkbmdBar']['labels']) ?>,
                datasets: [{
                    label: 'Nilai Pagu Usulan (Rp)',
                    data: <?= json_encode($chartData['rkbmdBar']['data']) ?>,
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.8)',
                        'rgba(13, 202, 240, 0.8)',
                        'rgba(111, 66, 193, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        '#0d6efd',
                        '#0dcaf0',
                        '#6f42c1',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 1,
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
                                return ' Pagu: Rp ' + Number(context.raw).toLocaleString('id-ID');
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
                        }
                    }
                }
            }
        });
    }

    // 3. Chart.js: Donut Chart Status Usulan Gabungan
    const ctxDonut = document.getElementById('chartStatusDonut');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chartData['statusDonut']['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($chartData['statusDonut']['data']) ?>,
                    backgroundColor: <?= json_encode($chartData['statusDonut']['colors']) ?>,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
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
                            padding: 15,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + ' berkas';
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
