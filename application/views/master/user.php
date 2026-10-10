<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-people-fill me-2"></i>Manajemen Pengguna</h1>
            <p class="page-subtitle">Kelola akun pengguna sistem SIRKBMD.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUser" onclick="openAddModal()">
            <i class="bi bi-person-plus-fill me-1"></i>Tambah Pengguna
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="dt-user">
            <thead class="table-light">
                <tr><th>#</th><th>Username</th><th>Nama Lengkap</th><th>NIP</th><th>SKPD</th><th>Role</th><th>Hak Akses Menu</th><th class="text-center">Status</th><th class="text-center">Login Terakhir</th><th class="text-center" width="130">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?><tr><td colspan="10" class="text-center py-4 text-muted">Belum ada pengguna.</td></tr>
                <?php else: foreach ($list as $i => $u): 
                    $perms = [];
                    if (!empty($u->menu_permissions)) {
                        $perms = json_decode($u->menu_permissions, true);
                        if (!is_array($perms)) $perms = [];
                    }
                ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= e($u->username) ?></strong></td>
                    <td><?= e($u->nama_lengkap) ?><?php if ($u->jabatan): ?><br><small class="text-muted"><?= e($u->jabatan) ?></small><?php endif; ?></td>
                    <td><small><?= e($u->nip ?: '-') ?></small></td>
                    <td><small><?= e($u->nama_skpd ?: '-') ?></small></td>
                    <td><span class="badge bg-<?= ['admin'=>'danger','verifikator'=>'primary','skpd'=>'info','pimpinan'=>'success'][$u->role]??'secondary' ?>"><?= ucfirst(e($u->role)) ?></span></td>
                    <td>
                        <?php if ($u->role === 'admin'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-shield-lock-fill me-1"></i>Akses Penuh (Admin)</span>
                        <?php elseif (empty($perms)): ?>
                            <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-gear-wide-connected me-1"></i>Default Role</span>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-1" style="max-width: 260px;">
                                <?php
                                $badges = [];
                                $rkbmdItems = ['rkbmd_pengadaan', 'rkbmd_pemeliharaan', 'rkbmd_pemanfaatan', 'rkbmd_pemindahtanganan', 'rkbmd_penghapusan'];
                                $hasRkbmd = 0;
                                foreach ($rkbmdItems as $rk) {
                                    if (in_array($rk, $perms)) $hasRkbmd++;
                                }
                                if ($hasRkbmd === 5) {
                                    $badges[] = '<span class="badge bg-success-subtle text-success border" title="Semua Modul RKBMD"><i class="bi bi-diagram-3 me-1"></i>RKBMD (5)</span>';
                                } elseif ($hasRkbmd > 0) {
                                    $badges[] = '<span class="badge bg-success-subtle text-success border" title="Sebagian Modul RKBMD"><i class="bi bi-diagram-3 me-1"></i>RKBMD (' . $hasRkbmd . ')</span>';
                                }

                                if (in_array('ssh', $perms)) {
                                    $badges[] = '<span class="badge bg-primary-subtle text-primary border"><i class="bi bi-tag me-1"></i>SSH</span>';
                                }
                                if (in_array('sbu', $perms)) {
                                    $badges[] = '<span class="badge bg-info-subtle text-info border"><i class="bi bi-wallet2 me-1"></i>SBU</span>';
                                }
                                if (in_array('laporan', $perms)) {
                                    $badges[] = '<span class="badge bg-warning-subtle text-dark border"><i class="bi bi-file-earmark-bar-graph me-1"></i>Laporan</span>';
                                }
                                $verifItems = ['verifikasi_rkbmd', 'verifikasi_standar', 'penetapan_standar', 'jadwal_standar'];
                                $hasVerif = 0;
                                foreach ($verifItems as $vk) {
                                    if (in_array($vk, $perms)) $hasVerif++;
                                }
                                if ($hasVerif > 0) {
                                    $badges[] = '<span class="badge bg-dark-subtle text-dark border"><i class="bi bi-shield-check me-1"></i>Verif (' . $hasVerif . ')</span>';
                                }

                                echo !empty($badges) ? implode(' ', $badges) : '<span class="text-muted small">-</span>';
                                ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><span class="badge bg-<?= $u->is_active ? 'success' : 'secondary' ?>"><?= $u->is_active ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="text-center"><small><?= $u->last_login ? tanggal_id($u->last_login) : '-' ?></small></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-edit-user"
                                data-id="<?= (int)$u->id ?>"
                                data-username="<?= e($u->username) ?>"
                                data-nama="<?= e($u->nama_lengkap) ?>"
                                data-nip="<?= e($u->nip) ?>"
                                data-email="<?= e($u->email) ?>"
                                data-jabatan="<?= e($u->jabatan) ?>"
                                data-role="<?= e($u->role) ?>"
                                data-skpd="<?= (int)$u->skpd_id ?>"
                                data-active="<?= (int)$u->is_active ?>"
                                data-permissions="<?= htmlspecialchars($u->menu_permissions ?: '', ENT_QUOTES, 'UTF-8') ?>"
                                title="Edit Pengguna & Hak Akses"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-warning btn-reset-pw" data-id="<?= (int)$u->id ?>" data-username="<?= e($u->username) ?>" title="Reset Password"><i class="bi bi-key"></i></button>
                            <?php if ((int)$u->id !== (int)$this->currentUser->id): ?>
                            <form method="post" action="<?= site_url('master/user') ?>" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                <?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$u->id ?>">
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal User -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= site_url('master/user') ?>">
            <?= csrf_input() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" id="user_id" value="">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modal-title-user"><i class="bi bi-person-fill me-2"></i>Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Data Akun -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-person-vcard me-2 text-primary"></i>Informasi Akun & Pegawai</h6>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" name="username" id="u_username" class="form-control" required minlength="4" maxlength="50" placeholder="contoh: operator_ssh"></div>
                        <div class="col-md-4"><label class="form-label">Password <small class="text-muted" id="pw-hint">(min. 8 karakter)</small></label><input type="password" name="password" id="u_password" class="form-control" minlength="8" autocomplete="new-password"></div>
                        <div class="col-md-4"><label class="form-label">Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="nama_lengkap" id="u_nama" class="form-control" required maxlength="150" placeholder="Nama pegawai"></div>
                        <div class="col-md-4"><label class="form-label">NIP</label><input type="text" name="nip" id="u_nip" class="form-control" maxlength="25" placeholder="NIP pegawai"></div>
                        <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" id="u_email" class="form-control" maxlength="100" placeholder="email@tapinkab.go.id"></div>
                        <div class="col-md-4"><label class="form-label">Jabatan</label><input type="text" name="jabatan" id="u_jabatan" class="form-control" maxlength="150" placeholder="Operator / Pengurus Barang"></div>
                        <div class="col-md-4"><label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" id="u_role" class="form-select" required>
                                <option value="admin">Admin (Akses Penuh)</option>
                                <option value="skpd" selected>Operator SKPD</option>
                                <option value="verifikator">Verifikator BPKAD</option>
                                <option value="penetap">Penetap Standar Harga</option>
                                <option value="pimpinan">Pimpinan</option>
                            </select>
                        </div>
                        <div class="col-md-5"><label class="form-label">SKPD</label>
                            <select name="skpd_id" id="u_skpd" class="form-select">
                                <option value="">-- Pilih SKPD --</option>
                                <?php foreach ($skpd as $s): ?><option value="<?= (int)$s->id ?>"><?= e($s->kode_skpd) ?> - <?= e($s->nama_skpd) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Status</label><select name="is_active" id="u_active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                    </div>

                    <!-- Checklist Hak Akses Menu -->
                    <div class="mt-4">
                        <div class="card border border-primary-subtle shadow-none">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                                <div>
                                    <h6 class="mb-0 fw-bold text-primary">
                                        <i class="bi bi-ui-checks-grid me-1"></i>Pengaturan Hak Akses Menu & Modul
                                    </h6>
                                    <small class="text-muted">Centang menu apa saja yang diizinkan untuk diakses oleh pengguna ini.</small>
                                </div>
                                <div class="btn-group btn-group-sm flex-wrap" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="btnPresetAll" title="Centang seluruh menu">
                                        <i class="bi bi-check-all me-1"></i>Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="btnPresetSshSbu" title="User A: Hanya Usulan SSH, SBU & Laporan">
                                        <i class="bi bi-tags-fill me-1"></i>User A (SSH + SBU + Laporan)
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btnPresetRkbmd" title="User B: Hanya Usulan RKBMD & Laporan">
                                        <i class="bi bi-diagram-3-fill me-1"></i>User B (RKBMD + Laporan)
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="btnPresetClear" title="Hilangkan semua centang">
                                        <i class="bi bi-x-circle me-1"></i>Bersihkan
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div id="adminNotice" class="alert alert-info py-2 px-3 small d-none mb-3">
                                    <i class="bi bi-info-circle-fill me-1"></i>User dengan Role <strong>Admin</strong> memiliki akses sistem penuh tanpa pembatasan, namun centang di bawah tetap dapat disesuaikan.
                                </div>

                                <div class="row g-3">
                                    <?php if (!empty($availablePermissions)): foreach ($availablePermissions as $groupKey => $group): ?>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 bg-white h-100 shadow-sm">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                <div class="fw-bold text-dark small">
                                                    <i class="bi <?= e($group['icon']) ?> text-primary me-1"></i><?= e($group['label']) ?>
                                                </div>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none group-toggle-btn" data-group="<?= $groupKey ?>" style="font-size: 0.75rem;">
                                                    Pilih Grup
                                                </button>
                                            </div>
                                            <div class="vstack gap-2">
                                                <?php foreach ($group['items'] as $permKey => $perm): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input perm-checkbox group-<?= $groupKey ?>" type="checkbox" name="permissions[]" value="<?= e($permKey) ?>" id="perm_<?= e($permKey) ?>">
                                                    <label class="form-check-label" for="perm_<?= e($permKey) ?>">
                                                        <span class="fw-semibold small d-block"><?= e($perm['label']) ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;"><?= e($perm['desc']) ?></span>
                                                    </label>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; endif; ?>
                                </div>

                                <div class="mt-3 p-2 bg-light rounded small text-muted d-flex align-items-center">
                                    <i class="bi bi-info-circle text-primary me-2 fs-5"></i>
                                    <div>
                                        <strong>Petunjuk:</strong> Jika semua checkbox di atas dibiarkan kosong, akun akan otomatis menggunakan <em>default permissions</em> sesuai peran (role)-nya.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Pengguna</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="modalResetPw" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('master/user') ?>">
            <?= csrf_input() ?><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" id="rp_user_id">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Reset Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Reset password untuk: <strong id="rp_username"></strong></p>
                    <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="new_password" class="form-control" required minlength="8" placeholder="Minimal 8 karakter">
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-warning"><i class="bi bi-key me-1"></i>Reset Password</button></div>
            </div>
        </form>
    </div>
</div>

<script>
function checkRoleNotice() {
    if ($('#u_role').val() === 'admin') {
        $('#adminNotice').removeClass('d-none');
    } else {
        $('#adminNotice').addClass('d-none');
    }
}

function clearAllPermissions() {
    $('.perm-checkbox').prop('checked', false);
}

function setPresetPermissions(list) {
    clearAllPermissions();
    list.forEach(function(key) {
        $('#perm_' + key).prop('checked', true);
    });
}

function openAddModal(){
    $('#modal-title-user').html('<i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna');
    $('#user_id').val('');
    $('#u_username,#u_password,#u_nama,#u_nip,#u_email,#u_jabatan').val('');
    $('#u_role').val('skpd');
    $('#u_skpd').val('');
    $('#u_active').val('1');
    $('#pw-hint').show();
    $('#u_password').prop('required', true);
    clearAllPermissions();
    checkRoleNotice();
}

window.addEventListener('load', function() {
    if ($.fn.DataTable) {
        $('#dt-user').DataTable({
            paging: true,
            ordering: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });
    }

    $('#u_role').on('change', checkRoleNotice);

    // Preset Buttons
    $('#btnPresetAll').on('click', function() {
        $('.perm-checkbox').prop('checked', true);
    });

    $('#btnPresetClear').on('click', function() {
        clearAllPermissions();
    });

    // Preset User A: SSH, SBU, Laporan
    $('#btnPresetSshSbu').on('click', function() {
        setPresetPermissions(['ssh', 'sbu', 'laporan']);
    });

    // Preset User B: RKBMD (semua 5 jenis) + Laporan
    $('#btnPresetRkbmd').on('click', function() {
        setPresetPermissions([
            'rkbmd_pengadaan',
            'rkbmd_pemeliharaan',
            'rkbmd_pemanfaatan',
            'rkbmd_pemindahtanganan',
            'rkbmd_penghapusan',
            'laporan'
        ]);
    });

    // Toggle per group
    $('.group-toggle-btn').on('click', function() {
        const group = $(this).data('group');
        const cbs = $('.group-' + group);
        const anyUnchecked = cbs.filter(':not(:checked)').length > 0;
        cbs.prop('checked', anyUnchecked);
    });

    // Tombol Edit User
    $(document).on('click', '.btn-edit-user', function() {
        const d = $(this).data();
        $('#modal-title-user').html('<i class="bi bi-pencil-square me-2"></i>Edit Pengguna & Hak Akses');
        $('#user_id').val(d.id);
        $('#u_username').val(d.username);
        $('#u_nama').val(d.nama);
        $('#u_nip').val(d.nip);
        $('#u_email').val(d.email);
        $('#u_jabatan').val(d.jabatan);
        $('#u_role').val(d.role);
        $('#u_skpd').val(d.skpd);
        $('#u_active').val(d.active);
        $('#u_password').val('').prop('required', false);
        $('#pw-hint').hide();

        // Populate Permissions
        clearAllPermissions();
        const rawPerms = $(this).attr('data-permissions') || '';
        if (rawPerms && rawPerms !== 'null') {
            try {
                const permsArr = JSON.parse(rawPerms);
                if (Array.isArray(permsArr)) {
                    permsArr.forEach(function(permKey) {
                        $('#perm_' + permKey).prop('checked', true);
                    });
                }
            } catch (e) {
                console.error('Error parsing permissions:', e);
            }
        }

        checkRoleNotice();
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    });

    // Tombol Reset Password
    $(document).on('click', '.btn-reset-pw', function() {
        $('#rp_user_id').val($(this).data('id'));
        $('#rp_username').text($(this).data('username'));
        new bootstrap.Modal(document.getElementById('modalResetPw')).show();
    });
});
</script>
