<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Partial: header info, status, action buttons
// Required: $usulan, $jenis, $detail
$canEdit = in_array($usulan->status, ['draft','revisi']) && in_array($this->auth->user()->role, ['admin','skpd']);
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title">
                <i class="bi bi-pencil-square"></i>
                Edit Usulan <?= e(label_jenis($jenis)) ?>
            </h1>
            <p class="page-subtitle">
                <strong><?= e($usulan->nomor_usulan) ?></strong>
                &middot; <?= badge_status($usulan->status) ?>
                &middot; <?= e($usulan->nama_skpd) ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url("rkbmd/{$jenis}") ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="<?= site_url("rkbmd/{$jenis}/cetak/{$usulan->id}") ?>" target="_blank" class="btn btn-outline-info">
                <i class="bi bi-printer"></i> Cetak
            </a>
            <a href="<?= site_url("rkbmd/{$jenis}/excel/{$usulan->id}") ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
        </div>
    </div>
</div>

<?php if ($usulan->status === 'revisi' && $usulan->catatan_verifikator): ?>
<div class="alert alert-warning d-flex">
    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
    <div>
        <strong>Usulan Diminta Revisi oleh Verifikator</strong>
        <div class="mt-1"><?= nl2br(e($usulan->catatan_verifikator)) ?></div>
    </div>
</div>
<?php endif; ?>

<!-- Card: Header info -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-card-list"></i> Informasi Usulan</strong>
        <?php if ($canEdit): ?>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editHeader">
            <i class="bi bi-pencil"></i> Edit Header
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><small class="text-muted">Nomor Usulan</small><br><strong><?= e($usulan->nomor_usulan) ?></strong></div>
            <div class="col-md-3"><small class="text-muted">Jenis</small><br><strong><?= e(label_jenis($jenis)) ?></strong></div>
            <div class="col-md-2"><small class="text-muted">Tahun</small><br><strong><?= (int) $usulan->tahun_anggaran ?></strong></div>
            <div class="col-md-2"><small class="text-muted">Tanggal</small><br><strong><?= tanggal_id($usulan->tanggal_usulan) ?></strong></div>
            <div class="col-md-2"><small class="text-muted">Total Item</small><br><strong><?= count($detail) ?> item</strong></div>
            <div class="col-md-2"><small class="text-muted">Laporan Nihil</small><br><strong><?= $usulan->is_nihil ? 'Ya' : 'Tidak' ?></strong></div>
            <div class="col-md-12"><small class="text-muted">Keterangan</small><br><?= e($usulan->keterangan ?: '-') ?></div>
        </div>

        <?php if ($canEdit): ?>
        <div class="collapse mt-3" id="editHeader">
            <hr>
            <form method="post" action="<?= site_url("rkbmd/{$jenis}/edit/{$usulan->id}") ?>" class="row g-2">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="update_header">
                <div class="col-md-3">
                    <label class="form-label small">Tanggal Usulan</label>
                    <input type="date" name="tanggal_usulan" class="form-control form-control-sm" value="<?= e($usulan->tanggal_usulan) ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tahun Anggaran</label>
                    <input type="number" name="tahun_anggaran" class="form-control form-control-sm" value="<?= (int) $usulan->tahun_anggaran ?>" min="2020" max="2099" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Periode</label>
                    <select name="periode_id" class="form-select form-select-sm" required>
                        <?php foreach ($periode as $p): ?>
                        <option value="<?= (int)$p->id ?>" <?= (int)$p->id === (int)$usulan->periode_id ? 'selected' : '' ?>>
                            <?= (int)$p->tahun ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control form-control-sm" value="<?= e($usulan->keterangan) ?>" maxlength="500">
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch mt-3 pt-2">
                        <input class="form-check-input" type="checkbox" name="is_nihil" id="editIsNihil" value="1" <?= $usulan->is_nihil ? 'checked' : '' ?>>
                        <label class="form-check-label" for="editIsNihil">Laporan nihil</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan Header</button>
                </div>
                <input type="hidden" name="jenis" value="<?= e($jenis) ?>">
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
