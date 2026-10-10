<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi <?= $isSbu ? 'bi-receipt-cutoff text-info' : 'bi-box-seam-fill text-primary' ?> me-2"></i>
            Usulan <?= $modTitle ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            <?= $isSbu 
                ? 'Kelola dan ajukan usulan standar biaya honorarium, jasa tenaga kerja, sewa, dan tarif operasional SKPD Anda.' 
                : 'Kelola dan ajukan usulan harga satuan barang, peralatan, dan material fisik SKPD Anda.' ?>
        </p>
    </div>
    <div>
        <a href="<?= site_url("{$prefixUrl}/tambah") ?>" class="btn <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Usulan <?= $tipe ?> Baru
        </a>
    </div>
</div>

<!-- Statistik Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 <?= $isSbu ? 'border-info' : 'border-primary' ?>">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Total Usulan</small>
                <h3 class="mb-0 mt-1 fw-bold <?= $isSbu ? 'text-info' : 'text-primary' ?>"><?= (int)($summary->total ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-secondary">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Draft</small>
                <h3 class="mb-0 mt-1 fw-bold text-secondary"><?= (int)($summary->draft ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-info">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Diajukan</small>
                <h3 class="mb-0 mt-1 fw-bold text-info"><?= (int)($summary->diajukan ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-warning">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Direvisi</small>
                <h3 class="mb-0 mt-1 fw-bold text-warning"><?= (int)($summary->direvisi ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-primary">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Diverifikasi</small>
                <h3 class="mb-0 mt-1 fw-bold text-primary"><?= (int)($summary->diverifikasi ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-success">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Ditetapkan</small>
                <h3 class="mb-0 mt-1 fw-bold text-success"><?= (int)($summary->ditetapkan ?? 0) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Card Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= site_url("{$prefixUrl}/usulan") ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Status Usulan</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">Semua Status</option>
                    <?php foreach (['Draft', 'Diajukan', 'Direvisi', 'Diverifikasi', 'Ditetapkan'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($filter['status_proses'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Kategori <?= $tipe ?></label>
                <select class="form-select form-select-sm" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $kat): ?>
                    <option value="<?= e($kat) ?>" <?= ($filter['kategori'] ?? '') === $kat ? 'selected' : '' ?>><?= e($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Cari <?= $isSbu ? 'Biaya / Jasa' : 'Barang' ?></label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filter['q'] ?? '') ?>" placeholder="Kode, uraian, spesifikasi...">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="<?= site_url("{$prefixUrl}/usulan") ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Usulan -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-table me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
            Daftar Usulan <?= $modTitle ?>
        </h6>
        <span class="badge bg-light text-dark border"><?= count($list) ?> Data ditemukan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Kode Usulan</th>
                        <th>Kategori</th>
                        <th><?= $isSbu ? 'Uraian Biaya / Ketentuan' : 'Uraian Barang & Spesifikasi' ?></th>
                        <th class="text-center">Satuan</th>
                        <th class="text-end">Harga Usulan</th>
                        <th class="text-center">Lampiran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <span class="fw-semibold">Belum ada data usulan <?= $tipe ?>.</span><br>
                            <small>Klik tombol <strong>Tambah Usulan <?= $tipe ?> Baru</strong> untuk mulai membuat usulan.</small>
                        </td>
                    </tr>
                    <?php else: foreach ($list as $idx => $row): ?>
                    <tr id="row-ssh-<?= $row->id ?>">
                        <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                        <td>
                            <strong class="text-dark font-monospace"><?= e($row->kode_usulan) ?></strong>
                            <div class="small text-muted"><?= tanggal_id($row->created_at) ?></div>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border"><?= e($row->kategori) ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($row->uraian) ?></div>
                            <div class="small text-muted text-truncate" style="max-width: 320px;" title="<?= e($row->spesifikasi) ?>">
                                <?= e($row->spesifikasi) ?>
                            </div>
                            <?php if ($row->status_proses === 'Direvisi' && !empty($row->catatan_verifikator)): ?>
                            <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small border-warning d-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0"></i>
                                <span><strong>Catatan Koreksi:</strong> <?= e($row->catatan_verifikator) ?></span>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= e($row->satuan) ?></span>
                        </td>
                        <td class="text-end fw-bold text-dark">
                            <?= rupiah($row->harga_usulan) ?>
                            <?php if ($row->status_proses === 'Ditetapkan' && $row->harga_ditetapkan): ?>
                            <div class="small text-success fw-normal">Tetap: <?= rupiah($row->harga_ditetapkan) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($row->file_lampiran)): ?>
                            <a href="<?= site_url("{$prefixUrl}/download/{$row->id}") ?>" class="btn btn-sm btn-outline-secondary" title="<?= e($row->file_nama_asli ?: 'Download Lampiran') ?>">
                                <i class="bi bi-paperclip"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?= badge_status($row->status_proses) ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- Tombol Kirim Usulan jika Draft atau Direvisi -->
                                <?php if (in_array($row->status_proses, ['Draft', 'Direvisi'])): ?>
                                <button type="button" class="btn btn-success btn-kirim-usulan" 
                                        data-id="<?= $row->id ?>" 
                                        data-kode="<?= e($row->kode_usulan) ?>"
                                        data-uraian="<?= e($row->uraian) ?>"
                                        data-prefix="<?= $prefixUrl ?>"
                                        title="<?= $row->status_proses === 'Draft' ? 'Kirim Usulan ke BPKAD' : 'Kirim Ulang Usulan Hasil Revisi' ?>">
                                    <i class="bi bi-send-fill me-1"></i> <?= $row->status_proses === 'Draft' ? 'Kirim' : 'Kirim Ulang' ?>
                                </button>
                                <a href="<?= site_url("{$prefixUrl}/edit/{$row->id}") ?>" class="btn btn-outline-primary" title="Edit usulan">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <?php if ($row->status_proses === 'Draft'): ?>
                                <a href="<?= site_url("{$prefixUrl}/hapus/{$row->id}") ?>" class="btn btn-outline-danger btn-hapus-usulan" 
                                   data-confirm="Hapus usulan <?= e($row->kode_usulan) ?>?" title="Hapus draft">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                                <?php else: ?>
                                <!-- Jika Diajukan, Diverifikasi, atau Ditetapkan -> Terkunci / Read-Only -->
                                <button type="button" class="btn btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" data-prefix="<?= $prefixUrl ?>" title="Lihat detail">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Data berstatus <?= $row->status_proses ?> telah dikunci">
                                    <button class="btn btn-outline-secondary" disabled><i class="bi bi-lock-fill"></i></button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail & History Audit Trail -->
<div class="modal fade" id="modalDetailSsh" tabindex="-1" aria-labelledby="modalDetailSshLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalDetailSshLabel"><i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Usulan <?= $modTitle ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
