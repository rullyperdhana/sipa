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

<?php if (isset($summary)): ?>
<!-- Statistik Antrean Verifikasi -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=Diajukan") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-warning">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Menunggu Verifikasi</small>
                    <h3 class="mb-0 mt-1 fw-bold text-warning"><?= (int)($summary->diajukan ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Perlu ditelaah BPKAD</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=Diverifikasi") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-primary">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Telah Diverifikasi</small>
                    <h3 class="mb-0 mt-1 fw-bold text-primary"><?= (int)($summary->diverifikasi ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Siap ke penetapan</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=Direvisi") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-danger">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Perlu Revisi SKPD</small>
                    <h3 class="mb-0 mt-1 fw-bold text-danger"><?= (int)($summary->direvisi ?? 0) ?></h3>
                    <small class="text-muted" style="font-size:11px;">Dikembalikan</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url("{$prefixUrl}/verifikasi?status=") ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center py-2 h-100 border-start border-4 border-secondary">
                <div class="card-body p-2">
                    <small class="text-muted fw-semibold d-block text-uppercase">Total Seluruh Berkas</small>
                    <h3 class="mb-0 mt-1 fw-bold text-secondary"><?= (int)($summary->total ?? 0) ?></h3>
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
                <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filter['q'] ?? '') ?>" placeholder="Kode usulan, uraian...">
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

<!-- Tabel Antrean Verifikasi -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-list-check me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
            Antrean Usulan <?= $tipe ?> untuk Diverifikasi
        </h6>
        <span class="badge <?= $isSbu ? 'bg-info' : 'bg-primary' ?>"><?= count($list) ?> Usulan</span>
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
                        <th class="text-center">Lampiran</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="220">Aksi Verifikasi</th>
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
                            <strong class="font-monospace text-dark"><?= e($row->kode_usulan) ?></strong>
                            <div class="small text-muted"><?= e($row->kategori) ?></div>
                        </td>
                        <td>
                            <strong class="text-dark d-block"><?= e($row->nama_skpd) ?></strong>
                            <small class="text-muted"><i class="bi bi-person me-1"></i><?= e($row->nama_pengusul ?: 'Operator') ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($row->uraian) ?></div>
                            <div class="small text-muted text-truncate" style="max-width: 300px;" title="<?= e($row->spesifikasi) ?>">
                                <?= e($row->spesifikasi) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= e($row->satuan) ?></span>
                        </td>
                        <td class="text-end fw-bold text-dark">
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
                                        title="Pratinjau Survey 1 (Langsung di Layar): <?= e($row->file_nama_asli ?: 'Berkas 1') ?>" 
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
                                        title="Pratinjau Survey 2 (Langsung di Layar): <?= e($row->file_nama_asli_2 ?: 'Berkas 2') ?>" 
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
                                        title="Pratinjau Survey 3 (Langsung di Layar): <?= e($row->file_nama_asli_3 ?: 'Berkas 3') ?>" 
                                        onclick="if(window.SshModule && window.SshModule.openPreview){ window.SshModule.openPreview(<?= $row->id ?>, '<?= $prefixUrl ?>', 3); }">
                                    <i class="bi bi-eye"></i> S3
                                </button>
                                <?php endif; ?>
                                <?php if (empty($row->file_lampiran) && empty($row->file_lampiran_2) && empty($row->file_lampiran_3)): ?>
                                <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?= badge_status($row->status_proses) ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if ($row->status_proses === 'Diajukan'): ?>
                                <!-- Tombol Setujui -->
                                <button type="button" class="btn btn-success btn-setujui-modal" 
                                        data-id="<?= $row->id ?>" 
                                        data-kode="<?= e($row->kode_usulan) ?>"
                                        data-uraian="<?= e($row->uraian) ?>"
                                        data-harga="<?= (float)$row->harga_usulan ?>"
                                        data-prefix="<?= $prefixUrl ?>"
                                        title="Setujui usulan dan teruskan ke penetapan">
                                    <i class="bi bi-check-lg me-1"></i> Setujui
                                </button>
                                <!-- Tombol Revisi -->
                                <button type="button" class="btn btn-warning text-dark btn-revisi-modal" 
                                        data-id="<?= $row->id ?>" 
                                        data-kode="<?= e($row->kode_usulan) ?>"
                                        data-uraian="<?= e($row->uraian) ?>"
                                        data-prefix="<?= $prefixUrl ?>"
                                        title="Kembalikan usulan ke SKPD dengan catatan perbaikan">
                                    <i class="bi bi-arrow-repeat me-1"></i> Revisi
                                </button>
                                <?php endif; ?>
                                <!-- Tombol WhatsApp -->
                                <button type="button" class="btn btn-outline-success btn-kirim-wa-ssh" 
                                        data-id="<?= $row->id ?>" 
                                        data-prefix="<?= $prefixUrl ?>" 
                                        data-status="<?= $row->status_proses ?>"
                                        title="Kirim Pemberitahuan WhatsApp ke Operator SKPD">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-detail-ssh" data-id="<?= $row->id ?>" data-prefix="<?= $prefixUrl ?>" title="Lihat detail">
                                    <i class="bi bi-eye"></i>
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

<!-- Modal Dialog: Setujui Usulan -->
<div class="modal fade" id="modalSetujui" tabindex="-1" aria-labelledby="modalSetujuiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalSetujuiLabel"><i class="bi bi-check-circle-fill me-2"></i>Setujui Usulan <?= $tipe ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSetujuiUsulan" method="post" action="">
                <?= csrf_input() ?>
                <input type="hidden" name="aksi" value="setujui">
                <div class="modal-body">
                    <p class="mb-2">Anda akan menyetujui usulan <strong id="setujuiKode"></strong> (<span id="setujuiUraian"></span>) untuk diteruskan ke tahap <strong>Penetapan <?= $tipe ?></strong>.</p>
                    
                    <div class="mb-3">
                        <label for="setujuiHarga" class="form-label fw-semibold">Harga yang Disetujui / Ditetapkan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="text" class="form-control form-control-lg fw-bold text-success" id="setujuiHarga" name="harga_ditetapkan" data-format-rupiah required>
                        </div>
                        <div class="form-text">Nilai awal otomatis diisi sama dengan harga usulan. Anda dapat menyesuaikannya bila ada hasil negosiasi/standarisasi.</div>
                    </div>

                    <div class="mb-3">
                        <label for="setujuiCatatan" class="form-label fw-semibold">Catatan Verifikator <small class="text-muted fw-normal">(Opsional)</small></label>
                        <textarea class="form-control" id="setujuiCatatan" name="catatan_verifikator" rows="2" placeholder="Catatan atau dasar penyesuaian harga jika ada..."></textarea>
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

<!-- Modal Dialog: Revisi Usulan -->
<div class="modal fade" id="modalRevisi" tabindex="-1" aria-labelledby="modalRevisiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="modalRevisiLabel"><i class="bi bi-arrow-repeat me-2"></i>Kembalikan Usulan <?= $tipe ?> untuk Revisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRevisiUsulan" method="post" action="">
                <?= csrf_input() ?>
                <input type="hidden" name="aksi" value="revisi">
                <div class="modal-body">
                    <p class="mb-2">Usulan <strong id="revisiKode"></strong> (<span id="revisiUraian"></span>) akan dikembalikan ke SKPD dengan status <strong>Direvisi</strong>.</p>
                    
                    <div class="mb-3">
                        <label for="catatan_verifikator" class="form-label fw-semibold">Catatan Verifikator / Alasan Pengembalian <span class="text-danger">*</span></label>
                        <textarea class="form-control border-warning" id="catatan_verifikator" name="catatan_verifikator" rows="4" 
                                  placeholder="<?= $isSbu ? 'Tuliskan catatan perbaikan (contoh: Lampirkan nota dinas telaahan staf, sesuaikan kualifikasi narasumber, sesuaikan dengan tarif PMK/Perbup)...' : 'Tuliskan catatan perbaikan (contoh: Lampirkan bukti survey harga 3 distributor resmi, lengkapi spesifikasi teknis barang, sesuaikan satuan)...' ?>" required></textarea>
                        <div class="form-text text-danger">Catatan ini akan tampil di dashboard SKPD sebagai panduan perbaikan.</div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-success btn-sm" id="btnWaFromModalRevisi">
                        <i class="bi bi-whatsapp me-1"></i>Draf Notif WA
                    </button>
                    <div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark fw-semibold"><i class="bi bi-send-exclamation me-1"></i> Kirim Catatan Revisi</button>
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
