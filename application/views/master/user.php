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
                <tr><th>#</th><th>Username</th><th>Nama Lengkap</th><th>NIP</th><th>SKPD</th><th>Role</th><th class="text-center">Status</th><th class="text-center">Login Terakhir</th><th class="text-center" width="130">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?><tr><td colspan="9" class="text-center py-4 text-muted">Belum ada pengguna.</td></tr>
                <?php else: foreach ($list as $i => $u): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= e($u->username) ?></strong></td>
                    <td><?= e($u->nama_lengkap) ?><?php if ($u->jabatan): ?><br><small class="text-muted"><?= e($u->jabatan) ?></small><?php endif; ?></td>
                    <td><small><?= e($u->nip ?: '-') ?></small></td>
                    <td><small><?= e($u->nama_skpd ?: '-') ?></small></td>
                    <td><span class="badge bg-<?= ['admin'=>'danger','verifikator'=>'primary','skpd'=>'info','pimpinan'=>'success'][$u->role]??'secondary' ?>"><?= ucfirst(e($u->role)) ?></span></td>
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
                                data-active="<?= (int)$u->is_active ?>"><i class="bi bi-pencil"></i></button>
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
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= site_url('master/user') ?>">
            <?= csrf_input() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" id="user_id" value="">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="modal-title-user">Tambah Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" name="username" id="u_username" class="form-control" required minlength="4" maxlength="50"></div>
                        <div class="col-md-6"><label class="form-label">Password <small class="text-muted" id="pw-hint">(min. 8 karakter, wajib untuk user baru)</small></label><input type="password" name="password" id="u_password" class="form-control" minlength="8" autocomplete="new-password"></div>
                        <div class="col-md-6"><label class="form-label">Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="nama_lengkap" id="u_nama" class="form-control" required maxlength="150"></div>
                        <div class="col-md-6"><label class="form-label">NIP</label><input type="text" name="nip" id="u_nip" class="form-control" maxlength="25"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" id="u_email" class="form-control" maxlength="100"></div>
                        <div class="col-md-6"><label class="form-label">Jabatan</label><input type="text" name="jabatan" id="u_jabatan" class="form-control" maxlength="150"></div>
                        <div class="col-md-4"><label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" id="u_role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="skpd" selected>Operator SKPD</option>
                                <option value="verifikator">Verifikator BPKAD</option>
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
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button></div>
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
function openAddModal(){
    $('#modal-title-user').text('Tambah Pengguna');
    $('#user_id').val('');
    $('#u_username,#u_password,#u_nama,#u_nip,#u_email,#u_jabatan').val('');
    $('#u_role').val('skpd'); $('#u_skpd').val(''); $('#u_active').val('1');
    $('#pw-hint').show();
    $('#u_password').prop('required',true);
}

window.addEventListener('load', function() {
    if($.fn.DataTable) $('#dt-user').DataTable({paging:true,ordering:true,language:{url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'}});
    $(document).on('click','.btn-edit-user',function(){
        const d=$(this).data();
        $('#modal-title-user').text('Edit Pengguna');
        $('#user_id').val(d.id);
        $('#u_username').val(d.username);
        $('#u_nama').val(d.nama);
        $('#u_nip').val(d.nip);
        $('#u_email').val(d.email);
        $('#u_jabatan').val(d.jabatan);
        $('#u_role').val(d.role);
        $('#u_skpd').val(d.skpd);
        $('#u_active').val(d.active);
        $('#u_password').val('').prop('required',false);
        $('#pw-hint').hide();
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    });
    $(document).on('click','.btn-reset-pw',function(){
        $('#rp_user_id').val($(this).data('id'));
        $('#rp_username').text($(this).data('username'));
        new bootstrap.Modal(document.getElementById('modalResetPw')).show();
    });
});
</script>
