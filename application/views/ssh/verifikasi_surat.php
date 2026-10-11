<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';

$totalItems = count($items);
$countDiverifikasi = 0;
$countDirevisi = 0;
$countDitolak = 0;
$countDiajukan = 0;
$countDraft = 0;

foreach ($items as $it) {
    if ($it->status_proses === 'Diverifikasi' || $it->status_proses === 'Ditetapkan') $countDiverifikasi++;
    elseif ($it->status_proses === 'Direvisi') $countDirevisi++;
    elseif ($it->status_proses === 'Ditolak') $countDitolak++;
    elseif ($it->status_proses === 'Diajukan') $countDiajukan++;
    elseif ($it->status_proses === 'Draft') $countDraft++;
}

$sudahDiproses = $countDiverifikasi + $countDirevisi + $countDitolak;
$progressPercent = ($totalItems > 0) ? round(($sudahDiproses / $totalItems) * 100) : 0;
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url("{$prefixUrl}/verifikasi") ?>">Verifikasi <?= $tipe ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($surat->nomor_surat) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 class="page-title mb-0">
                <i class="bi <?= $isSbu ? 'bi-check2-circle text-info' : 'bi-patch-check-fill text-primary' ?> me-2"></i>
                Verifikasi Surat Usulan: <span class="font-monospace text-dark"><?= e($surat->nomor_surat) ?></span>
            </h1>
            <?= badge_status($surat->status_proses) ?>
        </div>
        <p class="page-subtitle text-muted mb-0 mt-1">
            SKPD: <strong><?= e($surat->nama_skpd) ?></strong> &bull; Tanggal Surat: <strong><?= tanggal_id($surat->tanggal_surat) ?></strong> &bull; TA <?= (int)$surat->tahun_anggaran ?>
        </p>
    </div>
    <div>
        <a href="<?= site_url("{$prefixUrl}/verifikasi") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Antrean
        </a>
    </div>
</div>

<!-- Header Card: Info Surat Resmi & Progress Verifikasi -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-3 p-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                        <i class="bi bi-file-earmark-pdf-fill fs-2"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-secondary font-monospace"><?= $tipe ?> TA <?= (int)$surat->tahun_anggaran ?></span>
                            <span class="text-muted small">&bull; Pengusul: <strong><?= e($surat->nama_pengusul ?? 'Operator SKPD') ?></strong></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= e($surat->perihal) ?></h5>
                        <?php if (!empty($surat->keterangan)): ?>
                        <p class="text-secondary small mb-2 fst-italic">"<?= e($surat->keterangan) ?>"</p>
                        <?php endif; ?>

                        <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top">
                            <span class="small text-muted fw-semibold me-1"><i class="bi bi-paperclip me-1"></i>Surat Usulan Resmi:</span>
                            <?php if (!empty($surat->file_surat)): ?>
                            <button type="button" class="btn btn-sm btn-danger shadow-sm btn-preview-surat-pdf" 
                                    data-url="<?= site_url("{$prefixUrl}/surat/preview/{$surat->id}") ?>"
                                    data-download="<?= site_url("{$prefixUrl}/surat/download/{$surat->id}") ?>"
                                    data-nomor="<?= e($surat->nomor_surat) ?>"
                                    data-nama="<?= e($surat->file_nama_asli ?: $surat->file_surat) ?>">
                                <i class="bi bi-eye-fill me-1"></i> Periksa Berkas Surat PDF
                            </button>
                            <a href="<?= site_url("{$prefixUrl}/surat/download/{$surat->id}") ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i> Unduh
                            </a>
                            <span class="small text-muted font-monospace d-none d-md-inline ms-1">(<?= e($surat->file_nama_asli ?: $surat->file_surat) ?>)</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Berkas fisik belum diunggah</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Progress Verifikasi -->
            <div class="col-lg-5 border-start-lg">
                <div class="p-3 rounded-3 bg-light border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-uppercase text-muted">Progress Verifikasi Surat</span>
                        <span class="badge <?= $progressPercent === 100 ? 'bg-success' : 'bg-primary' ?>"><?= $progressPercent ?>% Selesai</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar <?= $progressPercent === 100 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $progressPercent ?>%;"></div>
                    </div>

                    <div class="row g-1 text-center small">
                        <div class="col">
                            <div class="p-1 bg-white rounded border">
                                <span class="d-block text-muted" style="font-size:10px;">MENUNGGU</span>
                                <strong class="text-warning fs-6"><?= $countDiajukan ?></strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-1 bg-white rounded border">
                                <span class="d-block text-muted" style="font-size:10px;">DISETUJUI</span>
                                <strong class="text-primary fs-6"><?= $countDiverifikasi ?></strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-1 bg-white rounded border">
                                <span class="d-block text-muted" style="font-size:10px;">REVISI</span>
                                <strong class="text-warning fs-6"><?= $countDirevisi ?></strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-1 bg-white rounded border">
                                <span class="d-block text-muted" style="font-size:10px;">DITOLAK</span>
                                <strong class="text-danger fs-6"><?= $countDitolak ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Tabel Rincian Item yang Perlu Diverifikasi -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <div>
            <h6 class="card-title mb-0 fw-bold">
                <i class="bi bi-card-checklist me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
                Daftar Rincian Item Usulan untuk Ditelaah (Total <?= $totalItems ?> Item)
            </h6>
            <small class="text-muted">Periksa keabsahan spesifikasi & 3 bukti survey harga pasar pada masing-masing item.</small>
        </div>
        <span class="badge bg-light text-dark border"><?= $sudahDiproses ?> / <?= $totalItems ?> Item Telah Diperiksa</span>
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
                        <th class="text-center" width="220">Keputusan Verifikator</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <span class="fw-semibold">Tidak ada rincian item dalam Surat Usulan ini.</span>
                        </td>
                    </tr>
                    <?php else: foreach ($items as $idx => $row): ?>
                    <tr id="row-ssh-<?= $row->id ?>" class="<?= $row->status_proses === 'Diajukan' ? 'table-warning-subtle' : '' ?>">
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
                                <span><strong>Catatan Koreksi:</strong> <?= e($row->catatan_verifikator) ?></span>
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
                            <?php if ($row->harga_ditetapkan): ?>
                            <div class="small text-success fw-normal">Ditetapkan: <?= rupiah($row->harga_ditetapkan) ?></div>
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
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm <?= $row->status_proses === 'Diajukan' ? 'btn-primary' : 'btn-outline-primary' ?> btn-action-verif"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalFormVerifikasi"
                                        data-id="<?= $row->id ?>"
                                        data-kode="<?= e($row->kode_usulan) ?>"
                                        data-uraian="<?= e($row->uraian) ?>"
                                        data-satuan="<?= e($row->satuan) ?>"
                                        data-hargausulan="<?= (float)$row->harga_usulan ?>"
                                        data-hargataraf="<?= (float)($row->harga_ditetapkan ?: $row->harga_usulan) ?>"
                                        data-status="<?= $row->status_proses ?>">
                                    <i class="bi bi-shield-check me-1"></i>
                                    <?= $row->status_proses === 'Diajukan' ? 'Telaah & Verifikasi' : 'Ubah Putusan' ?>
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

<!-- Modal Form Telaah / Putusan Verifikasi Item -->
<div class="modal fade" id="modalFormVerifikasi" tabindex="-1" aria-labelledby="modalFormVerifikasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold" id="modalFormVerifikasiLabel">
                    <i class="bi bi-patch-check-fill text-primary me-2"></i>Verifikasi Item Usulan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="formProsesVerifikasi" action="">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="redirect_surat" value="1">
                <div class="modal-body p-4">
                    <!-- Info Item -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-monospace fw-bold text-primary" id="vItemKode">-</span>
                            <span class="badge bg-secondary" id="vItemSatuan">-</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" id="vItemUraian">-</h6>
                        <div class="text-muted small">
                            Harga Usulan SKPD: <strong class="text-dark" id="vItemHargaUsulan">-</strong>
                        </div>
                    </div>

                    <!-- Pilihan Putusan Verifikasi -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Keputusan Verifikasi <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="aksi" id="aksiSetujui" value="setujui" checked autocomplete="off">
                                <label class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center" for="aksiSetujui">
                                    <i class="bi bi-check-circle-fill fs-5 mb-1"></i>
                                    <span class="fw-semibold small">Setujui</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="aksi" id="aksiRevisi" value="revisi" autocomplete="off">
                                <label class="btn btn-outline-warning w-100 py-2 d-flex flex-column align-items-center" for="aksiRevisi">
                                    <i class="bi bi-arrow-repeat fs-5 mb-1"></i>
                                    <span class="fw-semibold small">Revisi</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="aksi" id="aksiTolak" value="tolak" autocomplete="off">
                                <label class="btn btn-outline-danger w-100 py-2 d-flex flex-column align-items-center" for="aksiTolak">
                                    <i class="bi bi-x-circle-fill fs-5 mb-1"></i>
                                    <span class="fw-semibold small">Tolak</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Input Harga Ditetapkan (Hanya Muncul Jika Setujui) -->
                    <div class="mb-3" id="wrapperHargaDitetapkan">
                        <label class="form-label fw-semibold">Harga Standar yang Disetujui (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="text" name="harga_ditetapkan" id="inputHargaDitetapkan" class="form-control fw-bold fs-6 mask-rupiah" placeholder="0">
                        </div>
                        <div class="form-text small text-muted">Sesuaikan jika ada penyesuaian dari hasil survei BKAD.</div>
                    </div>

                    <!-- Catatan Verifikator -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold" id="labelCatatanVerifikator">Catatan / Alasan Putusan</label>
                        <textarea name="catatan_verifikator" id="inputCatatanVerifikator" class="form-control" rows="3" placeholder="Masukkan catatan telaah, instruksi perbaikan (bila revisi), atau dasar penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Putusan
                    </button>
                </div>
            </form>
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

    // Handler Form Verifikasi Per-Item
    const formVerif = document.getElementById('formProsesVerifikasi');
    const prefixUrl = '<?= $prefixUrl ?>';
    const wrapperHarga = document.getElementById('wrapperHargaDitetapkan');
    const inputHarga = document.getElementById('inputHargaDitetapkan');
    const labelCatatan = document.getElementById('labelCatatanVerifikator');
    const radioAksi = document.querySelectorAll('input[name="aksi"]');

    function updateAksiUI(val) {
        if (val === 'setujui') {
            wrapperHarga.style.display = 'block';
            inputHarga.setAttribute('required', 'required');
            labelCatatan.innerHTML = 'Catatan Telaah (Opsional)';
        } else if (val === 'revisi') {
            wrapperHarga.style.display = 'none';
            inputHarga.removeAttribute('required');
            labelCatatan.innerHTML = 'Catatan Koreksi / Petunjuk Perbaikan <span class="text-danger">*</span>';
        } else if (val === 'tolak') {
            wrapperHarga.style.display = 'none';
            inputHarga.removeAttribute('required');
            labelCatatan.innerHTML = 'Alasan Penolakan Usulan <span class="text-danger">*</span>';
        }
    }

    radioAksi.forEach(function (r) {
        r.addEventListener('change', function () {
            updateAksiUI(this.value);
        });
    });

    document.querySelectorAll('.btn-action-verif').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const kode = this.getAttribute('data-kode');
            const uraian = this.getAttribute('data-uraian');
            const satuan = this.getAttribute('data-satuan');
            const hargaUsulan = parseFloat(this.getAttribute('data-hargausulan') || 0);
            const hargaTaraf = parseFloat(this.getAttribute('data-hargataraf') || hargaUsulan);
            const status = this.getAttribute('data-status');

            formVerif.action = '<?= site_url("{$prefixUrl}/proses_verifikasi/") ?>' + id;
            document.getElementById('vItemKode').textContent = kode;
            document.getElementById('vItemSatuan').textContent = satuan;
            document.getElementById('vItemUraian').textContent = uraian;
            document.getElementById('vItemHargaUsulan').textContent = 'Rp ' + Math.round(hargaUsulan).toLocaleString('id-ID');

            if (window.SshModule && window.SshModule.helpers && window.SshModule.helpers.formatRupiah) {
                inputHarga.value = window.SshModule.helpers.formatRupiah(hargaTaraf);
            } else {
                inputHarga.value = Math.round(hargaTaraf).toLocaleString('id-ID');
            }

            // Atur default radio berdasarkan status saat ini
            if (status === 'Direvisi') {
                document.getElementById('aksiRevisi').checked = true;
                updateAksiUI('revisi');
            } else if (status === 'Ditolak') {
                document.getElementById('aksiTolak').checked = true;
                updateAksiUI('tolak');
            } else {
                document.getElementById('aksiSetujui').checked = true;
                updateAksiUI('setujui');
            }
        });
    });
});
</script>
