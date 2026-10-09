<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Master Data Standar Satuan Harga (SSH & SBU)</h1>
        <p class="page-subtitle text-muted mb-0">Katalog resmi Standar Satuan Harga dan Standar Biaya Umum Pemerintah Kabupaten yang telah berstatus Ditetapkan (Read-Only).</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Katalog
        </button>
    </div>
</div>

<!-- Card Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= site_url('ssh/master_data') ?>" class="row g-2 align-items-end">
            <!-- Filter Kategori -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Filter Kategori</label>
                <select class="form-select form-select-sm" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $kat): ?>
                    <option value="<?= e($kat) ?>" <?= ($filter['kategori'] ?? '') === $kat ? 'selected' : '' ?>>
                        <?= e($kat) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter SKPD -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Filter SKPD Pengusul</label>
                <select class="form-select form-select-sm" name="skpd_id">
                    <option value="">Semua SKPD</option>
                    <?php foreach ($skpdList as $s): ?>
                    <option value="<?= $s->id ?>" <?= ($filter['id_skpd'] ?? '') == $s->id ? 'selected' : '' ?>>
                        <?= e($s->nama_skpd) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Tipe -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Tipe</label>
                <select class="form-select form-select-sm" name="tipe">
                    <option value="">Semua (SSH & SBU)</option>
                    <option value="SSH" <?= ($filter['tipe'] ?? '') === 'SSH' ? 'selected' : '' ?>>SSH</option>
                    <option value="SBU" <?= ($filter['tipe'] ?? '') === 'SBU' ? 'selected' : '' ?>>SBU</option>
                </select>
            </div>

            <!-- Pencarian (Search) -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Pencarian Barang / Spesifikasi</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" value="<?= e($filter['q'] ?? '') ?>" placeholder="Ketik kata kunci pencarian...">
                </div>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="<?= site_url('ssh/master_data') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Katalog Master Data (Read-Only) -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Katalog Standar Harga Resmi</h6>
            <small class="text-muted">Data bersifat read-only untuk pedoman penyusunan anggaran seluruh perangkat daerah.</small>
        </div>
        <span class="badge bg-success py-2 px-3"><i class="bi bi-check-circle me-1"></i><?= count($list) ?> Item Ditetapkan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="tabelMasterDataSsh">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Kode Standar</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Uraian Barang / Jasa</th>
                        <th>Spesifikasi Teknis</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-end">Harga Ditetapkan (Rp)</th>
                        <th>SKPD Pengusul</th>
                        <th class="text-center" width="90">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                            <span class="fw-semibold">Tidak ada data standar harga yang sesuai dengan kriteria pencarian/filter.</span>
                        </td>
                    </tr>
                    <?php else: foreach ($list as $idx => $row): ?>
                    <tr>
                        <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                        <td>
                            <strong class="font-monospace text-dark"><?= e($row->kode_usulan) ?></strong>
                            <div class="small text-muted">TA <?= (int)$row->tahun_anggaran ?></div>
                        </td>
                        <td>
                            <span class="badge <?= $row->tipe === 'SSH' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-info-subtle text-info border border-info-subtle' ?>">
                                <?= e($row->tipe) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border"><?= e($row->kategori) ?></span>
                        </td>
                        <td>
                            <strong class="text-dark d-block"><?= e($row->uraian) ?></strong>
                        </td>
                        <td>
                            <div class="small text-muted text-truncate" style="max-width: 250px;" title="<?= e($row->spesifikasi) ?>">
                                <?= e($row->spesifikasi) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= e($row->satuan) ?></span>
                        </td>
                        <td class="text-end fw-bold text-success fs-6">
                            <?= rupiah($row->harga_ditetapkan ?: $row->harga_usulan) ?>
                        </td>
                        <td class="small text-muted">
                            <?= e($row->nama_skpd) ?>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-detail-ssh" data-id="<?= $row->id ?>" title="Lihat detail spesifikasi">
                                <i class="bi bi-eye"></i>
                            </button>
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
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Standar Satuan Harga Resmi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
