<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';
$stats = $summarySurat ?? $summary;
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi <?= $isSbu ? 'bi-check2-circle text-info' : 'bi-patch-check-fill text-primary' ?> me-2"></i>
            Verifikasi Usulan <?= $modTitle ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            <?= $isSbu 
                ? 'Verifikasi dan telaah usulan standar honorarium, jasa tenaga ahli, dan sewa dari seluruh SKPD sebelum diajukan ke penetapan.' 
                : 'Verifikasi dan telaah usulan standar harga satuan barang dan material fisik dari seluruh SKPD sebelum diajukan ke penetapan.' ?>
        </p>
    </div>
</div>

<?php if (isset($stats)): ?>
<!-- Statistik Antrean Verifikasi -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=Diajukan") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-warning">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Menunggu Verifikasi</small>
                    <h3 class="mb-0 mt-1 fw-bold text-warning"><?= (int)($stats->diajukan ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Perlu ditelaah BPKAD</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=Diverifikasi") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-primary">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Telah Diverifikasi</small>
                    <h3 class="mb-0 mt-1 fw-bold text-primary"><?= (int)($stats->diverifikasi ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Siap ke penetapan</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=Direvisi") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-warning">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Perlu Revisi SKPD</small>
                    <h3 class="mb-0 mt-1 fw-bold text-warning"><?= (int)($stats->direvisi ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Dikembalikan</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=Ditolak") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-danger">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Ditolak</small>
                    <h3 class="mb-0 mt-1 fw-bold text-danger"><?= (int)($stats->ditolak ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Tidak disetujui</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-4 col-lg">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=all") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-secondary">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Total Seluruh Surat</small>
                    <h3 class="mb-0 mt-1 fw-bold text-secondary"><?= (int)($stats->total ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Semua status</small>
                </div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Card Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= site_url("{$prefixUrl}/verifikasi") ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Status Usulan</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="Diajukan" <?= ($filter['status_proses'] ?? '') === 'Diajukan' ? 'selected' : '' ?>>Menunggu Verifikasi (Diajukan)</option>
                    <option value="Diverifikasi" <?= ($filter['status_proses'] ?? '') === 'Diverifikasi' ? 'selected' : '' ?>>Sudah Diverifikasi</option>
                    <option value="Direvisi" <?= ($filter['status_proses'] ?? '') === 'Direvisi' ? 'selected' : '' ?>>Dikembalikan (Direvisi)</option>
                    <option value="Ditolak" <?= ($filter['status_proses'] ?? '') === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    <option value="all" <?= in_array($filter['status_proses'] ?? '', ['all', ''], TRUE) ? 'selected' : '' ?>>Semua Status</option>
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
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filter['q'] ?? '') ?>" placeholder="Nomor surat, uraian...">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="<?= site_url("{$prefixUrl}/verifikasi") ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Card Terpadu: Tab Navigasi Antrean & Konten Tabel -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom pt-3 px-3 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <ul class="nav nav-tabs card-header-tabs" id="verifTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-surat-btn" data-bs-toggle="tab" data-bs-target="#tab-surat-content" type="button" role="tab" aria-controls="tab-surat-content" aria-selected="true">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1.5"></i>
                    Antrean Surat Usulan Resmi
                    <span class="badge bg-primary ms-1.5"><?= count($suratList ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-item-btn" data-bs-toggle="tab" data-bs-target="#tab-item-content" type="button" role="tab" aria-controls="tab-item-content" aria-selected="false">
                    <i class="bi bi-list-columns-reverse text-primary me-1.5"></i>
                    Semua Rincian Item (Flat View)
                    <span class="badge bg-secondary ms-1.5"><?= count($list ?? []) ?></span>
                </button>
            </li>
        </ul>
        <div class="text-muted small pb-2 d-none d-md-block">
            <i class="bi bi-shield-check me-1 text-primary"></i> Telaah & verifikasi berbasis <strong>Berkas Surat Resmi</strong> dari SKPD
        </div>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="verifTabsContent">
            <!-- TAB 1: ANTREAN BERBASIS SURAT USULAN RESMI (ALUR UTAMA) -->
            <div class="tab-pane fade show active" id="tab-surat-content" role="tabpanel" aria-labelledby="tab-surat-btn">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th width="45" class="text-center">#</th>
                                <th style="min-width: 200px;">Nomor & Tanggal Surat</th>
                                <th style="min-width: 200px;">SKPD Pengusul</th>
                                <th style="min-width: 220px;">Perihal Usulan</th>
                                <th class="text-center" style="width: 120px;">Berkas Surat</th>
                                <th class="text-center" style="width: 110px;">Jumlah Item</th>
                                <th class="text-end text-nowrap" style="width: 140px;">Total Nominal</th>
                                <th class="text-center text-nowrap" style="width: 110px;">Status</th>
                                <th class="text-center text-nowrap" style="width: 180px;">Aksi Verifikator</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suratList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                    <span class="fw-semibold">Tidak ada berkas Surat Usulan <?= $tipe ?> yang menunggu verifikasi saat ini.</span>
                                </td>
                            </tr>
                            <?php else: foreach ($suratList as $idx => $sRow): ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark font-monospace"><?= e($sRow->nomor_surat) ?></strong>
                                    <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i><?= tanggal_id($sRow->tanggal_surat) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($sRow->nama_skpd) ?></div>
                                    <div class="small text-muted">Oleh: <?= e($sRow->nama_pengusul ?? 'Operator') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($sRow->perihal) ?></div>
                                    <?php if (!empty($sRow->keterangan)): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 280px;" title="<?= e($sRow->keterangan) ?>">
                                        <?= e($sRow->keterangan) ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($sRow->file_surat)): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 btn-preview-surat-pdf" 
                                            data-url="<?= site_url("{$prefixUrl}/surat/preview/{$sRow->id}") ?>"
                                            data-download="<?= site_url("{$prefixUrl}/surat/download/{$sRow->id}") ?>"
                                            data-nomor="<?= e($sRow->nomor_surat) ?>"
                                            data-nama="<?= e($sRow->file_nama_asli ?: $sRow->file_surat) ?>"
                                            title="Pratinjau Surat Resmi PDF di Layar: <?= e($sRow->file_nama_asli ?: $sRow->file_surat) ?>">
                                        <i class="bi bi-eye me-1"></i> Lihat PDF
                                    </button>
                                    <?php else: ?>
                                    <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fs-6 px-2.5 py-1">
                                        <i class="bi bi-list-check me-1"></i><?= (int)$sRow->total_item ?> Item
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark font-monospace">
                                    <?= rupiah($sRow->total_nominal) ?>
                                </td>
                                <td class="text-center">
                                    <?= badge_status($sRow->status_proses) ?>
                                </td>
                                <td class="text-center">
                                    <!-- Tombol Verifikasi Surat Usulan -->
                                    <a href="<?= site_url("{$prefixUrl}/verifikasi/surat/{$sRow->id}") ?>" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="bi bi-shield-check me-1"></i> Periksa & Verifikasi
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: SEMUA RINCIAN ITEM (FLAT VIEW) -->
            <div class="tab-pane fade" id="tab-item-content" role="tabpanel" aria-labelledby="tab-item-btn">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th width="45" class="text-center">#</th>
                                <th style="min-width: 170px;">Kode Usulan</th>
                                <th style="min-width: 180px;">SKPD Pengusul</th>
                                <th style="min-width: 260px;"><?= $isSbu ? 'Uraian Biaya / Ketentuan' : 'Uraian Barang & Spesifikasi' ?></th>
                                <th class="text-center" style="width: 90px;">Satuan</th>
                                <th class="text-end text-nowrap" style="width: 140px;">Harga Usulan</th>
                                <th class="text-center text-nowrap" style="width: 110px;">Bukti Survey</th>
                                <th class="text-center text-nowrap" style="width: 110px;">Status</th>
                                <th class="text-center text-nowrap" style="width: 220px;">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                    <span class="fw-semibold">Tidak ada usulan <?= $tipe ?> yang menunggu verifikasi saat ini.</span>
                                </td>
                            </tr>
                            <?php else: foreach ($list as $idx => $row): ?>
                            <tr id="row-verif-<?= $row->id ?>">
                                <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark font-monospace"><?= e($row->kode_usulan) ?></strong>
                                    <div class="small text-muted"><?= tanggal_id($row->created_at) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($row->nama_skpd) ?></div>
                                    <small class="text-muted">Pengusul: <?= e($row->nama_pengusul ?? '-') ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($row->uraian) ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 320px;" title="<?= e($row->spesifikasi) ?>">
                                        <?= e($row->spesifikasi) ?>
                                    </div>
                                    <div class="mt-1 d-flex gap-1 flex-wrap">
                                        <span class="badge bg-light text-secondary border"><?= e($row->kategori) ?></span>
                                        <?php if (!empty($row->kode_rekening)): ?>
                                        <span class="badge bg-light text-dark border font-monospace"><?= e($row->kode_rekening) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?= e($row->satuan) ?></span>
                                </td>
                                <td class="text-end fw-bold text-dark font-monospace">
                                    <?= rupiah($row->harga_usulan) ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1 justify-content-center">
                                        <?php if (!empty($row->file_lampiran)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1.5 btn-preview-lampiran" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalPreviewBukti"
                                                data-id="<?= $row->id ?>" 
                                                data-prefix="<?= $prefixUrl ?>" 
                                                data-slot="1" 
                                                title="Pratinjau Survey 1: <?= e($row->file_nama_asli ?: 'Berkas 1') ?>" 
                                                onclick="if(window.SshModule && window.SshModule.openPreview){ window.SshModule.openPreview(<?= $row->id ?>, '<?= $prefixUrl ?>', 1); }">
                                            <i class="bi bi-eye"></i> S1
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!empty($row->file_lampiran_2)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1.5 btn-preview-lampiran" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalPreviewBukti"
                                                data-id="<?= $row->id ?>" 
                                                data-prefix="<?= $prefixUrl ?>" 
                                                data-slot="2" 
                                                title="Pratinjau Survey 2: <?= e($row->file_nama_asli_2 ?: 'Berkas 2') ?>" 
                                                onclick="if(window.SshModule && window.SshModule.openPreview){ window.SshModule.openPreview(<?= $row->id ?>, '<?= $prefixUrl ?>', 2); }">
                                            <i class="bi bi-eye"></i> S2
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!empty($row->file_lampiran_3)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1.5 btn-preview-lampiran" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalPreviewBukti"
                                                data-id="<?= $row->id ?>" 
                                                data-prefix="<?= $prefixUrl ?>" 
                                                data-slot="3" 
                                                title="Pratinjau Survey 3: <?= e($row->file_nama_asli_3 ?: 'Berkas 3') ?>" 
                                                onclick="if(window.SshModule && window.SshModule.openPreview){ window.SshModule.openPreview(<?= $row->id ?>, '<?= $prefixUrl ?>', 3); }">
                                            <i class="bi bi-eye"></i> S3
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?= badge_status($row->status_proses) ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($row->status_proses === 'Diajukan'): ?>
                                        <button type="button" class="btn btn-sm btn-success btn-verif-setujui" 
                                                data-id="<?= $row->id ?>" 
                                                data-kode="<?= e($row->kode_usulan) ?>" 
                                                data-uraian="<?= e($row->uraian) ?>" 
                                                data-harga="<?= (float)$row->harga_usulan ?>" 
                                                data-prefix="<?= $prefixUrl ?>">
                                            <i class="bi bi-check-lg"></i> Setujui
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning text-dark btn-verif-revisi" 
                                                data-id="<?= $row->id ?>" 
                                                data-kode="<?= e($row->kode_usulan) ?>" 
                                                data-uraian="<?= e($row->uraian) ?>" 
                                                data-prefix="<?= $prefixUrl ?>">
                                            <i class="bi bi-arrow-repeat"></i> Revisi
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-verif-tolak" 
                                                data-id="<?= $row->id ?>" 
                                                data-kode="<?= e($row->kode_usulan) ?>" 
                                                data-uraian="<?= e($row->uraian) ?>" 
                                                data-prefix="<?= $prefixUrl ?>">
                                            <i class="bi bi-x-circle"></i> Tolak
                                        </button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" data-prefix="<?= $prefixUrl ?>">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
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
    </div>
</div>

<!-- Modal Pratinjau Surat Usulan PDF -->
<div class="modal fade" id="modalPreviewSuratPdf" tabindex="-1" aria-labelledby="modalPreviewSuratPdfLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw;">
        <div class="modal-content border-0 shadow-lg" style="height: 88vh;">
            <div class="modal-header bg-light border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-4"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="modalPreviewSuratPdfLabel">Pratinjau Berkas Surat Usulan Resmi</h6>
                        <small class="text-muted" id="previewSuratPdfSub">-</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnDownloadSuratModal" class="btn btn-sm btn-outline-secondary" download>
                        <i class="bi bi-download me-1"></i> Unduh
                    </a>
                    <a href="#" id="btnOpenNewTabSuratModal" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Tab Baru
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body p-0 bg-dark-subtle position-relative overflow-hidden" style="flex: 1 1 auto;">
                <iframe id="iframeSuratPdf" src="about:blank" class="w-100 h-100 border-0" style="min-height: 100%;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verifikasi: Setujui -->
<div class="modal fade" id="modalVerifSetujui" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle me-2"></i>Setujui Usulan <?= $tipe ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSetujuiUsulan" method="post" action="">
                <?= csrf_input() ?>
                <input type="hidden" name="aksi" value="setujui">
                <div class="modal-body">
                    <p class="mb-3">Usulan <strong id="setujuiKode"></strong> (<span id="setujuiUraian"></span>) akan disetujui untuk diajukan ke penetapan.</p>
                    
                    <div class="mb-3">
                        <label for="setujuiHarga" class="form-label fw-semibold">Harga Standar yang Disetujui (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="text" class="form-control fw-bold fs-6 mask-rupiah" id="setujuiHarga" name="harga_ditetapkan" required>
                        </div>
                        <div class="form-text small">Secara default diisi harga usulan SKPD. Ubah jika ada penyesuaian dari hasil survei BPKAD.</div>
                    </div>

                    <div class="mb-3">
                        <label for="setujuiCatatan" class="form-label fw-semibold">Catatan Verifikator (Opsional)</label>
                        <textarea class="form-control" id="setujuiCatatan" name="catatan_verifikator" rows="2" placeholder="Catatan hasil verifikasi atau dasar penyesuaian harga..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold"><i class="bi bi-check-lg me-1"></i> Konfirmasi Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Verifikasi: Revisi -->
<div class="modal fade" id="modalVerifRevisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Kembalikan untuk Direvisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRevisiUsulan" method="post" action="">
                <?= csrf_input() ?>
                <input type="hidden" name="aksi" value="revisi">
                <div class="modal-body">
                    <p class="mb-2">Usulan <strong id="revisiKode"></strong> (<span id="revisiUraian"></span>) akan dikembalikan ke SKPD untuk diperbaiki.</p>
                    
                    <div class="mb-3">
                        <label for="revisiCatatan" class="form-label fw-semibold">Catatan Koreksi / Petunjuk Revisi <span class="text-danger">*</span></label>
                        <textarea class="form-control border-warning" id="revisiCatatan" name="catatan_verifikator" rows="3" 
                                  placeholder="Tuliskan alasan pengembalian dan bagian yang perlu diperbaiki (contoh: Lampirkan bukti survey harga pembanding yang sah minimal dari 2 penyedia berbeda)..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-success btn-sm" id="btnWaFromModal">
                        <i class="bi bi-whatsapp me-1"></i>Draf Notif WA
                    </button>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning fw-semibold"><i class="bi bi-arrow-repeat me-1"></i> Kembalikan Usulan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Verifikasi: Tolak -->
<div class="modal fade" id="modalVerifTolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2"></i>Tolak Usulan <?= $tipe ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTolakUsulan" method="post" action="">
                <?= csrf_input() ?>
                <input type="hidden" name="aksi" value="tolak">
                <div class="modal-body">
                    <p class="mb-2">Usulan <strong id="tolakKode"></strong> (<span id="tolakUraian"></span>) akan <strong>Ditolak</strong>.</p>
                    
                    <div class="mb-3">
                        <label for="tolakCatatan" class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control border-danger" id="tolakCatatan" name="catatan_verifikator" rows="4" 
                                  placeholder="Tuliskan alasan penolakan secara jelas (contoh: Tidak sesuai tupoksi/kebutuhan daerah, usulan duplikat, barang tidak memenuhi kriteria standarisasi)..." required></textarea>
                        <div class="form-text text-danger">Alasan penolakan ini akan dicatat dan dapat dilihat oleh SKPD pengusul.</div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-success btn-sm" id="btnWaFromModalTolak">
                        <i class="bi bi-whatsapp me-1"></i>Draf Notif WA
                    </button>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-semibold"><i class="bi bi-x-circle me-1"></i> Konfirmasi Tolak</button>
                    </div>
                </div>
            </form>
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

<!-- Modal Pratinjau Bukti Dukung (Survey / Brosur) -->
<?php $this->load->view('templates/preview_modal'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handler Modal Preview Surat PDF
    const btnPreviewSurat = document.querySelectorAll('.btn-preview-surat-pdf');
    const modalEl = document.getElementById('modalPreviewSuratPdf');
    const iframeEl = document.getElementById('iframeSuratPdf');
    const subEl = document.getElementById('previewSuratPdfSub');
    const btnDl = document.getElementById('btnDownloadSuratModal');
    const btnTab = document.getElementById('btnOpenNewTabSuratModal');

    btnPreviewSurat.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');
            const dl = this.getAttribute('data-download');
            const nomor = this.getAttribute('data-nomor');
            const nama = this.getAttribute('data-nama');

            subEl.textContent = 'Nomor: ' + nomor + ' (' + nama + ')';
            iframeEl.src = url;
            btnDl.href = dl;
            btnTab.href = url;

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            }
        });
    });
});
</script>
