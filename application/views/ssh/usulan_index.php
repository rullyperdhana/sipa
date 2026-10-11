<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';
$stats = $summarySurat ?? $summary;
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
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
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($isJadwalBuka || in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE)): ?>
        <a href="<?= site_url("{$prefixUrl}/surat/tambah") ?>" class="btn <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> shadow-sm">
            <i class="bi bi-file-earmark-plus-fill me-1"></i> Buat Usulan Baru (Upload Surat)
        </a>
        <?php else: ?>
        <button type="button" class="btn btn-secondary shadow-sm" disabled title="Jadwal pengusulan saat ini ditutup / belum dibuka oleh BPKAD">
            <i class="bi bi-lock-fill me-1"></i> Menunggu Jadwal Dibuka
        </button>
        <?php endif; ?>
        <?php if (in_array($this->currentUser->role, ['admin', 'verifikator'], TRUE)): ?>
        <a href="<?= site_url("{$prefixUrl}/jadwal") ?>" class="btn btn-outline-primary" title="Kelola Jadwal Pengusulan">
            <i class="bi bi-calendar-range me-1"></i> Atur Jadwal
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Banner Status Jadwal Pengusulan -->
<?php if ($isJadwalBuka && !empty($jadwalAktif)): ?>
<div class="alert alert-success border-success shadow-sm d-flex justify-content-between align-items-center mb-4 py-2.5 px-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-broadcast fs-5 text-success"></i>
        <div>
            <strong>Jadwal Pengusulan Aktif:</strong> <?= e($jadwalAktif->nama_jadwal) ?> (TA <?= (int)$jadwalAktif->tahun_anggaran ?>)
            <span class="text-muted small ms-2">&bull; Batas Akhir: <strong><?= date('d M Y', strtotime($jadwalAktif->tanggal_selesai)) ?></strong></span>
        </div>
    </div>
    <span class="badge bg-success py-1.5 px-2.5"><i class="bi bi-unlock-fill me-1"></i>PENGUSULAN DIBUKA</span>
</div>
<?php else: ?>
<div class="alert alert-warning border-warning shadow-sm d-flex justify-content-between align-items-center mb-4 py-2.5 px-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-clock-history fs-5 text-warning"></i>
        <div>
            <strong>Pengusulan Ditutup / Menunggu Jadwal:</strong> Tahapan pengusulan <?= $tipe ?> saat ini belum dibuka atau telah berakhir.
            <div class="text-muted small">Silakan menunggu pembuatan dan pengaktifan jadwal pengusulan resmi oleh BPKAD Kabupaten Tapin.</div>
        </div>
    </div>
    <span class="badge bg-danger py-1.5 px-2.5"><i class="bi bi-lock-fill me-1"></i>MENUNGGU JADWAL</span>
</div>
<?php endif; ?>

<!-- Statistik Ringkasan Surat Usulan -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 <?= $isSbu ? 'border-info' : 'border-primary' ?>">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Total Surat</small>
                <h3 class="mb-0 mt-1 fw-bold <?= $isSbu ? 'text-info' : 'text-primary' ?>"><?= (int)($stats->total ?? 0) ?></h3>
                <small class="text-muted" style="font-size:11px;">Berkas usulan</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-secondary">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Draft</small>
                <h3 class="mb-0 mt-1 fw-bold text-secondary"><?= (int)($stats->draft ?? 0) ?></h3>
                <small class="text-muted" style="font-size:11px;">Belum dikirim</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-info">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Diajukan</small>
                <h3 class="mb-0 mt-1 fw-bold text-info"><?= (int)($stats->diajukan ?? 0) ?></h3>
                <small class="text-muted" style="font-size:11px;">Menunggu telaah</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-warning">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Direvisi</small>
                <h3 class="mb-0 mt-1 fw-bold text-warning"><?= (int)($stats->direvisi ?? 0) ?></h3>
                <small class="text-muted" style="font-size:11px;">Perlu perbaikan</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-danger">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Ditolak</small>
                <h3 class="mb-0 mt-1 fw-bold text-danger"><?= (int)($stats->ditolak ?? 0) ?></h3>
                <small class="text-muted" style="font-size:11px;">Tidak disetujui</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-primary">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Diverifikasi</small>
                <h3 class="mb-0 mt-1 fw-bold text-primary"><?= (int)($stats->diverifikasi ?? 0) ?></h3>
                <small class="text-muted" style="font-size:11px;">Disetujui BPKAD</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-success">
            <div class="card-body p-2">
                <small class="text-muted fw-semibold d-block text-uppercase">Ditetapkan</small>
                <h3 class="mb-0 mt-1 fw-bold text-success"><?= (int)($stats->ditetapkan ?? 0) ?></h3>
                <small class="text-muted" style="font-size:11px;">Standar resmi</small>
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
                    <?php foreach (['Draft', 'Diajukan', 'Direvisi', 'Ditolak', 'Diverifikasi', 'Ditetapkan'] as $st): ?>
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
                <label class="form-label small fw-semibold text-muted mb-1">Cari Kata Kunci</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filter['q'] ?? '') ?>" placeholder="Nomor surat, perihal, uraian...">
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

<!-- Navigasi Tab Tampilan: Berbasis Surat Usulan (Utama) vs Semua Rincian Item (Flat) -->
<ul class="nav nav-tabs border-bottom mb-3" id="usulanTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="tab-surat-btn" data-bs-toggle="tab" data-bs-target="#tab-surat-content" type="button" role="tab" aria-controls="tab-surat-content" aria-selected="true">
            <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
            Daftar Surat Usulan Resmi
            <span class="badge bg-primary ms-1"><?= count($suratList ?? []) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="tab-item-btn" data-bs-toggle="tab" data-bs-target="#tab-item-content" type="button" role="tab" aria-controls="tab-item-content" aria-selected="false">
            <i class="bi bi-list-columns-reverse text-secondary me-2"></i>
            Semua Rincian Item (Flat View)
            <span class="badge bg-secondary ms-1"><?= count($list ?? []) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content" id="usulanTabsContent">
    <!-- TAB 1: DAFTAR SURAT USULAN (WORKFLOW RESMI INDUK) -->
    <div class="tab-pane fade show active" id="tab-surat-content" role="tabpanel" aria-labelledby="tab-surat-btn">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-envelope-paper me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
                    Daftar Surat Usulan <?= $modTitle ?>
                </h6>
                <span class="badge bg-light text-dark border"><?= count($suratList ?? []) ?> Surat Ditemukan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th>Nomor & Tanggal Surat</th>
                                <th>Perihal & Keterangan</th>
                                <th class="text-center">Berkas Surat Resmi</th>
                                <th class="text-center">Rincian Item</th>
                                <th class="text-end">Total Nominal</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="220">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suratList)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary"></i>
                                    <span class="fw-semibold">Belum ada Surat Usulan <?= $tipe ?> yang dibuat.</span><br>
                                    <small class="text-muted">Klik tombol <strong>Buat Usulan Baru (Upload Surat)</strong> di atas untuk memulai pengusulan.</small>
                                </td>
                            </tr>
                            <?php else: foreach ($suratList as $idx => $sRow): ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark font-monospace"><?= e($sRow->nomor_surat) ?></strong>
                                    <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i><?= tanggal_id($sRow->tanggal_surat) ?></div>
                                    <?php if (!empty($sRow->nama_skpd)): ?>
                                    <div class="small text-secondary fw-semibold mt-0.5"><?= e($sRow->nama_skpd) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($sRow->perihal) ?></div>
                                    <?php if (!empty($sRow->keterangan)): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 300px;" title="<?= e($sRow->keterangan) ?>">
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
                                    <?= status_badge($sRow->status_proses) ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Tombol Utama: Buka Detail & Rincian Item -->
                                        <a href="<?= site_url("{$prefixUrl}/surat/detail/{$sRow->id}") ?>" class="btn btn-sm btn-primary" title="Buka dan kelola rincian item dalam surat ini">
                                            <i class="bi bi-folder2-open me-1"></i> Kelola Item
                                        </a>

                                        <?php if (in_array($sRow->status_proses, ['Draft', 'Direvisi'], TRUE)): ?>
                                        <a href="<?= site_url("{$prefixUrl}/surat/edit/{$sRow->id}") ?>" class="btn btn-sm btn-outline-secondary" title="Edit informasi surat">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <?php if ($sRow->status_proses === 'Draft'): ?>
                                        <a href="<?= site_url("{$prefixUrl}/surat/hapus/{$sRow->id}") ?>" class="btn btn-sm btn-outline-danger btn-confirm-hapus-surat" 
                                           data-nomor="<?= e($sRow->nomor_surat) ?>" title="Hapus draft surat">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
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

    <!-- TAB 2: SEMUA RINCIAN ITEM (FLAT LIST VIEW) -->
    <div class="tab-pane fade" id="tab-item-content" role="tabpanel" aria-labelledby="tab-item-btn">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-table me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
                    Daftar Semua Rincian Item <?= $modTitle ?>
                </h6>
                <span class="badge bg-light text-dark border"><?= count($list) ?> Item ditemukan</span>
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
                                    <span class="fw-semibold">Belum ada data rincian item usulan <?= $tipe ?>.</span><br>
                                    <small>Klik tab <strong>Daftar Surat Usulan</strong> lalu buka surat untuk menambahkan rincian item.</small>
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
                                    <?php if (!empty($row->master_standar_id)): ?>
                                    <div class="mt-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 11px;">
                                            <i class="bi bi-link-45deg me-1"></i>Acuan Master 2027 (Harga Dasar: <?= rupiah($row->harga_acuan_master) ?>)
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($row->kode_rekening)): ?>
                                    <div class="text-muted small mt-0.5" style="font-size: 11px;" title="<?= e($row->nama_rekening ?? '') ?>">
                                        <i class="bi bi-journal-text me-1 text-secondary"></i><span class="font-monospace"><?= e($row->kode_rekening) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($row->status_proses === 'Direvisi' && !empty($row->catatan_verifikator)): ?>
                                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small border-warning d-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0"></i>
                                        <span><strong>Catatan Koreksi:</strong> <?= e($row->catatan_verifikator) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($row->status_proses === 'Ditolak' && !empty($row->catatan_verifikator)): ?>
                                    <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small border-danger d-flex align-items-center gap-1">
                                        <i class="bi bi-x-circle-fill text-danger flex-shrink-0"></i>
                                        <span><strong>Alasan Penolakan:</strong> <?= e($row->catatan_verifikator) ?></span>
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
                                    <?= status_badge($row->status_proses) ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if (in_array($row->status_proses, ['Draft', 'Direvisi'], TRUE)): ?>
                                        <a href="<?= site_url("{$prefixUrl}/edit/{$row->id}") ?>" class="btn btn-sm btn-outline-primary" title="Edit usulan">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <?php if ($row->status_proses === 'Draft'): ?>
                                        <a href="<?= site_url("{$prefixUrl}/hapus/{$row->id}") ?>" class="btn btn-sm btn-outline-danger btn-hapus-usulan" 
                                           data-confirm="Hapus usulan <?= e($row->kode_usulan) ?>?" title="Hapus draft">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" data-prefix="<?= $prefixUrl ?>" title="Lihat detail">
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

    // Handler Konfirmasi Hapus Surat Usulan
    const btnHapus = document.querySelectorAll('.btn-confirm-hapus-surat');
    btnHapus.forEach(function(btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            const nomor = this.getAttribute('data-nomor');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Surat Usulan?',
                    html: 'Apakah Anda yakin ingin menghapus Surat Usulan No. <strong>' + nomor + '</strong>?<br><br><span class="text-danger small">Seluruh rincian item usulan di dalam surat ini juga akan ikut terhapus permanen.</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            } else {
                if (confirm('Hapus Surat Usulan ' + nomor + ' beserta seluruh rincian item di dalamnya?')) {
                    window.location.href = href;
                }
            }
        });
    });
});
</script>
