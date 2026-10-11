<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';
$isEdit = ($mode ?? 'tambah') === 'edit';
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url("{$prefixUrl}/usulan") ?>">Usulan <?= $tipe ?></a></li>
                <?php if ($isEdit): ?>
                <li class="breadcrumb-item"><a href="<?= site_url("{$prefixUrl}/surat/detail/{$surat->id}") ?>"><?= e($surat->nomor_surat) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Surat</li>
                <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page">Buat Surat Usulan Baru</li>
                <?php endif; ?>
            </ol>
        </nav>
        <h1 class="page-title">
            <i class="bi <?= $isSbu ? 'bi-envelope-paper-heart text-info' : 'bi-file-earmark-pdf-fill text-primary' ?> me-2"></i>
            <?= $isEdit ? 'Edit Surat Usulan ' . $tipe : 'Buat Surat Usulan ' . $tipe . ' Baru' ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            <?= $isEdit 
                ? 'Perbarui informasi nomor, tanggal, perihal, atau lampiran berkas surat usulan resmi.' 
                : 'Tahap 1: Unggah Surat Usulan resmi dari SKPD sebelum menginput rincian item standar harga.' ?>
        </p>
    </div>
    <div>
        <a href="<?= $isEdit ? site_url("{$prefixUrl}/surat/detail/{$surat->id}") : site_url("{$prefixUrl}/usulan") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Alert Penjelasan Alur -->
<div class="alert alert-primary border-primary shadow-sm d-flex align-items-start gap-3 mb-4">
    <div class="fs-4 text-primary flex-shrink-0 mt-1">
        <i class="bi bi-info-circle-fill"></i>
    </div>
    <div>
        <h6 class="fw-bold mb-1">Alur Pengusulan <?= $modTitle ?>:</h6>
        <ol class="mb-0 ps-3 text-secondary small">
            <li><strong>Langkah 1:</strong> SKPD membuat berkas dan mengunggah <strong>Surat Usulan Resmi</strong> (Format PDF bertanda tangan/stempel).</li>
            <li><strong>Langkah 2:</strong> Setelah Surat Usulan tersimpan, Anda dapat menambahkan <strong>satu atau banyak rincian item <?= $tipe ?></strong> beserta 3 bukti survey harga pasar ke dalam surat tersebut.</li>
            <li><strong>Langkah 3:</strong> Periksa seluruh rincian usulan, lalu klik <strong>Kirim ke BPKAD</strong> untuk diverifikasi.</li>
        </ol>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-pencil-square me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
                    Formulir Berkas Surat Usulan Resmi
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="post" action="<?= $isEdit ? site_url("{$prefixUrl}/surat/edit/{$surat->id}") : site_url("{$prefixUrl}/surat/tambah") ?>" enctype="multipart/form-data">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="tipe" value="<?= $tipe ?>">

                    <?php if ($this->currentUser->role === 'admin' && !empty($skpdList)): ?>
                    <!-- Khusus Admin: Pilih SKPD Pengusul -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">SKPD Pengusul <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_skpd" required>
                            <option value="">-- Pilih SKPD Pengusul --</option>
                            <?php foreach ($skpdList as $s): ?>
                            <option value="<?= $s->id ?>" <?= ($isEdit ? ($surat->id_skpd == $s->id) : (set_value('id_skpd') == $s->id)) ? 'selected' : '' ?>>
                                <?= e($s->nama_skpd) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Nomor Surat Usulan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-hash"></i></span>
                                <input type="text" name="nomor_surat" class="form-control" 
                                       placeholder="Contoh: 027/152/DISDIK/2026" 
                                       value="<?= e($isEdit ? ($surat->nomor_surat ?? '') : set_value('nomor_surat')) ?>" required>
                            </div>
                            <div class="form-text small">Nomor surat dinas resmi dari SKPD pengusul.</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Tanggal Surat <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="tanggal_surat" class="form-control" 
                                       value="<?= e($isEdit ? ($surat->tanggal_surat ?? date('Y-m-d')) : (set_value('tanggal_surat') ?: date('Y-m-d'))) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Perihal Surat <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-card-heading"></i></span>
                            <input type="text" name="perihal" class="form-control" 
                                   placeholder="Contoh: Usulan Standar Satuan Harga (SSH) Tahun Anggaran 2027" 
                                   value="<?= e($isEdit ? ($surat->perihal ?? '') : (set_value('perihal') ?: "Usulan {$modTitle} Tahun Anggaran {$thAktif}")) ?>" required>
                        </div>
                    </div>

                    <!-- Upload Berkas Surat Usulan PDF -->
                    <div class="mb-3 p-3 rounded-3 bg-light border">
                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                            <span>
                                <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                Berkas Surat Usulan (Format PDF Resmi) <?= $isEdit ? '<span class="text-muted fw-normal">(Opsional jika tidak diganti)</span>' : '<span class="text-danger">*</span>' ?>
                            </span>
                            <span class="badge bg-secondary">Maks. 5 MB</span>
                        </label>
                        <input type="file" name="file_surat" class="form-control" accept=".pdf,application/pdf" <?= $isEdit ? '' : 'required' ?>>
                        <div class="form-text small text-muted">
                            Unggah pindaian (scan) surat pengantar/usulan resmi yang telah ditandatangani pimpinan SKPD dan berstempel. Wajib berformat PDF.
                        </div>

                        <?php if ($isEdit && !empty($surat->file_surat)): ?>
                        <div class="mt-2 p-2 bg-white rounded border d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <i class="bi bi-file-earmark-pdf-fill fs-4 text-danger"></i>
                                <div class="text-truncate">
                                    <div class="fw-semibold small text-truncate"><?= e($surat->file_nama_asli ?: $surat->file_surat) ?></div>
                                    <div class="text-muted" style="font-size: 11px;">Berkas surat saat ini</div>
                                </div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="<?= site_url("{$prefixUrl}/surat/preview/{$surat->id}") ?>" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2 small">
                                    <i class="bi bi-eye me-1"></i> Pratinjau
                                </a>
                                <a href="<?= site_url("{$prefixUrl}/surat/download/{$surat->id}") ?>" class="btn btn-xs btn-outline-secondary py-1 px-2 small">
                                    <i class="bi bi-download me-1"></i> Unduh
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan / Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan khusus atau penjelasan urgensi usulan ini jika diperlukan..."><?= e($isEdit ? ($surat->keterangan ?? '') : set_value('keterangan')) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="<?= $isEdit ? site_url("{$prefixUrl}/surat/detail/{$surat->id}") : site_url("{$prefixUrl}/usulan") ?>" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> px-4 shadow-sm">
                            <i class="bi <?= $isEdit ? 'bi-check2-circle' : 'bi-arrow-right-circle' ?> me-1"></i>
                            <?= $isEdit ? 'Simpan Perubahan Surat' : 'Simpan & Lanjutkan Tambah Rincian Item' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
