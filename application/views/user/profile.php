<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-person-circle me-2"></i>Profil Saya</h1></div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <!-- Data Profil User -->
        <div class="card mb-3">
            <div class="card-header"><strong>Data Profil</strong></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('profile/update') ?>">
                    <?= csrf_input() ?>
                    <div class="mb-3"><label class="form-label">Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="nama_lengkap" class="form-control" value="<?= e($user->nama_lengkap) ?>" required maxlength="150"></div>
                    <div class="mb-3"><label class="form-label">NIP</label><input type="text" name="nip" class="form-control" value="<?= e($user->nip) ?>" maxlength="25"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user->email) ?>" maxlength="100"></div>
                    <div class="mb-3"><label class="form-label">Jabatan</label><input type="text" name="jabatan" class="form-control" value="<?= e($user->jabatan) ?>" maxlength="150"></div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Profil</button>
                </form>
            </div>
        </div>

        <?php if ($user->role === 'skpd'): ?>
        <!-- Data Kepala SKPD & Pengurus Barang (hanya untuk user SKPD) -->
        <div class="card mb-3">
            <div class="card-header"><strong>Data Kepala SKPD & Pengurus Barang</strong></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('profile/update') ?>">
                    <?= csrf_input() ?>
                    <h6 class="mb-3 text-primary">Kepala SKPD</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Kepala SKPD</label>
                            <input type="text" name="kepala_skpd" class="form-control" value="<?= e($user->kepala_skpd) ?>" maxlength="150">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP Kepala SKPD</label>
                            <input type="text" name="nip_kepala" class="form-control" value="<?= e($user->nip_kepala) ?>" maxlength="25">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan Kepala</label>
                        <input type="text" name="jabatan_kepala" class="form-control" value="<?= e($user->jabatan_kepala) ?>" maxlength="150">
                    </div>

                    <h6 class="mb-3 text-primary">Pengurus Barang</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pengurus Barang</label>
                            <input type="text" name="nama_pengurus" class="form-control" value="<?= e($user->nama_pengurus) ?>" maxlength="150">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP Pengurus Barang</label>
                            <input type="text" name="nip_pengurus" class="form-control" value="<?= e($user->nip_pengurus) ?>" maxlength="25">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Simpan Data SKPD</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><strong>Ganti Password</strong></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('profile/password') ?>">
                    <?= csrf_input() ?>
                    <div class="mb-3"><label class="form-label">Password Saat Ini</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password Baru <small class="text-muted">(min. 8 karakter)</small></label><input type="password" name="new_password" class="form-control" required minlength="8" maxlength="200"></div>
                    <div class="mb-3"><label class="form-label">Konfirmasi Password Baru</label><input type="password" name="confirm_password" class="form-control" required></div>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-key me-1"></i>Ganti Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:36px;font-weight:700;">
                    <?= strtoupper(substr($user->nama_lengkap,0,1)) ?>
                </div>
                <h5><?= e($user->nama_lengkap) ?></h5>
                <p class="text-muted mb-1"><span class="badge bg-<?= ['admin'=>'danger','verifikator'=>'primary','skpd'=>'info','pimpinan'=>'success'][$user->role]??'secondary' ?>"><?= ucfirst(e($user->role)) ?></span></p>
                <p class="text-muted small mb-1">Username: <strong><?= e($user->username) ?></strong></p>
                <?php if ($user->nip): ?><p class="text-muted small mb-1">NIP: <?= e($user->nip) ?></p><?php endif; ?>
                <?php if ($user->jabatan): ?><p class="text-muted small mb-1"><?= e($user->jabatan) ?></p><?php endif; ?>
                <?php if ($user->email): ?><p class="text-muted small mb-0"><?= e($user->email) ?></p><?php endif; ?>
            </div>
        </div>

        <?php if ($user->role === 'skpd'): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-building me-2 text-primary"></i>Info SKPD</h6>
                <p class="mb-1"><strong><?= e($user->nama_skpd) ?></strong></p>
                <p class="small text-muted mb-2">Kode: <?= e($user->kode_skpd) ?></p>

                <?php if ($user->kepala_skpd): ?>
                <hr class="my-2">
                <p class="small mb-1 text-primary"><i class="bi bi-person-check me-1"></i>Kepala SKPD:</p>
                <p class="small mb-0"><strong><?= e($user->kepala_skpd) ?></strong></p>
                <?php if ($user->nip_kepala): ?><p class="small text-muted mb-0">NIP: <?= e($user->nip_kepala) ?></p><?php endif; ?>
                <?php endif; ?>

                <?php if ($user->nama_pengurus): ?>
                <hr class="my-2">
                <p class="small mb-1 text-success"><i class="bi bi-person-badge me-1"></i>Pengurus Barang:</p>
                <p class="small mb-0"><strong><?= e($user->nama_pengurus) ?></strong></p>
                <?php if ($user->nip_pengurus): ?><p class="small text-muted mb-0">NIP: <?= e($user->nip_pengurus) ?></p><?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-info-circle me-2 text-primary"></i>Info Akun</h6>
                <ul class="list-unstyled small mb-0 text-muted">
                    <li><i class="bi bi-calendar me-2"></i>Bergabung: <?= tanggal_id($user->created_at) ?></li>
                    <li><i class="bi bi-clock me-2"></i>Login terakhir: <?= $user->last_login ? tanggal_id($user->last_login) : '-' ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
