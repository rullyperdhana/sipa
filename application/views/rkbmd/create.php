<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title">
                <i class="bi bi-plus-square"></i>
                Buat Usulan <?= e(label_jenis($jenis)) ?>
            </h1>
            <p class="page-subtitle">Isi data header usulan. Setelah disimpan, anda akan diarahkan ke halaman pengisian item.</p>
        </div>
        <a href="<?= site_url("rkbmd/{$jenis}") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <strong>Data Usulan</strong>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url("rkbmd/{$jenis}/create") ?>">
                    <?= csrf_input() ?>

                    <?php $user = $this->auth->user(); ?>

                    <div class="mb-3">
                        <label class="form-label">SKPD <span class="text-danger">*</span></label>
                        <?php if ($user->role === 'skpd'): ?>
                            <input type="hidden" name="skpd_id" value="<?= (int) $user->skpd_id ?>">
                            <input type="text" class="form-control" value="<?= e($user->nama_skpd ?? '') ?>" disabled>
                            <div class="form-text">SKPD anda otomatis terpilih.</div>
                        <?php else: ?>
                            <select name="skpd_id" class="form-select select2" required>
                                <option value="">-- Pilih SKPD --</option>
                                <?php foreach ($skpd as $s): ?>
                                <option value="<?= (int)$s->id ?>" <?= set_select('skpd_id', $s->id) ?>>
                                    <?= e($s->kode_skpd) ?> - <?= e($s->nama_skpd) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periode <span class="text-danger">*</span></label>
                            <select name="periode_id" class="form-select" required>
                                <option value="">-- Pilih Periode --</option>
                                <?php foreach ($periode as $p): ?>
                                <option value="<?= (int)$p->id ?>" data-tahun="<?= (int)$p->tahun ?>" <?= set_select('periode_id', $p->id) ?>>
                                    Periode <?= (int)$p->tahun ?> <?= $p->status === 'aktif' ? '(Aktif)' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="number" name="tahun_anggaran" class="form-control" min="2020" max="2099" value="<?= set_value('tahun_anggaran', date('Y')+1) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Usulan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_usulan" class="form-control" value="<?= set_value('tanggal_usulan', date('Y-m-d')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" maxlength="500" placeholder="Catatan tambahan tentang usulan ini (opsional)..."><?= set_value('keterangan') ?></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_nihil" id="isNihil" value="1" <?= set_checkbox('is_nihil', '1') ?>>
                        <label class="form-check-label" for="isNihil">
                            Laporan nihil (tidak ada item usulan)
                        </label>
                        <div class="form-text">Centang jika SKPD tidak memiliki usulan item untuk periode ini.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan & Lanjut Tambah Item
                        </button>
                        <a href="<?= site_url("rkbmd/{$jenis}") ?>" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="text-primary"><i class="bi bi-info-circle"></i> Petunjuk</h6>
                <ol class="small mb-0 ps-3">
                    <li>Pilih periode dan tahun anggaran usulan.</li>
                    <li>Setelah header tersimpan, anda akan diarahkan ke halaman pengisian item.</li>
                    <li>Tambahkan satu per satu item barang sesuai kebutuhan.</li>
                    <li>Setelah semua item lengkap, klik "Submit ke Verifikator".</li>
                    <li>Usulan akan diteruskan ke BPKAD untuk diverifikasi.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

    // Auto-update tahun anggaran saat periode berubah
    $('select[name=periode_id]').on('change', function(){
        const tahun = $(this).find(':selected').data('tahun');
        if (tahun) $('input[name=tahun_anggaran]').val(tahun);
    });
});
</script>
