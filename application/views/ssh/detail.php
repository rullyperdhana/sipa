<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Detail Usulan <?= e($item->kode_usulan) ?></h1>
        <p class="page-subtitle text-muted mb-0">Informasi spesifikasi lengkap dan jejak audit (audit trail) status proses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Lembar Usulan
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-card-checklist me-2 text-primary"></i>Rincian Usulan Item</h6>
                <?= badge_status($item->status_proses) ?>
            </div>
            <div class="card-body p-4">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <th width="35%" class="text-muted fw-normal">Kode Usulan</th>
                            <td class="fw-bold font-monospace text-dark"><?= e($item->kode_usulan) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Tipe & Kategori</th>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= e($item->tipe) ?></span>
                                <span class="text-dark fw-semibold ms-2"><?= e($item->kategori) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">SKPD Pengusul</th>
                            <td class="fw-semibold text-dark"><?= e($item->nama_skpd) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Tahun Anggaran</th>
                            <td class="fw-semibold"><?= (int)$item->tahun_anggaran ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Uraian Barang / Jasa</th>
                            <td class="fs-6 fw-bold text-dark"><?= e($item->uraian) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal align-top">Spesifikasi Teknis</th>
                            <td class="bg-light p-3 rounded text-secondary" style="white-space: pre-line;"><?= e($item->spesifikasi) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Satuan</th>
                            <td><span class="badge bg-light text-dark border"><?= e($item->satuan) ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Harga Usulan</th>
                            <td class="fs-5 fw-bold text-primary"><?= rupiah($item->harga_usulan) ?></td>
                        </tr>
                        <?php if ($item->harga_ditetapkan): ?>
                        <tr class="table-success rounded">
                            <th class="text-success fw-bold">Harga Ditetapkan</th>
                            <td class="fs-5 fw-bold text-success"><?= rupiah($item->harga_ditetapkan) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th class="text-muted fw-normal">Dokumen Lampiran</th>
                            <td>
                                <?php if (!empty($item->file_lampiran)): ?>
                                <a href="<?= site_url('ssh/download/' . $item->id) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-paperclip me-1"></i> <?= e($item->file_nama_asli ?: $item->file_lampiran) ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted small">Tidak ada lampiran dokumen.</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($item->catatan_verifikator)): ?>
                        <tr>
                            <th class="text-muted fw-normal align-top">Catatan Verifikator</th>
                            <td>
                                <div class="alert alert-warning py-2 px-3 mb-0 small">
                                    <i class="bi bi-chat-left-dots-fill me-1"></i> <?= nl2br(e($item->catatan_verifikator)) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Timeline Audit Trail -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Alur Proses (Audit Trail)</h6>
            </div>
            <div class="card-body p-4">
                <?php if (empty($logs)): ?>
                <p class="text-muted small">Belum ada riwayat aktivitas yang tercatat.</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($logs as $l): ?>
                    <div class="mb-3 ps-3 border-start border-3 border-primary position-relative">
                        <div class="d-flex justify-content-between">
                            <strong class="text-dark small"><?= e($l->status_sesudah) ?></strong>
                            <small class="text-muted"><?= tanggal_id($l->created_at, TRUE) ?> <?= substr($l->created_at, 11, 5) ?></small>
                        </div>
                        <div class="small text-muted mb-1">
                            <i class="bi bi-person me-1"></i><?= e($l->nama_lengkap ?: $l->username ?: $l->role) ?> (<?= e(ucfirst($l->role)) ?>)
                        </div>
                        <?php if (!empty($l->catatan)): ?>
                        <div class="bg-light p-2 rounded small text-secondary">
                            <?= e($l->catatan) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
