<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? ($item->tipe ?? 'SSH');
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$isEdit = !empty($item);
$modName = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';
$pageTitle = ($isEdit ? 'Edit Usulan ' : 'Tambah Usulan ') . $modName;
$actionUrl = $isEdit ? site_url("{$prefixUrl}/edit/{$item->id}") : site_url("{$prefixUrl}/tambah");
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi <?= $isSbu ? 'bi-receipt-cutoff text-info' : 'bi-box-seam-fill text-primary' ?> me-2"></i><?= $pageTitle ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            <?php if ($isEdit): ?>
                Perbaiki rincian item usulan <strong><?= e($item->kode_usulan) ?></strong>.
            <?php else: ?>
                <?= $isSbu 
                    ? 'Input usulan standar honorarium, jasa tenaga ahli, sewa, dan biaya operasional non-fisik untuk SKPD Anda.' 
                    : 'Input rincian item harga satuan barang atau material fisik baru untuk SKPD Anda.' ?>
            <?php endif; ?>
        </p>
    </div>
    <div>
        <a href="<?= site_url("{$prefixUrl}/usulan") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Usulan <?= $tipe ?>
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
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark">
                    <i class="bi bi-ui-checks-grid me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
                    Formulir Input <?= $isSbu ? 'Biaya / Honorarium / Jasa (SBU)' : 'Barang & Material Fisik (SSH)' ?>
                </h6>
                <span class="badge <?= $isSbu ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> py-2 px-3">
                    Modul: <?= $tipe ?>
                </span>
            </div>
            <div class="card-body p-4">
                <form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data" id="formSshUsulan" novalidate>
                    <?= csrf_input() ?>
                    <!-- Hidden Tipe Otomatis Sesuai Modul -->
                    <input type="hidden" name="tipe" value="<?= $tipe ?>">
                    <input type="hidden" name="master_standar_id" id="master_standar_id" value="<?= $isEdit ? (int)($item->master_standar_id ?? 0) : (!empty($masterItem) ? (int)$masterItem->id : '') ?>">
                    <input type="hidden" name="kode_kelompok" id="kode_kelompok" value="<?= $isEdit ? e($item->kode_kelompok ?? '') : (!empty($masterItem) ? e($masterItem->kode_kelompok) : '') ?>">
                    <input type="hidden" name="harga_acuan_master" id="harga_acuan_master" value="<?= $isEdit ? (float)($item->harga_acuan_master ?? 0) : (!empty($masterItem) ? (float)$masterItem->harga_satuan : '') ?>">

                    <!-- Kotak Pilihan Sumber Usulan (Master Data 2027 vs Item Baru) -->
                    <?php if (!$isEdit): ?>
                    <div class="card bg-light border-0 mb-4 p-3 rounded-3">
                        <div class="fw-bold mb-2 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3-fill text-primary"></i>
                            Sumber Referensi Usulan:
                        </div>
                        <div class="d-flex flex-wrap gap-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_usulan" id="modeMaster" value="master" checked>
                                <label class="form-check-label fw-semibold text-primary" for="modeMaster">
                                    <i class="bi bi-box-arrow-in-down-right me-1"></i>Pilih dari Master Data 2027 (Penyesuaian Harga)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_usulan" id="modeManual" value="manual">
                                <label class="form-check-label fw-semibold text-secondary" for="modeManual">
                                    <i class="bi bi-plus-circle me-1"></i>Input Item Baru (Item belum ada di Master)
                                </label>
                            </div>
                        </div>

                        <!-- Dropdown Autocomplete Master Data 2027 -->
                        <div id="sectionSearchMaster" class="mt-2">
                            <label for="selectMasterStandar" class="form-label small fw-semibold text-dark mb-1">
                                Cari & Pilih Item Katalog Resmi <?= $tipe ?> 2027:
                            </label>
                            <select class="form-select" id="selectMasterStandar" style="width: 100%;">
                                <?php if (!empty($masterItem)): ?>
                                <option value="<?= $masterItem->id ?>" selected>
                                    <?= e("{$masterItem->kode_kelompok} - {$masterItem->uraian} ({$masterItem->spesifikasi}) - Rp " . number_format($masterItem->harga_satuan, 0, ',', '.') . "/{$masterItem->satuan}") ?>
                                </option>
                                <?php else: ?>
                                <option value="">-- Ketik nama barang / jasa / spesifikasi untuk mencari master 2027 --</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text small">Pilih salah satu item untuk mengisi otomatis nama, spesifikasi, satuan, kategori, dan rekening SIPD.</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Banner Status Terhubung ke Master Data -->
                    <div id="cardAcuanMaster" class="alert alert-primary border-primary <?= (!empty($masterItem) || ($isEdit && !empty($item->master_standar_id))) ? '' : 'd-none' ?> mb-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="alert-heading fw-bold mb-1 text-primary">
                                    <i class="bi bi-link-45deg fs-5 me-1"></i>Terkoneksi ke Master Data Standar Harga 2027:
                                </h6>
                                <div class="small">
                                    Kode Kelompok: <strong id="lblKodeKelompok"><?= !empty($masterItem) ? e($masterItem->kode_kelompok) : ($isEdit ? e($item->kode_kelompok ?? '-') : '-') ?></strong> &bull;
                                    Harga Acuan 2027: <strong id="lblHargaAcuan" class="text-primary"><?= !empty($masterItem) ? rupiah($masterItem->harga_satuan) : ($isEdit && !empty($item->harga_acuan_master) ? rupiah($item->harga_acuan_master) : '-') ?></strong>
                                </div>
                                <div class="small text-muted mt-1" id="lblRekeningAcuan">
                                    <?php if (!empty($masterItem) && !empty($masterItem->kode_rekening)): ?>
                                        <i class="bi bi-journal-text me-1"></i>Rekening SIPD: <strong><?= e($masterItem->kode_rekening) ?></strong> &bull; <?= e($masterItem->nama_rekening ?? '') ?>
                                    <?php elseif ($isEdit && !empty($item->kode_rekening)): ?>
                                        <i class="bi bi-journal-text me-1"></i>Rekening SIPD: <strong><?= e($item->kode_rekening) ?></strong> &bull; <?= e($item->nama_rekening ?? '') ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!$isEdit): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnResetMaster" title="Lepas tautan master">
                                <i class="bi bi-x-circle me-1"></i>Lepas Acuan
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Kategori -->
                        <div class="col-md-8">
                            <label for="kategori" class="form-label fw-semibold">
                                Kategori <?= $isSbu ? 'Biaya / Jasa' : 'Barang' ?> <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="kategori" name="kategori" required>
                                <option value="">-- Pilih Kategori <?= $tipe ?> --</option>
                                <?php foreach ($kategori as $kat): ?>
                                <option value="<?= e($kat) ?>" <?= (($isEdit && $item->kategori === $kat) || (!empty($masterItem) && $masterItem->kategori === $kat)) ? 'selected' : '' ?>>
                                    <?= e($kat) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Pilih salah satu kategori item.</div>
                        </div>

                        <!-- Tahun Anggaran -->
                        <div class="col-md-4">
                            <label for="tahun_anggaran" class="form-label fw-semibold">Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tahun_anggaran" name="tahun_anggaran" 
                                   value="<?= $isEdit ? $item->tahun_anggaran : (!empty($jadwalAktif) ? (int)$jadwalAktif->tahun_anggaran : 2027) ?>" min="2020" max="2099" required>
                            <div class="form-text small text-muted">Tahun anggaran pelaksanaan usulan.</div>
                        </div>
                    </div>

                    <!-- Rekening Belanja SIPD RI -->
                    <div class="mb-3">
                        <label for="kode_rekening" class="form-label fw-semibold">
                            Kode Rekening Belanja SIPD RI <small class="text-muted fw-normal">(Otomatis terisi dari master)</small>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-journal-text text-muted"></i></span>
                            <input type="text" class="form-control font-monospace" id="kode_rekening" name="kode_rekening" 
                                   value="<?= $isEdit ? e($item->kode_rekening ?? '') : (!empty($masterItem) ? e($masterItem->kode_rekening) : '') ?>" 
                                   placeholder="Contoh: 5.1.02.01.001.00038">
                            <input type="hidden" id="nama_rekening" name="nama_rekening" 
                                   value="<?= $isEdit ? e($item->nama_rekening ?? '') : (!empty($masterItem) ? e($masterItem->nama_rekening) : '') ?>">
                        </div>
                        <div class="small text-muted mt-1" id="lblNamaRekening">
                            <?= ($isEdit && !empty($item->nama_rekening)) ? e($item->nama_rekening) : (!empty($masterItem) ? e($masterItem->nama_rekening) : '') ?>
                        </div>
                    </div>

                    <!-- Uraian Item -->
                    <div class="mb-3">
                        <label for="uraian" class="form-label fw-semibold">
                            <?= $isSbu ? 'Uraian Biaya / Nama Honorarium / Jasa' : 'Nama Barang / Uraian Item Fisik' ?> 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="uraian" name="uraian" 
                               value="<?= $isEdit ? e($item->uraian) : '' ?>" 
                               placeholder="<?= $isSbu ? 'Contoh: Honorarium Narasumber Pakar/Praktisi, Tenaga Ahli Programmer Senior, Sewa Gedung Pertemuan...' : 'Contoh: Kertas HVS A4 80gr, Laptop Pengadaan Standar ASN, Semen Portland Komposit 50 Kg...' ?>" 
                               required minlength="3">
                        <div class="form-text">
                            <?= $isSbu 
                                ? 'Tuliskan nama pos biaya, kualifikasi tenaga ahli, atau jenis jasa secara terstandarisasi.' 
                                : 'Tuliskan nama barang fisik secara jelas dan terstandarisasi.' ?>
                        </div>
                        <div class="invalid-feedback">Uraian wajib diisi minimal 3 karakter.</div>
                    </div>

                    <!-- Spesifikasi -->
                    <div class="mb-3">
                        <label for="spesifikasi" class="form-label fw-semibold">
                            <?= $isSbu ? 'Ketentuan, Kualifikasi & Dasar Perhitungan Teknis' : 'Spesifikasi Teknis & Merek Lengkap' ?> 
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="spesifikasi" name="spesifikasi" rows="4" 
                                  placeholder="<?= $isSbu ? 'Tingkat pendidikan minimal, sertifikasi keahlian, durasi/jam kerja, kapasitas tempat, dasar regulasi PMK/Perbup terkait...' : 'Rincian merek, tipe, kapasitas, dimensi, daya listrik, bahan, standar SNI, atau detail teknis barang...' ?>" 
                                  required><?= $isEdit ? e($item->spesifikasi) : '' ?></textarea>
                        <div class="form-text">Rincian spesifikasi teknis yang jelas mempermudah dan mempercepat persetujuan verifikator BPKAD.</div>
                        <div class="invalid-feedback">Spesifikasi teknis wajib diisi.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Satuan -->
                        <div class="col-md-6">
                            <label for="satuan" class="form-label fw-semibold">
                                Satuan <?= $isSbu ? '(Waktu / Orang / Kegiatan)' : '(Unit / Fisik)' ?> <span class="text-danger">*</span>
                            </label>
                            <input list="listSatuan" class="form-control" id="satuan" name="satuan" 
                                   value="<?= $isEdit ? e($item->satuan) : ($isSbu ? 'Orang/Bulan (OB)' : 'Unit') ?>" 
                                   placeholder="Pilih atau ketik satuan..." required>
                            <datalist id="listSatuan">
                                <?php foreach ($satuan as $sat): ?>
                                <option value="<?= e($sat) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="invalid-feedback">Satuan wajib diisi.</div>
                        </div>

                        <!-- Harga Usulan -->
                        <div class="col-md-6">
                            <label for="harga_usulan" class="form-label fw-semibold">
                                <?= $isSbu ? 'Besaran Biaya / Tarif Usulan (Rp)' : 'Harga Satuan Usulan (Rp)' ?> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                                <input type="text" class="form-control form-control-lg fw-bold <?= $isSbu ? 'text-info' : 'text-primary' ?>" id="harga_usulan" name="harga_usulan" 
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
                            <?= $isSbu ? 'Upload Bukti / Telaahan Staf / Regulasi Acuan' : 'Upload Bukti Survey Harga Pasar / Brosur Resmi' ?>
                            <small class="text-muted fw-normal">(Opsional / Dianjurkan)</small>
                        </label>
                        <input class="form-control" type="file" id="file_lampiran" name="file_lampiran" 
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xlsx,.xls">
                        <div class="form-text">
                            Format yang didukung: <strong>PDF, JPG, PNG, DOCX, XLSX</strong> (Maksimal 5MB).<br>
                            <?= $isSbu 
                                ? 'Lampirkan nota dinas telaahan staf, SK/regulasi tarif yang berlaku, atau perbandingan standar biaya masukan.' 
                                : 'Lampirkan hasil survey harga pasar minimal 3 toko/distributor, brosur resmi distributor, atau screenshot e-katalog LKPP.' ?>
                        </div>

                        <?php if ($isEdit && !empty($item->file_lampiran)): ?>
                        <div class="mt-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                            <div class="small">
                                <i class="bi bi-paperclip text-primary me-1"></i>
                                <strong>File Saat Ini:</strong> <?= e($item->file_nama_asli ?: $item->file_lampiran) ?>
                            </div>
                            <a href="<?= site_url("{$prefixUrl}/download/{$item->id}") ?>" class="btn btn-sm btn-outline-primary py-0">
                                <i class="bi bi-download"></i> Unduh
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= site_url("{$prefixUrl}/usulan") ?>" class="btn btn-light">Batal</a>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> px-4 fw-semibold" id="btnSimpanUsulan">
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
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-info-circle-fill <?= $isSbu ? 'text-info' : 'text-primary' ?> me-2"></i>
                    Panduan Pengusulan <?= $tipe ?>
                </h6>
                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-2">
                    <?php if ($isSbu): ?>
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span><strong>SBU</strong> menstandarisasi biaya operasional dan belanja non-fisik: honorarium tim/narasumber, konsultan perorangan, uang harian perjadin, sewa gedung, dsb.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span>Satuan umum pada SBU meliputi <strong>OB</strong> (Orang/Bulan), <strong>OH</strong> (Orang/Hari), <strong>OJ</strong> (Orang/Jam), atau per <strong>Kegiatan</strong>.</span>
                    </li>
                    <?php else: ?>
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span><strong>SSH</strong> menstandarisasi harga satuan barang fisik, perlengkapan kantor, alat kesehatan, material bangunan, mesin, dan kendaraan.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span>Satuan umum pada SSH meliputi <strong>Unit</strong>, <strong>Buah</strong>, <strong>Rim</strong>, <strong>Dus</strong>, <strong>Sak</strong>, <strong>Meter</strong>, dsb.</span>
                    </li>
                    <?php endif; ?>
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success flex-shrink-0 mt-1"></i>
                        <span>Data baru akan disimpan dengan status <strong>Draft</strong>. Anda dapat memeriksanya kembali sebelum menekan tombol <strong>Kirim Usulan</strong>.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-shield-lock-fill text-primary flex-shrink-0 mt-1"></i>
                        <span><strong>Kebijakan RLS:</strong> SKPD hanya dapat mengedit usulan saat berstatus <em>Draft</em> atau <em>Direvisi</em>. Setelah dikirim (status <em>Diajukan</em>), data terkunci.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mode switcher (Master vs Manual)
    const modeMaster = document.getElementById('modeMaster');
    const modeManual = document.getElementById('modeManual');
    const secSearch = document.getElementById('sectionSearchMaster');
    const cardAcuan = document.getElementById('cardAcuanMaster');
    const btnReset = document.getElementById('btnResetMaster');

    if (modeMaster && modeManual && secSearch) {
        modeMaster.addEventListener('change', function() {
            if (this.checked) {
                secSearch.classList.remove('d-none');
            }
        });
        modeManual.addEventListener('change', function() {
            if (this.checked) {
                secSearch.classList.add('d-none');
                resetMasterAcuan();
            }
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', function() {
            resetMasterAcuan();
            if ($('#selectMasterStandar').length) {
                $('#selectMasterStandar').val(null).trigger('change');
            }
        });
    }

    function resetMasterAcuan() {
        document.getElementById('master_standar_id').value = '';
        document.getElementById('harga_acuan_master').value = '';
        if (cardAcuan) cardAcuan.classList.add('d-none');
        document.getElementById('lblNamaRekening').textContent = '';
        document.getElementById('kode_rekening').value = '';
        document.getElementById('nama_rekening').value = '';
    }

    // Select2 Autocomplete untuk Master Data 2027
    if (typeof jQuery !== 'undefined' && $('#selectMasterStandar').length) {
        $('#selectMasterStandar').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Ketik nama barang / jasa / spesifikasi untuk mencari katalog 2027 --',
            allowClear: true,
            ajax: {
                url: '<?= site_url("ajax/standar_harga/search") ?>',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        tipe: '<?= $tipe ?>',
                        tahun: 2027,
                        q: params.term || ''
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || []
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        }).on('select2:select', function(e) {
            const data = e.params.data;
            if (!data) return;

            // Isi nilai terhubung
            document.getElementById('master_standar_id').value = data.id;
            document.getElementById('kode_kelompok').value = data.kode_kelompok || '';
            document.getElementById('harga_acuan_master').value = data.harga_satuan || 0;

            document.getElementById('uraian').value = data.uraian || '';
            document.getElementById('spesifikasi').value = data.spesifikasi || '';
            document.getElementById('satuan').value = data.satuan || '';

            // Set kategori jika ada di opsi
            const selectKat = document.getElementById('kategori');
            if (selectKat && data.kategori) {
                for (let i = 0; i < selectKat.options.length; i++) {
                    if (selectKat.options[i].value === data.kategori) {
                        selectKat.selectedIndex = i;
                        break;
                    }
                }
            }

            // Set Rekening Belanja SIPD
            document.getElementById('kode_rekening').value = data.kode_rekening || '';
            document.getElementById('nama_rekening').value = data.nama_rekening || '';
            document.getElementById('lblNamaRekening').textContent = data.nama_rekening ? (data.kode_rekening + ' - ' + data.nama_rekening) : (data.kode_rekening || '');

            // Set Harga Acuan & Default Harga Usulan
            const inputHarga = document.getElementById('harga_usulan');
            if (inputHarga) {
                inputHarga.value = data.harga_satuan_fmt || '0';
            }

            // Tampilkan card info acuan
            if (cardAcuan) {
                cardAcuan.classList.remove('d-none');
                document.getElementById('lblKodeKelompok').textContent = data.kode_kelompok || '-';
                document.getElementById('lblHargaAcuan').textContent = 'Rp ' + (data.harga_satuan_fmt || '0');
                document.getElementById('lblRekeningAcuan').innerHTML = data.kode_rekening 
                    ? '<i class="bi bi-journal-text me-1"></i>Rekening SIPD: <strong>' + data.kode_rekening + '</strong> &bull; ' + (data.nama_rekening || '') 
                    : '';
            }
        });
    }

    // Format Rupiah pada input harga
    const inputHarga = document.getElementById('harga_usulan');
    if (inputHarga) {
        inputHarga.addEventListener('input', function(e) {
            let val = this.value.replace(/[^0-9]/g, '');
            if (val === '') {
                this.value = '';
                return;
            }
            this.value = parseInt(val, 10).toLocaleString('id-ID');
        });
    }
});
</script>

