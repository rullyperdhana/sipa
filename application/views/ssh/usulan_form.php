<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$isEdit = !empty($item);
$pageTitle = $isEdit ? 'Edit Usulan SSH & SBU' : 'Tambah Usulan SSH & SBU';
$actionUrl = $isEdit ? site_url('ssh/edit/' . $item->id) : site_url('ssh/tambah');
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-pencil-square text-primary me-2"></i><?= $pageTitle ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            <?= $isEdit ? 'Perbaiki rincian item usulan ' . e($item->kode_usulan) : 'Input rincian item Standar Satuan Harga atau Standar Biaya Umum baru untuk SKPD Anda.' ?>
        </p>
    </div>
    <div>
        <a href="<?= site_url('ssh/usulan') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<?php if ($isEdit && $item->status_proses === 'Direvisi' && !empty($item->catatan_verifikator)): ?>
<div class="alert alert-warning border-warning shadow-sm mb-4">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0 mt-1"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">Catatan Koreksi dari Verifikator BPKAD:</h6>
            <p class="mb-0"><?= nl2br(e($item->catatan_verifikator)) ?></p>
            <small class="text-muted mt-1 d-block">Silakan perbaiki data di bawah ini, simpan, lalu klik tombol <strong>Kirim Ulang</strong> pada halaman daftar usulan.</small>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark">
                    <i class="bi bi-ui-checks-grid me-2 text-primary"></i>Formulir Data Usulan
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data" id="formSshUsulan" novalidate>
                    <?= csrf_input() ?>

                    <div class="row g-3 mb-3">
                        <!-- Tipe Standar -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe Standar <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipeSSH" value="SSH" 
                                           <?= ($isEdit ? $item->tipe : 'SSH') === 'SSH' ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="tipeSSH">
                                        <strong>SSH</strong> (Standar Satuan Harga Barang/Jasa)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipeSBU" value="SBU" 
                                           <?= ($isEdit ? $item->tipe : '') === 'SBU' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tipeSBU">
                                        <strong>SBU</strong> (Standar Biaya Umum / Honor / Sewa)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Tahun Anggaran -->
                        <div class="col-md-6">
                            <label for="tahun_anggaran" class="form-label fw-semibold">Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tahun_anggaran" name="tahun_anggaran" 
                                   value="<?= $isEdit ? $item->tahun_anggaran : date('Y') ?>" min="2020" max="2099" required>
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-3">
                        <label for="kategori" class="form-label fw-semibold">Kategori Item <span class="text-danger">*</span></label>
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori as $kat): ?>
                            <option value="<?= e($kat) ?>" <?= ($isEdit && $item->kategori === $kat) ? 'selected' : '' ?>>
                                <?= e($kat) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Pilih salah satu kategori item.</div>
                    </div>

                    <!-- Uraian Item -->
                    <div class="mb-3">
                        <label for="uraian" class="form-label fw-semibold">Uraian / Nama Item <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="uraian" name="uraian" 
                               value="<?= $isEdit ? e($item->uraian) : '' ?>" 
                               placeholder="Contoh: Kertas HVS A4 80gr, Laptop Pengadaan ASN, Honorarium Narasumber..." required minlength="3">
                        <div class="form-text">Tuliskan nama barang atau jasa secara jelas dan terstandarisasi.</div>
                        <div class="invalid-feedback">Uraian wajib diisi minimal 3 karakter.</div>
                    </div>

                    <!-- Spesifikasi -->
                    <div class="mb-3">
                        <label for="spesifikasi" class="form-label fw-semibold">Spesifikasi Teknis / Keterangan Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="spesifikasi" name="spesifikasi" rows="4" 
                                  placeholder="Rincian merek, tipe, kapasitas, bahan, ukuran, sertifikasi, kualifikasi, atau spesifikasi teknis lainnya..." required><?= $isEdit ? e($item->spesifikasi) : '' ?></textarea>
                        <div class="form-text">Spesifikasi yang rinci mempercepat persetujuan oleh tim verifikator BPKAD.</div>
                        <div class="invalid-feedback">Spesifikasi teknis wajib diisi.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Satuan -->
                        <div class="col-md-6">
                            <label for="satuan" class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <input list="listSatuan" class="form-control" id="satuan" name="satuan" 
                                   value="<?= $isEdit ? e($item->satuan) : 'Unit' ?>" placeholder="Pilih atau ketik satuan..." required>
                            <datalist id="listSatuan">
                                <?php foreach ($satuan as $sat): ?>
                                <option value="<?= e($sat) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="invalid-feedback">Satuan wajib diisi.</div>
                        </div>

                        <!-- Harga Usulan -->
                        <div class="col-md-6">
                            <label for="harga_usulan" class="form-label fw-semibold">Harga Usulan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                                <input type="text" class="form-control form-control-lg fw-bold text-primary" id="harga_usulan" name="harga_usulan" 
                                       value="<?= $isEdit ? number_format($item->harga_usulan, 0, ',', '.') : '' ?>" 
                                       placeholder="0" required data-format-rupiah>
                            </div>
                            <div class="form-text text-muted" id="terbilang_helper">Masukkan nilai nominal harga (tanpa sen).</div>
                            <div class="invalid-feedback" id="hargaFeedback">Harga usulan harus diisi dan lebih besar dari Rp 0 (tidak boleh minus).</div>
                        </div>
                    </div>

                    <!-- Upload File Pendukung -->
                    <div class="mb-4">
                        <label for="file_lampiran" class="form-label fw-semibold">
                            Upload Bukti Pendukung / Survey Harga 
                            <small class="text-muted fw-normal">(Opsional / Dianjurkan)</small>
                        </label>
                        <input class="form-control" type="file" id="file_lampiran" name="file_lampiran" 
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xlsx,.xls">
                        <div class="form-text">
                            Format yang didukung: <strong>PDF, JPG, PNG, DOCX, XLSX</strong> (Maksimal 5MB).<br>
                            Lampirkan hasil survey harga pasar, brosur resmi distributor, atau screenshot e-katalog LKPP.
                        </div>

                        <?php if ($isEdit && !empty($item->file_lampiran)): ?>
                        <div class="mt-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                            <div class="small">
                                <i class="bi bi-paperclip text-primary me-1"></i>
                                <strong>File Saat Ini:</strong> <?= e($item->file_nama_asli ?: $item->file_lampiran) ?>
                            </div>
                            <a href="<?= site_url('ssh/download/' . $item->id) ?>" class="btn btn-sm btn-outline-primary py-0">
                                <i class="bi bi-download"></i> Unduh
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= site_url('ssh/usulan') ?>" class="btn btn-light">Batal</a>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btnSimpanUsulan">
                                <i class="bi bi-floppy-fill me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan sebagai Draft' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel Informasi Pedoman -->
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i>Panduan Pengusulan</h6>
                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-2">
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span><strong>SSH</strong> digunakan untuk standar harga barang fisik, perlengkapan kantor, material bangunan, dan mesin.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span><strong>SBU</strong> digunakan untuk honorarium panitia/narasumber, konsultan perorangan, sewa gedung, dan jasa lainnya.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span>Data baru akan disimpan dengan status <strong>Draft</strong>. Anda dapat memeriksanya kembali sebelum menekan tombol <strong>Kirim Usulan</strong>.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-shield-lock-fill text-primary flex-shrink-0 mt-1"></i>
                        <span><strong>Kebijakan RLS:</strong> SKPD hanya dapat mengedit usulan saat berstatus <em>Draft</em> atau <em>Direvisi</em>. Setelah dikirim (status <em>Diajukan</em>), data akan terkunci dari pengeditan.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
