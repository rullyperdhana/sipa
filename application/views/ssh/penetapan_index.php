<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-award-fill text-primary me-2"></i>Penetapan Standar Harga (SSH & SBU)</h1>
        <p class="page-subtitle text-muted mb-0">Pengesahan dan penetapan akhir standar harga yang telah diverifikasi BPKAD menjadi Master Data resmi daerah.</p>
    </div>
</div>

<!-- Card Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= site_url('ssh/penetapan') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Status Usulan</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="Diverifikasi" <?= ($filter['status_proses'] ?? '') === 'Diverifikasi' ? 'selected' : '' ?>>Menunggu Penetapan (Diverifikasi)</option>
                    <option value="Ditetapkan" <?= ($filter['status_proses'] ?? '') === 'Ditetapkan' ? 'selected' : '' ?>>Sudah Ditetapkan (Resmi)</option>
                    <option value="" <?= ($filter['status_proses'] ?? '') === '' ? 'selected' : '' ?>>Semua Status</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">SKPD Pengusul</label>
                <select class="form-select form-select-sm" name="skpd_id">
                    <option value="">Semua SKPD</option>
                    <?php foreach ($skpdList as $s): ?>
                    <option value="<?= $s->id ?>" <?= ($filter['id_skpd'] ?? '') == $s->id ? 'selected' : '' ?>>
                        <?= e($s->nama_skpd) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Tipe</label>
                <select class="form-select form-select-sm" name="tipe">
                    <option value="">Semua</option>
                    <option value="SSH" <?= ($filter['tipe'] ?? '') === 'SSH' ? 'selected' : '' ?>>SSH</option>
                    <option value="SBU" <?= ($filter['tipe'] ?? '') === 'SBU' ? 'selected' : '' ?>>SBU</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Cari Keyword</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filter['q'] ?? '') ?>" placeholder="Uraian, spesifikasi...">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="<?= site_url('ssh/penetapan') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Penetapan -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold"><i class="bi bi-check2-all me-2 text-primary"></i>Daftar Usulan Diverifikasi untuk Ditetapkan</h6>
        <span class="badge bg-success"><?= count($list) ?> Item</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Kode Usulan</th>
                        <th>SKPD Pengusul</th>
                        <th>Uraian & Spesifikasi</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-end">Harga Usulan</th>
                        <th class="text-end">Harga Hasil Verifikasi</th>
                        <th class="text-center">Verifikator</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-check fs-1 d-block mb-2 text-secondary"></i>
                            <span class="fw-semibold">Tidak ada usulan berstatus 'Diverifikasi' yang menunggu penetapan saat ini.</span>
                        </td>
                    </tr>
                    <?php else: foreach ($list as $idx => $row): ?>
                    <tr id="row-penetap-<?= $row->id ?>">
                        <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                        <td>
                            <strong class="font-monospace text-dark"><?= e($row->kode_usulan) ?></strong>
                            <div class="small text-muted"><?= e($row->tipe) ?> &bull; <?= e($row->kategori) ?></div>
                        </td>
                        <td>
                            <strong class="text-dark d-block"><?= e($row->nama_skpd) ?></strong>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($row->uraian) ?></div>
                            <div class="small text-muted text-truncate" style="max-width: 280px;" title="<?= e($row->spesifikasi) ?>">
                                <?= e($row->spesifikasi) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= e($row->satuan) ?></span>
                        </td>
                        <td class="text-end text-muted small">
                            <?= rupiah($row->harga_usulan) ?>
                        </td>
                        <td class="text-end fw-bold text-success fs-6">
                            <?= rupiah($row->harga_ditetapkan ?: $row->harga_usulan) ?>
                        </td>
                        <td class="text-center small">
                            <div class="text-dark fw-semibold"><?= e($row->nama_verifikator ?: 'Tim Verifikator') ?></div>
                            <small class="text-muted"><?= tanggal_id($row->tgl_verifikasi) ?></small>
                        </td>
                        <td class="text-center">
                            <?= badge_status($row->status_proses) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row->status_proses === 'Diverifikasi'): ?>
                            <form action="<?= site_url('ssh/proses-penetapan/' . $row->id) ?>" method="post" class="d-inline form-tetapkan-usulan" 
                                  data-kode="<?= e($row->kode_usulan) ?>" data-uraian="<?= e($row->uraian) ?>">
                                <?= csrf_input() ?>
                                <button type="submit" class="btn btn-sm btn-primary shadow-sm" title="Tetapkan dan kunci standar harga resmi">
                                    <i class="bi bi-shield-check me-1"></i> Tetapkan
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php else: ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-lock-fill me-1"></i> Terkunci
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-ssh ms-1" data-id="<?= $row->id ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetailSsh" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Usulan Standar Harga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
        </div>
    </div>
</div>
