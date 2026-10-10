<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h1 class="page-title"><i class="bi bi-people-fill me-2"></i>Manajemen Pengguna</h1>
            <p class="page-subtitle">Kelola akun pengguna sistem SIPA dan verifikasi pendaftaran operator.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($reg_settings['registration_enabled']) && $reg_settings['registration_enabled'] === '1'): ?>
                <span class="badge bg-success-subtle text-success border border-success py-2 px-3 d-none d-md-inline-block" title="Pendaftaran mandiri sedang dibuka untuk operator SKPD">
                    <i class="bi bi-person-check-fill me-1"></i>Pendaftaran Mandiri: Terbuka
                </span>
            <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border py-2 px-3 d-none d-md-inline-block" title="Pendaftaran mandiri sedang ditutup oleh Administrator">
                    <i class="bi bi-person-x-fill me-1"></i>Pendaftaran Mandiri: Ditutup
                </span>
            <?php endif; ?>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalRegSettings">
                <i class="bi bi-gear-fill me-1"></i>Pengaturan Pendaftaran
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUser" onclick="openAddModal()">
                <i class="bi bi-person-plus-fill me-1"></i>Tambah Pengguna
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="dt-user">
            <thead class="table-light">
                <tr><th>#</th><th>Username</th><th>Nama Lengkap</th><th>NIP / WA</th><th>SKPD</th><th>Role</th><th>Hak Akses Menu</th><th class="text-center">Status</th><th class="text-center">Login Terakhir</th><th class="text-center" width="160">Aksi</th></tr>
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
                <tr class="<?= !$u->is_active ? 'table-warning-subtle' : '' ?>">
                    <td><?= $i+1 ?></td>
                    <td><strong><?= e($u->username) ?></strong></td>
                    <td><?= e($u->nama_lengkap) ?><?php if ($u->jabatan): ?><br><small class="text-muted"><?= e($u->jabatan) ?></small><?php endif; ?></td>
                    <td>
                        <small class="d-block"><?= e($u->nip ?: '-') ?></small>
                        <?php if (!empty($u->no_wa)): ?>
                        <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', (substr($u->no_wa, 0, 2) === '08' ? '62' . substr($u->no_wa, 1) : $u->no_wa)) ?>" target="_blank" class="badge bg-success-subtle text-success text-decoration-none border mt-1" title="Kirim Pesan WhatsApp">
                            <i class="bi bi-whatsapp me-1"></i><?= e($u->no_wa) ?>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td><small><?= e($u->nama_skpd ?: '-') ?></small></td>
                    <td><span class="badge bg-<?= ['admin'=>'danger','verifikator'=>'primary','skpd'=>'info','pimpinan'=>'success'][$u->role]??'secondary' ?>"><?= ucfirst(e($u->role)) ?></span></td>
                    <td>
                        <?php if ($u->role === 'admin'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-shield-lock-fill me-1"></i>Akses Penuh (Admin)</span>
                        <?php elseif (empty($perms)): ?>
                            <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-gear-wide-connected me-1"></i>Default Role</span>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-1" style="max-width: 280px;">
                                <?php
                                $badges = [];
                                $rkbmdItems = ['rkbmd_pengadaan', 'rkbmd_pemeliharaan', 'rkbmd_pemanfaatan', 'rkbmd_pemindahtanganan', 'rkbmd_penghapusan'];
                                $hasRkbmd = 0;
                                foreach ($rkbmdItems as $rk) {
                                    if (in_array($rk, $perms)) $hasRkbmd++;
                                }
                                if ($hasRkbmd === 5) {
                                    $badges[] = '<span class="badge bg-success-subtle text-success border" title="Semua 5 Modul RKBMD"><i class="bi bi-diagram-3-fill me-1"></i>Grup RKBMD (5/5)</span>';
                                } elseif ($hasRkbmd > 0) {
                                    $badges[] = '<span class="badge bg-success-subtle text-success border" title="Sebagian Modul RKBMD"><i class="bi bi-diagram-3 me-1"></i>Grup RKBMD (' . $hasRkbmd . '/5)</span>';
                                }

                                $hasSsh = in_array('ssh', $perms);
                                $hasSbu = in_array('sbu', $perms);
                                if ($hasSsh && $hasSbu) {
                                    $badges[] = '<span class="badge bg-primary-subtle text-primary border" title="Modul SSH & SBU Lengkap"><i class="bi bi-tags-fill me-1"></i>Grup Standar (SSH & SBU)</span>';
                                } elseif ($hasSsh) {
                                    $badges[] = '<span class="badge bg-primary-subtle text-primary border"><i class="bi bi-tag-fill me-1"></i>Standar SSH</span>';
                                } elseif ($hasSbu) {
                                    $badges[] = '<span class="badge bg-info-subtle text-info border"><i class="bi bi-wallet2 me-1"></i>Standar SBU</span>';
                                }

                                if (in_array('laporan', $perms)) {
                                    $badges[] = '<span class="badge bg-warning-subtle text-dark border"><i class="bi bi-file-earmark-bar-graph-fill me-1"></i>Grup Laporan</span>';
                                }

                                $verifItems = ['verifikasi_rkbmd', 'verifikasi_standar', 'penetapan_standar', 'jadwal_standar'];
                                $hasVerif = 0;
                                foreach ($verifItems as $vk) {
                                    if (in_array($vk, $perms)) $hasVerif++;
                                }
                                if ($hasVerif === 4) {
                                    $badges[] = '<span class="badge bg-dark-subtle text-dark border"><i class="bi bi-shield-check me-1"></i>Grup Verif (4/4)</span>';
                                } elseif ($hasVerif > 0) {
                                    $badges[] = '<span class="badge bg-dark-subtle text-dark border"><i class="bi bi-shield me-1"></i>Verif (' . $hasVerif . '/4)</span>';
                                }

                                echo !empty($badges) ? implode(' ', $badges) : '<span class="text-muted small">-</span>';
                                ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($u->is_active): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark border border-warning" title="Akun pendaftaran mandiri menunggu verifikasi admin">
                                <i class="bi bi-clock-history me-1"></i>Menunggu Verifikasi
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><small><?= $u->last_login ? tanggal_id($u->last_login) : '-' ?></small></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <?php if (!$u->is_active): ?>
                                <form method="post" action="<?= site_url('master/user') ?>" class="d-inline" onsubmit="return confirm('Setujui dan aktifkan akun <?= e($u->nama_lengkap) ?>?')">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="id" value="<?= (int)$u->id ?>">
                                    <button type="submit" class="btn btn-success" title="Setujui & Aktifkan Akun">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($u->no_wa)): ?>
                                <button type="button" class="btn btn-outline-success btn-wa-user"
                                    data-nama="<?= e($u->nama_lengkap) ?>"
                                    data-nowa="<?= e($u->no_wa) ?>"
                                    data-skpd="<?= e($u->nama_skpd ?: 'SKPD') ?>"
                                    data-active="<?= (int)$u->is_active ?>"
                                    title="Hubungi / Kirim Info via WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                            <?php endif; ?>

                            <button class="btn btn-outline-primary btn-edit-user"
                                data-id="<?= (int)$u->id ?>"
                                data-username="<?= e($u->username) ?>"
                                data-nama="<?= e($u->nama_lengkap) ?>"
                                data-nip="<?= e($u->nip) ?>"
                                data-email="<?= e($u->email) ?>"
                                data-nowa="<?= e($u->no_wa ?? '') ?>"
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
                                <button type="submit" class="btn btn-outline-danger" title="Hapus Pengguna"><i class="bi bi-trash"></i></button>
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
                        <div class="col-md-4"><label class="form-label"><i class="bi bi-whatsapp text-success me-1"></i>No. WhatsApp</label><input type="text" name="no_wa" id="u_nowa" class="form-control" maxlength="25" placeholder="Contoh: 08123456789"></div>
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
                        <div class="col-md-4"><label class="form-label">SKPD</label>
                            <select name="skpd_id" id="u_skpd" class="form-select">
                                <option value="">-- Pilih SKPD --</option>
                                <?php foreach ($skpd as $s): ?><option value="<?= (int)$s->id ?>"><?= e($s->kode_skpd) ?> - <?= e($s->nama_skpd) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Status</label><select name="is_active" id="u_active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                    </div>

                    <!-- Checklist Hak Akses Menu Berhirarki -->
                    <div class="mt-4">
                        <div class="card border border-primary-subtle shadow-none">
                            <div class="card-header bg-light py-2">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-primary">
                                            <i class="bi bi-diagram-3-fill me-1"></i>Hirarki Pengelompokan Hak Akses Menu & Modul
                                        </h6>
                                        <small class="text-muted">Centang Group Parent untuk mengaktifkan seluruh modul di grup tersebut, atau atur secara spesifik per sub-modul.</small>
                                    </div>
                                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                                        <button type="button" class="btn btn-outline-primary" id="btnPresetAll" title="Centang seluruh modul">
                                            <i class="bi bi-check-all me-1"></i>Pilih Semua
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="btnPresetClear" title="Hilangkan semua centang">
                                            <i class="bi bi-x-circle me-1"></i>Bersihkan
                                        </button>
                                    </div>
                                </div>

                                <!-- Template Group Profiler -->
                                <div class="row g-2 align-items-center p-2 bg-white rounded border border-secondary-subtle">
                                    <div class="col-md-4">
                                        <label class="form-label mb-0 small fw-bold text-dark d-flex align-items-center">
                                            <i class="bi bi-layers-fill text-primary me-1 fs-6"></i>Template Grup Hak Akses:
                                        </label>
                                    </div>
                                    <div class="col-md-8">
                                        <select class="form-select form-select-sm" id="selectGroupTemplate">
                                            <option value="">-- Pilih Preset Template Grup Cepat --</option>
                                            <option value="user_a">🏷️ Preset User A: Grup Standar Harga (SSH + SBU) & Laporan</option>
                                            <option value="user_b">📦 Preset User B: Grup Perencanaan RKBMD (5 Modul) & Laporan</option>
                                            <option value="verifikator">⚖️ Preset Verifikator: Telaah RKBMD & Standar Harga, Jadwal, Laporan</option>
                                            <option value="penetap">🛡️ Preset Penetap & Pimpinan: RKBMD, Standar Harga, Penetapan SK, Laporan</option>
                                            <option value="all">👑 Preset Akses Penuh: Aktifkan Semua Grup</option>
                                            <option value="clear">🧹 Bersihkan Semua Grup (Gunakan Hak Bawaan Role)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div id="adminNotice" class="alert alert-info py-2 px-3 small d-none mb-3">
                                    <i class="bi bi-info-circle-fill me-1"></i>User dengan Role <strong>Admin</strong> memiliki akses sistem penuh tanpa pembatasan, namun centang di bawah tetap dapat disesuaikan.
                                </div>

                                <div class="row g-3">
                                    <?php if (!empty($availablePermissions)): foreach ($availablePermissions as $groupKey => $group): ?>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 bg-white h-100 shadow-sm tree-group-card">
                                            <!-- Parent Group Header -->
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom bg-light-subtle rounded p-2">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input group-parent-cb" type="checkbox" id="group_cb_<?= $groupKey ?>" data-group="<?= $groupKey ?>">
                                                    <label class="form-check-label fw-bold text-dark small cursor-pointer" for="group_cb_<?= $groupKey ?>">
                                                        <i class="bi <?= e($group['icon']) ?> text-primary me-1"></i><?= e($group['label']) ?>
                                                    </label>
                                                </div>
                                                <span class="badge bg-white text-secondary border small group-count-badge" id="badge_count_<?= $groupKey ?>">
                                                    0 / <?= count($group['items']) ?>
                                                </span>
                                            </div>

                                            <!-- Child Items (Hierarchical Tree Indentation) -->
                                            <div class="tree-group-children ms-3 ps-3 border-start border-2 border-primary-subtle vstack gap-2 pt-1">
                                                <?php foreach ($group['items'] as $permKey => $perm): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input perm-checkbox child-of-<?= $groupKey ?>" type="checkbox" name="permissions[]" value="<?= e($permKey) ?>" id="perm_<?= e($permKey) ?>" data-group="<?= $groupKey ?>">
                                                    <label class="form-check-label cursor-pointer" for="perm_<?= e($permKey) ?>">
                                                        <span class="fw-semibold small d-block text-dark">
                                                            <i class="bi bi-arrow-return-right text-muted me-1 small"></i><?= e($perm['label']) ?>
                                                        </span>
                                                        <span class="text-muted" style="font-size: 0.74rem;"><?= e($perm['desc']) ?></span>
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
                                        <strong>Petunjuk Hirarki:</strong> Centang toggle pada <strong>Parent Group</strong> untuk mengaktifkan seluruh sub-modul anak sekaligus. Jika hanya sebagian anak yang dicentang, grup akan berstatus <em>indeterminate</em> (strip).
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

<!-- Modal Pengaturan Pendaftaran Mandiri -->
<div class="modal fade" id="modalRegSettings" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('master/user') ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save_reg_settings">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Pengaturan Pendaftaran Mandiri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Atur kebijakan registrasi akun mandiri bagi operator SKPD melalui tautan halaman publik.</p>

                    <div class="card p-3 mb-3 border">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="registration_enabled" id="set_reg_enabled" value="1" <?= (!empty($reg_settings['registration_enabled']) && $reg_settings['registration_enabled'] === '1') ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="set_reg_enabled">Buka Pendaftaran Mandiri</label>
                        </div>
                        <small class="text-muted">Jika dinonaktifkan, tautan dan formulir registrasi mandiri akan ditutup untuk publik.</small>
                    </div>

                    <div class="card p-3 mb-3 border">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="registration_require_approval" id="set_reg_approval" value="1" <?= (!empty($reg_settings['registration_require_approval']) && $reg_settings['registration_require_approval'] === '1') ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="set_reg_approval">Wajib Verifikasi & Persetujuan Admin</label>
                        </div>
                        <small class="text-muted">Akun baru yang mendaftar mandiri akan berstatus <em>Nonaktif / Menunggu Verifikasi</em> sampai disetujui oleh Administrator BPKAD (Sangat Disarankan untuk Keamanan).</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role Bawaan Pendaftar Baru</label>
                        <select name="registration_default_role" class="form-select">
                            <option value="operator_skpd" <?= ($reg_settings['registration_default_role'] === 'operator_skpd') ? 'selected' : '' ?>>Operator SKPD (operator_skpd - Rekomendasi)</option>
                            <option value="skpd" <?= ($reg_settings['registration_default_role'] === 'skpd') ? 'selected' : '' ?>>SKPD / Unit Kerja (skpd)</option>
                        </select>
                        <div class="form-text">Role yang otomatis disematkan pada pengguna saat berhasil mendaftar mandiri.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button>
                </div>
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

// Sinkronisasi status Parent Checkbox & Badge Counter berdasarkan anak-anaknya
function updateGroupState(groupKey) {
    const children = $('.child-of-' + groupKey);
    const total = children.length;
    const checked = children.filter(':checked').length;
    const parent = $('#group_cb_' + groupKey);
    const badge = $('#badge_count_' + groupKey);

    badge.text(checked + ' / ' + total);

    if (checked === total && total > 0) {
        parent.prop('checked', true).prop('indeterminate', false);
        badge.removeClass('bg-white text-secondary bg-warning-subtle text-dark border-warning').addClass('bg-primary text-white border-primary');
    } else if (checked === 0) {
        parent.prop('checked', false).prop('indeterminate', false);
        badge.removeClass('bg-primary text-white border-primary bg-warning-subtle text-dark border-warning').addClass('bg-white text-secondary');
    } else {
        parent.prop('checked', false).prop('indeterminate', true);
        badge.removeClass('bg-primary text-white border-primary bg-white text-secondary').addClass('bg-warning-subtle text-dark border-warning');
    }
}

// Update semua grup sekaligus
function updateAllGroups() {
    $('.group-parent-cb').each(function() {
        const groupKey = $(this).data('group');
        updateGroupState(groupKey);
    });
}

function clearAllPermissions() {
    $('.perm-checkbox').prop('checked', false);
    $('#selectGroupTemplate').val('');
    updateAllGroups();
}

function setPresetPermissions(list) {
    $('.perm-checkbox').prop('checked', false);
    list.forEach(function(key) {
        $('#perm_' + key).prop('checked', true);
    });
    updateAllGroups();
}

function openAddModal(){
    $('#modal-title-user').html('<i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna');
    $('#user_id').val('');
    $('#u_username,#u_password,#u_nama,#u_nip,#u_email,#u_nowa,#u_jabatan').val('');
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

    // 1. Parent Checkbox di-klik -> Centang / Hapus semua anak dalam grup
    $(document).on('change', '.group-parent-cb', function() {
        const groupKey = $(this).data('group');
        const isChecked = $(this).is(':checked');
        $('.child-of-' + groupKey).prop('checked', isChecked);
        updateGroupState(groupKey);
        $('#selectGroupTemplate').val(''); // Custom
    });

    // 2. Child Checkbox di-klik -> Perbarui status Parent (checked, unchecked, indeterminate)
    $(document).on('change', '.perm-checkbox', function() {
        const groupKey = $(this).data('group');
        if (groupKey) {
            updateGroupState(groupKey);
        }
        $('#selectGroupTemplate').val(''); // Custom
    });

    // 3. Dropdown Template Grup Hak Akses
    $('#selectGroupTemplate').on('change', function() {
        const val = $(this).val();
        if (val === 'user_a') {
            // Preset User A: SSH + SBU + Laporan
            setPresetPermissions(['ssh', 'sbu', 'laporan']);
        } else if (val === 'user_b') {
            // Preset User B: RKBMD (5 Modul) + Laporan
            setPresetPermissions([
                'rkbmd_pengadaan',
                'rkbmd_pemeliharaan',
                'rkbmd_pemanfaatan',
                'rkbmd_pemindahtanganan',
                'rkbmd_penghapusan',
                'laporan'
            ]);
        } else if (val === 'verifikator') {
            // Preset Verifikator BPKAD
            setPresetPermissions([
                'verifikasi_rkbmd',
                'verifikasi_standar',
                'jadwal_standar',
                'laporan'
            ]);
        } else if (val === 'penetap') {
            // Preset Penetap & Pimpinan
            setPresetPermissions([
                'rkbmd_pengadaan',
                'rkbmd_pemeliharaan',
                'rkbmd_pemanfaatan',
                'rkbmd_pemindahtanganan',
                'rkbmd_penghapusan',
                'ssh',
                'sbu',
                'penetapan_standar',
                'laporan'
            ]);
        } else if (val === 'all') {
            $('.perm-checkbox').prop('checked', true);
            updateAllGroups();
        } else if (val === 'clear') {
            clearAllPermissions();
        }
        $(this).val(val);
    });

    // Tombol Pilih Semua & Bersihkan
    $('#btnPresetAll').on('click', function() {
        $('.perm-checkbox').prop('checked', true);
        $('#selectGroupTemplate').val('all');
        updateAllGroups();
    });

    $('#btnPresetClear').on('click', function() {
        clearAllPermissions();
        $('#selectGroupTemplate').val('clear');
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
        $('#u_nowa').val(d.nowa || '');
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

        updateAllGroups();
        checkRoleNotice();
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    });

    // Tombol Reset Password
    $(document).on('click', '.btn-reset-pw', function() {
        $('#rp_user_id').val($(this).data('id'));
        $('#rp_username').text($(this).data('username'));
        new bootstrap.Modal(document.getElementById('modalResetPw')).show();
    });

    // Tombol Kirim WhatsApp ke Pengguna
    $(document).on('click', '.btn-wa-user', function() {
        var nama   = $(this).data('nama');
        var nowa   = String($(this).data('nowa') || '');
        var skpd   = $(this).data('skpd');
        var active = $(this).data('active');

        var pesan = '';
        if (active == 1) {
            pesan = "🏛️ *PEMBERITAHUAN SIPA KABUPATEN TAPIN*\n" +
                    "----------------------------------------\n" +
                    "Yth. *" + nama + "* (" + skpd + ")\n\n" +
                    "Akun Anda pada sistem SIPA telah *AKTIF* dan diverifikasi oleh Administrator BPKAD Kabupaten Tapin.\n\n" +
                    "Silakan masuk ke aplikasi melalui:\n" +
                    "👉 https://sipa.bkadtapinkab.online/login\n\n" +
                    "_Pemberitahuan resmi BPKAD Kabupaten Tapin_";
        } else {
            pesan = "🏛️ *PEMBERITAHUAN SIPA KABUPATEN TAPIN*\n" +
                    "----------------------------------------\n" +
                    "Yth. *" + nama + "* (" + skpd + ")\n\n" +
                    "Pendaftaran akun Anda pada sistem SIPA sedang dalam proses verifikasi oleh Administrator BPKAD Kabupaten Tapin.\n\n" +
                    "_Pemberitahuan resmi BPKAD Kabupaten Tapin_";
        }

        if (window.SipaWa && typeof window.SipaWa.open === 'function') {
            window.SipaWa.open({
                phone: nowa,
                recipient_name: nama + ' (' + skpd + ')',
                message: pesan
            });
        } else {
            var clean = nowa.replace(/[^0-9]/g, '');
            if (clean.substring(0, 2) === '08') clean = '62' + clean.substring(1);
            window.open('https://api.whatsapp.com/send?phone=' + clean + '&text=' + encodeURIComponent(pesan), '_blank');
        }
    });
});
</script>

