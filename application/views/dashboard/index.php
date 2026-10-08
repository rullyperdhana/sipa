<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
        <p class="page-subtitle text-muted">Selamat datang, <strong><?= e($this->currentUser->nama_lengkap) ?></strong>. Ringkasan RKBMD tahun <?= e($tahun) ?>.</p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
        <label class="small text-muted mb-0">Tahun:</label>
        <select name="tahun" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <?php for ($y = (int)date('Y')+1; $y >= (int)date('Y')-3; $y--): ?>
                <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <label class="small text-muted mb-0">Jenis:</label>
        <select name="jenis" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <?php foreach (['pengadaan'=>'Pengadaan','pemeliharaan'=>'Pemeliharaan','pemanfaatan'=>'Pemanfaatan','pemindahtanganan'=>'Pemindahtanganan','penghapusan'=>'Penghapusan'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= $k === $jenis ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <label class="small text-muted mb-0">Periode:</label>
        <select name="periode_id" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <?php foreach ($periodeList as $p): ?>
                <option value="<?= $p->id ?>" <?= $p->id === $periodeId ? 'selected' : '' ?>><?= e($p->tahun) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($this->currentUser->role !== 'skpd'): ?>
        <label class="small text-muted mb-0">SKPD:</label>
        <select name="skpd_id" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="">Semua SKPD</option>
            <?php foreach ($skpdList as $s): ?>
                <option value="<?= $s->id ?>" <?= $s->id == $skpdId ? 'selected' : '' ?>><?= e($s->nama_skpd) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </form>
</div>

<!-- Statistik Utama -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total Usulan</div>
                <div class="stat-value"><?= number_format(array_sum(array_column($rekap,'total'))) ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-info">
                <div class="stat-label">Menunggu Verifikasi</div>
                <div class="stat-value"><?= number_format(array_sum(array_column($rekap,'diajukan'))) ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Disetujui</div>
                <div class="stat-value"><?= number_format(array_sum(array_column($rekap,'disetujui'))) ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total Nilai</div>
                <div class="stat-value" style="font-size:16px;"><?= rupiah($totalNilai) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Rekap per Jenis -->
<h5 class="mb-3"><i class="bi bi-bar-chart-line me-2"></i>Rekap per Jenis Usulan</h5>
<div class="row g-3 mb-4">
<?php
$jenisIcons = [
    'pengadaan'        => ['cart-plus-fill',   '#1e6091'],
    'pemeliharaan'     => ['tools',             '#0891b2'],
    'pemanfaatan'      => ['share-fill',        '#7c3aed'],
    'pemindahtanganan' => ['arrow-left-right',  '#ea580c'],
    'penghapusan'      => ['trash3-fill',       '#dc2626']
];
foreach ($rekap as $jenis => $data):
    $icon = $jenisIcons[$jenis];
?>
<div class="col-lg-4 col-md-6">
    <div class="card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:<?= $icon[1] ?>18;color:<?= $icon[1] ?>;width:44px;height:44px;font-size:20px;">
                        <i class="bi bi-<?= $icon[0] ?>"></i>
                    </div>
                    <div>
                        <h6 class="mb-0"><?= label_jenis($jenis) ?></h6>
                        <small class="text-muted"><?= $data['total'] ?> usulan</small>
                    </div>
                </div>
                <a href="<?= site_url('rkbmd/'.$jenis) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-1 text-center mb-2">
                <div class="col-3"><div class="small text-muted">Draft</div><div class="fw-bold"><?= $data['draft'] ?></div></div>
                <div class="col-3"><div class="small text-muted">Ajukan</div><div class="fw-bold text-info"><?= $data['diajukan'] ?></div></div>
                <div class="col-3"><div class="small text-muted">Setuju</div><div class="fw-bold text-success"><?= $data['disetujui'] ?></div></div>
                <div class="col-3"><div class="small text-muted">Tolak</div><div class="fw-bold text-danger"><?= $data['ditolak'] ?></div></div>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Total Nilai</small>
                <strong class="text-primary"><?= rupiah($data['nilai']) ?></strong>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Monitoring Usulan SKPD -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0"><i class="bi bi-eye-fill me-2"></i>Monitoring Usulan SKPD</h6>
            <small class="text-muted">Status input usulan untuk jenis <?= e(label_jenis($jenis)) ?> pada periode terpilih.</small>
        </div>
        <span class="badge bg-secondary">SKPD terdaftar: <?= count($monitoring) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>SKPD</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Draft</th>
                    <th class="text-center">Diajukan</th>
                    <th class="text-center">Diverifikasi</th>
                    <th class="text-center">Disetujui</th>
                    <th class="text-center">Ditolak</th>
                    <th class="text-center">Revisi</th>
                    <th class="text-center">Terakhir Input</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($monitoring)): ?>
                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada data monitoring.</td></tr>
                <?php else: foreach ($monitoring as $row): ?>
                <tr>
                    <td><?= e($row['nama_skpd']) ?></td>
                    <td class="text-center"><strong><?= number_format($row['total']) ?></strong></td>
                    <td class="text-center"><?= number_format($row['counts']['draft']) ?></td>
                    <td class="text-center"><?= number_format($row['counts']['diajukan']) ?></td>
                    <td class="text-center"><?= number_format($row['counts']['diverifikasi']) ?></td>
                    <td class="text-center"><?= number_format($row['counts']['disetujui']) ?></td>
                    <td class="text-center"><?= number_format($row['counts']['ditolak']) ?></td>
                    <td class="text-center"><?= number_format($row['counts']['revisi']) ?></td>
                    <td class="text-center"><?= $row['last_update'] ? tanggal_id($row['last_update']) : '-' ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Usulan Terbaru -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Usulan Terbaru</h6>
        <a href="<?= site_url('laporan') ?>" class="btn btn-sm btn-link">Lihat Semua <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>No. Usulan</th>
                    <th>Jenis</th>
                    <?php if ($this->currentUser->role !== 'skpd'): ?><th>SKPD</th><?php endif; ?>
                    <th>Tanggal</th>
                    <th class="text-center">Item</th>
                    <th class="text-end">Total Nilai</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usulanTerbaru)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Belum ada usulan.</td></tr>
                <?php else: foreach ($usulanTerbaru as $u): ?>
                <tr>
                    <td><strong><?= e($u->nomor_usulan) ?></strong></td>
                    <td><small><?= label_jenis($u->jenis_usulan) ?></small></td>
                    <?php if ($this->currentUser->role !== 'skpd'): ?>
                    <td><small class="text-muted"><?= e($u->nama_skpd) ?></small></td>
                    <?php endif; ?>
                    <td><?= tanggal_id($u->tanggal_usulan) ?></td>
                    <td class="text-center"><?= (int)$u->total_item ?></td>
                    <td class="text-end"><?= rupiah($u->total_nilai) ?></td>
                    <td class="text-center"><?= badge_status($u->status) ?></td>
                    <td class="text-center">
                        <a href="<?= site_url('rkbmd/'.$u->jenis_usulan.'/detail/'.$u->id) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
