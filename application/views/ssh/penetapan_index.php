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
            <i class="bi <?= $isSbu ? 'bi-shield-check text-info' : 'bi-award-fill text-primary' ?> me-2"></i>
            Penetapan <?= $modTitle ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            <?= $isSbu 
                ? 'Pengesahan dan penetapan akhir standar biaya honorarium, jasa, dan sewa yang telah diverifikasi menjadi Master Data resmi.' 
                : 'Pengesahan dan penetapan akhir standar satuan harga barang yang telah diverifikasi menjadi Master Data resmi.' ?>
        </p>
    </div>
    <?php
    $activeStatus = $filter['status_proses'] ?? '';
    $exportQueryBase = [
        'source' => 'penetapan',
    ];
    if (!empty($filter['id_skpd'])) $exportQueryBase['skpd_id'] = $filter['id_skpd'];
    if (!empty($filter['kategori'])) $exportQueryBase['kategori'] = $filter['kategori'];
    if (!empty($filter['tahun'])) $exportQueryBase['tahun'] = $filter['tahun'];
    if (!empty($filter['q'])) $exportQueryBase['q'] = $filter['q'];

    $urlExportCurrent = site_url("{$prefixUrl}/export_sipd?" . http_build_query(array_merge($exportQueryBase, array_filter(['status' => $activeStatus]))));
    $urlExportAll = site_url("{$prefixUrl}/export_sipd?" . http_build_query(array_merge($exportQueryBase, ['status' => 'all'])));
    $urlExportDitetapkan = site_url("{$prefixUrl}/export_sipd?" . http_build_query(array_merge($exportQueryBase, ['status' => 'Ditetapkan'])));
    $urlExportDiverifikasi = site_url("{$prefixUrl}/export_sipd?" . http_build_query(array_merge($exportQueryBase, ['status' => 'Diverifikasi'])));
    ?>
    <div class="dropdown">
        <button class="btn btn-success shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Unduh berkas Excel usulan berformat resmi SIPD RI (form_upload_SIPD.xlsx)">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Ekspor Format Upload SIPD (.xlsx)
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a class="dropdown-item fw-semibold" href="<?= $urlExportCurrent ?>">
                    <i class="bi bi-funnel-fill text-primary me-2"></i> Ekspor Sesuai Tampilan Tabel (<?= $activeStatus ?: 'Semua Status' ?>)
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="<?= $urlExportAll ?>">
                    <i class="bi bi-check2-all text-success me-2"></i> Ekspor Semua Usulan Siap SIPD (Ditetapkan &amp; Diverifikasi)
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="<?= $urlExportDitetapkan ?>">
                    <i class="bi bi-shield-check text-success me-2"></i> Ekspor Hanya yang Telah Ditetapkan (SK Bupati)
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="<?= $urlExportDiverifikasi ?>">
                    <i class="bi bi-hourglass-split text-warning me-2"></i> Ekspor Menunggu Penetapan (Diverifikasi BKAD)
                </a>
            </li>
        </ul>
    </div>
</div>

<?php if (isset($summary)): ?>
<!-- Statistik Antrean Penetapan -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= site_url("{$prefixUrl}/penetapan?status=Diverifikasi") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-warning">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Menunggu Penetapan Resmi</small>
                    <h3 class="mb-0 mt-1 fw-bold text-warning"><?= (int)($summary->diverifikasi ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Siap disahkan Pimpinan</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= site_url("{$prefixUrl}/penetapan?status=Ditetapkan") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-success">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Telah Ditetapkan (SK Bupati)</small>
                    <h3 class="mb-0 mt-1 fw-bold text-success"><?= (int)($summary->ditetapkan ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Aktif di Master Data</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= site_url("{$prefixUrl}/penetapan?status=") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-secondary">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Total Seluruh Berkas</small>
                    <h3 class="mb-0 mt-1 fw-bold text-secondary"><?= (int)($summary->total ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Semua tahapan</small>
                </div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Card Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= site_url("{$prefixUrl}/penetapan") ?>" class="row g-2 align-items-end">
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
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Kategori <?= $tipe ?></label>
                <select class="form-select form-select-sm" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $kat): ?>
                    <option value="<?= e($kat) ?>" <?= ($filter['kategori'] ?? '') === $kat ? 'selected' : '' ?>><?= e($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Cari Keyword</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filter['q'] ?? '') ?>" placeholder="Uraian, spesifikasi...">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> w-100" title="Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="<?= site_url("{$prefixUrl}/penetapan") ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Penetapan -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-check2-all me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
            Daftar Usulan <?= $tipe ?> Diverifikasi untuk Ditetapkan
        </h6>
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
                        <th><?= $isSbu ? 'Uraian Biaya / Ketentuan' : 'Uraian Barang & Spesifikasi' ?></th>
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
                            <span class="fw-semibold">Tidak ada usulan <?= $tipe ?> berstatus 'Diverifikasi' yang menunggu penetapan saat ini.</span>
                        </td>
                    </tr>
                    <?php else: foreach ($list as $idx => $row): ?>
                    <tr id="row-penetap-<?= $row->id ?>">
                        <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                        <td>
                            <strong class="font-monospace text-dark"><?= e($row->kode_usulan) ?></strong>
                            <div class="small text-muted"><?= e($row->kategori) ?></div>
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
                            <div class="d-inline-flex gap-1 align-items-center">
                                <?php if ($row->status_proses === 'Diverifikasi'): ?>
                                <form action="<?= site_url("{$prefixUrl}/proses-penetapan/{$row->id}") ?>" method="post" class="d-inline form-tetapkan-usulan mb-0" 
                                      data-kode="<?= e($row->kode_usulan) ?>" data-uraian="<?= e($row->uraian) ?>">
                                    <?= csrf_input() ?>
                                    <button type="submit" class="btn btn-sm <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> shadow-sm" title="Tetapkan dan kunci standar harga resmi">
                                        <i class="bi bi-shield-check me-1"></i> Tetapkan
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" data-prefix="<?= $prefixUrl ?>" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-lock-fill me-1"></i> Terkunci
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" data-prefix="<?= $prefixUrl ?>" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-success btn-kirim-wa-ssh" 
                                        data-id="<?= $row->id ?>" 
                                        data-prefix="<?= $prefixUrl ?>" 
                                        data-status="<?= $row->status_proses ?>"
                                        title="Kirim Notifikasi WhatsApp ke SKPD">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                            </div>
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
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Usulan <?= $modTitle ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
        </div>
    </div>
</div>
