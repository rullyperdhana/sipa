<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';

$canModify = in_array($surat->status_proses, ['Draft', 'Direvisi'], TRUE) 
    && (in_array($this->currentUser->role, ['operator_skpd', 'skpd', 'admin'], TRUE));

$isLocked = !in_array($surat->status_proses, ['Draft', 'Direvisi'], TRUE);
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url("{$prefixUrl}/usulan") ?>">Usulan <?= $tipe ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($surat->nomor_surat) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="page-title mb-0">
                <i class="bi <?= $isSbu ? 'bi-file-earmark-ruled text-info' : 'bi-file-earmark-text-fill text-primary' ?> me-2"></i>
                Surat Usulan: <span class="font-monospace text-dark"><?= e($surat->nomor_surat) ?></span>
            </h1>
            <?= badge_status($surat->status_proses) ?>
        </div>
        <p class="page-subtitle text-muted mb-0 mt-1">
            SKPD: <strong><?= e($surat->nama_skpd) ?></strong> &bull; Tanggal Surat: <strong><?= tanggal_id($surat->tanggal_surat) ?></strong> &bull; TA <?= (int)$surat->tahun_anggaran ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <?php if (!empty($items) && in_array($this->currentUser->role, ['admin', 'verifikator', 'pimpinan'], TRUE)): ?>
        <button type="button" class="btn btn-outline-success btn-kirim-wa-ssh" 
                data-id="<?= $items[0]->id ?>" 
                data-prefix="<?= $prefixUrl ?>" 
                data-status="<?= $surat->status_proses ?>"
                title="Hubungi Operator SKPD via WhatsApp">
            <i class="bi bi-whatsapp me-1"></i> Hubungi SKPD via WA
        </button>
        <?php endif; ?>

        <a href="<?= site_url("{$prefixUrl}/usulan") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>

        <?php if ($canModify): ?>
            <a href="<?= site_url("{$prefixUrl}/surat/edit/{$surat->id}") ?>" class="btn btn-outline-primary" title="Ubah informasi nomor atau lampiran surat">
                <i class="bi bi-pencil-square me-1"></i> Edit Surat
            </a>

            <?php if ($isJadwalBuka || in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE)): ?>
            <a href="<?= site_url("{$prefixUrl}/tambah/{$surat->id}") ?>" class="btn <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Rincian Item
            </a>
            <?php endif; ?>

            <?php if (!empty($items)): ?>
            <a href="<?= site_url("{$prefixUrl}/surat/kirim/{$surat->id}") ?>" 
               class="btn btn-success shadow-sm btn-confirm-kirim-surat"
               data-nomor="<?= e($surat->nomor_surat) ?>"
               data-total="<?= count($items) ?>"
               title="Kirim surat usulan beserta seluruh rincian item ke BKAD">
                <i class="bi bi-send-fill me-1"></i> <?= $surat->status_proses === 'Draft' ? 'Kirim ke BKAD' : 'Kirim Ulang ke BKAD' ?>
            </a>
            <?php endif; ?>

            <?php if ($surat->status_proses === 'Draft'): ?>
            <a href="<?= site_url("{$prefixUrl}/surat/hapus/{$surat->id}") ?>" 
               class="btn btn-outline-danger btn-confirm-hapus-surat" 
               data-nomor="<?= e($surat->nomor_surat) ?>" 
               title="Hapus berkas surat usulan ini">
                <i class="bi bi-trash"></i>
            </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Card Informasi Surat Usulan -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-3 p-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                        <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-secondary font-monospace"><?= $tipe ?> TA <?= (int)$surat->tahun_anggaran ?></span>
                            <span class="text-muted small">&bull; Dibuat pada <?= tanggal_id($surat->created_at) ?> oleh <strong><?= e($surat->nama_pengusul ?? 'Operator SKPD') ?></strong></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= e($surat->perihal) ?></h5>
                        <?php if (!empty($surat->keterangan)): ?>
                        <p class="text-secondary small mb-2 fst-italic">"<?= e($surat->keterangan) ?>"</p>
                        <?php endif; ?>

                        <!-- Tombol Pratinjau & Unduh Surat Resmi PDF -->
                        <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top">
                            <span class="small text-muted fw-semibold me-1"><i class="bi bi-paperclip me-1"></i>Berkas Surat Resmi:</span>
                            <?php if (!empty($surat->file_surat)): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-preview-surat-pdf" 
                                    data-url="<?= site_url("{$prefixUrl}/surat/preview/{$surat->id}") ?>"
                                    data-download="<?= site_url("{$prefixUrl}/surat/download/{$surat->id}") ?>"
                                    data-nomor="<?= e($surat->nomor_surat) ?>"
                                    data-nama="<?= e($surat->file_nama_asli ?: $surat->file_surat) ?>">
                                <i class="bi bi-eye-fill me-1"></i> Pratinjau Surat PDF
                            </button>
                            <a href="<?= site_url("{$prefixUrl}/surat/download/{$surat->id}") ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i> Unduh Berkas
                            </a>
                            <span class="small text-muted font-monospace d-none d-md-inline ms-1">(<?= e($surat->file_nama_asli ?: $surat->file_surat) ?>)</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Belum ada file surat diunggah</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Statistik Cepat Surat Ini -->
            <div class="col-lg-4 border-start-lg">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <small class="text-muted d-block fw-semibold text-uppercase" style="font-size: 11px;">Jumlah Rincian Item</small>
                            <h3 class="mb-0 mt-1 fw-bold <?= $isSbu ? 'text-info' : 'text-primary' ?>"><?= count($items) ?></h3>
                            <small class="text-muted" style="font-size: 11px;">Item diusulkan</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <small class="text-muted d-block fw-semibold text-uppercase" style="font-size: 11px;">Total Nilai Usulan</small>
                            <h4 class="mb-0 mt-1 fw-bold text-dark text-truncate" style="font-size: 16px;">
                                <?= rupiah($surat->total_nominal ?? 0) ?>
                            </h4>
                            <small class="text-muted" style="font-size: 11px;">Akumulasi harga</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($surat->status_proses === 'Direvisi' && !empty($surat->catatan_verifikator)): ?>
        <!-- Catatan Revisi BKAD -->
        <div class="alert alert-warning border-warning shadow-sm mt-3 mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
            <div>
                <strong>Catatan Koreksi dari Verifikator BKAD:</strong>
                <div><?= e($surat->catatan_verifikator) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($surat->status_proses === 'Ditolak' && !empty($surat->catatan_verifikator)): ?>
        <!-- Alasan Penolakan BKAD -->
        <div class="alert alert-danger border-danger shadow-sm mt-3 mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-x-circle-fill fs-5 text-danger flex-shrink-0"></i>
            <div>
                <strong>Alasan Penolakan Usulan:</strong>
                <div><?= e($surat->catatan_verifikator) ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Section Tabel Rincian Item Usulan -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div>
            <h6 class="card-title mb-0 fw-bold">
                <i class="bi bi-list-check me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
                Rincian Usulan Item <?= $tipe ?>
            </h6>
            <small class="text-muted">Item-item standar harga yang diajukan di dalam Surat No. <?= e($surat->nomor_surat) ?></small>
        </div>
        <div>
            <?php if ($canModify): ?>
                <?php if ($isJadwalBuka || in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE)): ?>
                <a href="<?= site_url("{$prefixUrl}/tambah/{$surat->id}") ?>" class="btn btn-sm <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Rincian Item
                </a>
                <?php else: ?>
                <span class="badge bg-warning text-dark"><i class="bi bi-lock-fill me-1"></i>Jadwal Pengusulan Ditutup</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Kode Usulan</th>
                        <th>Kategori</th>
                        <th><?= $isSbu ? 'Uraian Biaya & Ketentuan' : 'Uraian Barang & Spesifikasi' ?></th>
                        <th class="text-center">Satuan</th>
                        <th class="text-end">Harga Usulan</th>
                        <th class="text-center">3 Bukti Survey</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <span class="fw-semibold">Belum ada rincian item dalam Surat Usulan ini.</span><br>
                            <small class="text-muted">Klik tombol <strong>+ Tambah Rincian Item</strong> di atas untuk mulai menginput data barang/biaya beserta bukti survey.</small>
                            <?php if ($canModify): ?>
                            <div class="mt-3">
                                <a href="<?= site_url("{$prefixUrl}/tambah/{$surat->id}") ?>" class="btn btn-sm <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?>">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Rincian Item Sekarang
                                </a>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: foreach ($items as $idx => $row): ?>
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
                                    <i class="bi bi-link-45deg me-1"></i>Acuan Master 2027 (Dasar: <?= rupiah($row->harga_acuan_master) ?>)
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($row->kode_rekening)): ?>
                            <div class="text-muted small mt-0.5" style="font-size: 11px;">
                                <i class="bi bi-journal-text me-1 text-secondary"></i><span class="font-monospace"><?= e($row->kode_rekening) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($row->status_proses === 'Direvisi' && !empty($row->catatan_verifikator)): ?>
                            <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small border-warning d-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0"></i>
                                <span><strong>Koreksi:</strong> <?= e($row->catatan_verifikator) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($row->status_proses === 'Ditolak' && !empty($row->catatan_verifikator)): ?>
                            <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small border-danger d-flex align-items-center gap-1">
                                <i class="bi bi-x-circle-fill text-danger flex-shrink-0"></i>
                                <span><strong>Alasan Tolak:</strong> <?= e($row->catatan_verifikator) ?></span>
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
                            <?= badge_status($row->status_proses) ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if ($canModify): ?>
                                <a href="<?= site_url("{$prefixUrl}/edit/{$row->id}") ?>" class="btn btn-outline-primary" title="Edit rincian item">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <?php if ($row->status_proses === 'Draft'): ?>
                                <a href="<?= site_url("{$prefixUrl}/hapus/{$row->id}") ?>" class="btn btn-outline-danger btn-hapus-usulan" 
                                   data-confirm="Hapus usulan <?= e($row->kode_usulan) ?>?" title="Hapus item">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                                <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" data-prefix="<?= $prefixUrl ?>" title="Lihat detail item">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <?php endif; ?>
                                <?php if (in_array($this->currentUser->role, ['admin', 'verifikator', 'pimpinan'], TRUE)): ?>
                                <button type="button" class="btn btn-outline-success btn-kirim-wa-ssh" 
                                        data-id="<?= $row->id ?>" 
                                        data-prefix="<?= $prefixUrl ?>" 
                                        data-status="<?= $row->status_proses ?>"
                                        title="Kirim notifikasi WA ke SKPD">
                                    <i class="bi bi-whatsapp"></i>
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

<!-- Modal Detail & History Audit Trail (Global Item Viewer) -->
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

    // Handler Konfirmasi Kirim Surat Usulan
    const btnKirim = document.querySelector('.btn-confirm-kirim-surat');
    if (btnKirim) {
        btnKirim.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            const nomor = this.getAttribute('data-nomor');
            const total = this.getAttribute('data-total');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Kirim Usulan ke BKAD?',
                    html: 'Surat Usulan No. <strong>' + nomor + '</strong> beserta <strong>' + total + ' rincian item</strong> akan diajukan ke BKAD untuk diverifikasi.<br><br><small class="text-muted">Setelah dikirim, data akan terkunci dari pengeditan selama proses telaah.</small>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ya, Kirim Sekarang',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            } else {
                if (confirm('Kirim Surat Usulan ' + nomor + ' beserta ' + total + ' rincian item ke BKAD?')) {
                    window.location.href = href;
                }
            }
        });
    }

    // Handler Konfirmasi Hapus Surat Usulan
    const btnHapus = document.querySelector('.btn-confirm-hapus-surat');
    if (btnHapus) {
        btnHapus.addEventListener('click', function (e) {
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
    }
});
</script>
