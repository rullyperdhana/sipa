<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Registrasi Akun - SIPA') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
<style>
:root {
    --primary: #1e6091;
    --primary-dark: #134263;
    --accent: #f59e0b;
}
* { box-sizing: border-box; }
body {
    margin: 0;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
}
.auth-container {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    width: 100%;
    max-width: 1020px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    min-height: 640px;
}
.auth-banner {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff;
    padding: 50px 36px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.auth-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 20% 80%, rgba(245,158,11,.2), transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,.08), transparent 50%);
}
.auth-banner > * { position: relative; z-index: 1; }
.banner-logo {
    width: 72px;
    height: 72px;
    background: rgba(255,255,255,.15);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
}
.banner-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 10px;
    letter-spacing: -.5px;
}
.banner-subtitle {
    opacity: .9;
    line-height: 1.6;
    margin-bottom: 24px;
    font-size: 14px;
}
.feature-list { list-style: none; padding: 0; margin: 0; }
.feature-list li {
    padding: 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    opacity: .9;
}
.feature-list i { color: var(--accent); }

.auth-form {
    padding: 40px 45px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    max-height: 90vh;
    overflow-y: auto;
}
.form-title {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 6px;
}
.form-subtitle {
    color: #6b7280;
    margin-bottom: 20px;
    font-size: 14px;
}
.form-floating > .form-control, .form-floating > .form-select {
    border-radius: 10px;
    padding: 12px 14px;
    height: 52px;
    border-color: #e5e7eb;
    font-size: 14px;
}
.form-floating > .form-control:focus, .form-floating > .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(30,96,145,.1);
}
.form-floating > label {
    padding: 14px 14px;
    font-size: 13px;
}
.form-floating { margin-bottom: 12px; }
.password-toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: 0;
    color: #6b7280;
    cursor: pointer;
    z-index: 5;
}
.btn-auth {
    background: var(--primary);
    color: #fff;
    border: 0;
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    width: 100%;
    transition: all .2s;
    font-size: 15px;
}
.btn-auth:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 8px 20px rgba(30,96,145,.3); }

.alert { border-radius: 10px; border: 0; font-size: 13.5px; }

@media (max-width: 860px) {
    .auth-container { grid-template-columns: 1fr; max-width: 520px; }
    .auth-banner { display: none; }
    .auth-form { padding: 30px 24px; max-height: none; }
}
</style>
</head>
<body>

<div class="auth-container">
    <div class="auth-banner">
        <div>
            <div class="banner-logo"><i class="bi bi-building-fill-check"></i></div>
            <h1 class="banner-title">SIPA</h1>
            <p class="banner-subtitle">
                Sistem Informasi Pengelolaan Aset<br>
                <strong>Pemerintah Kabupaten Tapin</strong>
            </p>
            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill"></i> Pengusulan & Perencanaan RKBMD</li>
                <li><i class="bi bi-check-circle-fill"></i> Standar Satuan Harga (SSH) & SBU</li>
                <li><i class="bi bi-check-circle-fill"></i> Verifikasi Online oleh Tim BPKAD</li>
                <li><i class="bi bi-check-circle-fill"></i> Notifikasi WhatsApp Terintegrasi</li>
            </ul>
        </div>
        <div style="opacity:.8; font-size: 12.5px;">
            <i class="bi bi-shield-lock-fill me-1"></i> Sistem terlindungi & terenkripsi SSL.
        </div>
    </div>

    <div class="auth-form">
        <?php if (!empty($is_closed)): ?>
            <!-- JIKA PENDAFTARAN DITUTUP OLEH ADMIN -->
            <div class="text-center py-4">
                <div class="mb-3 text-warning" style="font-size: 48px;">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <h2 class="form-title">Pendaftaran Mandiri Ditutup</h2>
                <p class="text-muted mt-2 mb-4" style="line-height: 1.6;">
                    Pendaftaran akun secara mandiri saat ini sedang dinonaktifkan oleh Administrator BPKAD Kabupaten Tapin.<br><br>
                    Untuk mendapatkan akun operator SKPD, silakan berkoordinasi langsung dengan <strong>Bidang Pengelolaan Aset BPKAD Kabupaten Tapin</strong>.
                </p>
                <a href="<?= site_url('login') ?>" class="btn btn-auth" style="text-decoration:none; display:inline-block; width:auto; padding: 10px 24px;">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Login
                </a>
            </div>
        <?php else: ?>
            <h2 class="form-title">Pendaftaran Akun Operator</h2>
            <p class="form-subtitle">Daftarkan akun operator untuk instansi / SKPD Anda.</p>

            <?php if (!empty($require_approval)): ?>
                <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Verifikasi Admin:</strong> Akun yang didaftarkan akan diperiksa dan disetujui oleh BPKAD sebelum dapat digunakan login.
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($msg = $this->session->flashdata('danger')): ?>
                <div class="alert alert-danger py-2 px-3 mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $msg ?></div>
            <?php endif; ?>
            <?php if ($msg = $this->session->flashdata('success')): ?>
                <div class="alert alert-success py-2 px-3 mb-3"><i class="bi bi-check-circle-fill me-2"></i><?= e($msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= site_url('register/process') ?>" autocomplete="off" novalidate>
                <?= csrf_input() ?>

                <div class="row g-2">
                    <div class="col-md-7">
                        <div class="form-floating">
                            <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" placeholder="Nama Lengkap"
                                   value="<?= e(set_value('nama_lengkap')) ?>" required maxlength="150" autofocus>
                            <label for="nama_lengkap"><i class="bi bi-person-badge me-1"></i>Nama Lengkap *</label>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-floating">
                            <input type="text" name="nip" id="nip" class="form-control" placeholder="NIP"
                                   value="<?= e(set_value('nip')) ?>" maxlength="25">
                            <label for="nip"><i class="bi bi-card-text me-1"></i>NIP (Opsional)</label>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="username" id="username" class="form-control" placeholder="Username"
                                   value="<?= e(set_value('username')) ?>" required maxlength="50">
                            <label for="username"><i class="bi bi-person me-1"></i>Username *</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email"
                                   value="<?= e(set_value('email')) ?>" required maxlength="100">
                            <label for="email"><i class="bi bi-envelope me-1"></i>Email *</label>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-5">
                        <div class="form-floating">
                            <input type="tel" name="no_wa" id="no_wa" class="form-control" placeholder="No. WhatsApp"
                                   value="<?= e(set_value('no_wa')) ?>" required maxlength="20">
                            <label for="no_wa"><i class="bi bi-whatsapp me-1 text-success"></i>No. WhatsApp *</label>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-floating">
                            <select name="skpd_id" id="skpd_id" class="form-select" required>
                                <option value="">Pilih SKPD / Instansi...</option>
                                <?php if (!empty($skpd_list)): foreach ($skpd_list as $skpd): ?>
                                    <option value="<?= $skpd->id ?>" <?= set_select('skpd_id', $skpd->id) ?>><?= e($skpd->nama_skpd) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                            <label for="skpd_id"><i class="bi bi-building me-1"></i>SKPD / Dinas *</label>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Password"
                                   required minlength="8" maxlength="200">
                            <label for="password"><i class="bi bi-lock me-1"></i>Password (min 8) *</label>
                            <button type="button" class="password-toggle" onclick="togglePass('password', 'eye-icon-pass')" aria-label="Show password">
                                <i class="bi bi-eye" id="eye-icon-pass"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Konfirmasi Password"
                                   required minlength="8" maxlength="200">
                            <label for="password_confirm"><i class="bi bi-lock-fill me-1"></i>Konfirmasi Password *</label>
                            <button type="button" class="password-toggle" onclick="togglePass('password_confirm', 'eye-icon-confirm')" aria-label="Show password">
                                <i class="bi bi-eye" id="eye-icon-confirm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Anti-Bot Math Captcha -->
                <div class="form-floating mt-1">
                    <input type="number" name="captcha" id="captcha" class="form-control" placeholder="Jawaban Keamanan" required autocomplete="off">
                    <label for="captcha"><i class="bi bi-shield-check me-1 text-primary"></i>Verifikasi Keamanan: <?= e($captcha_question) ?> *</label>
                </div>

                <button type="submit" class="btn btn-auth mt-3">
                    <i class="bi bi-person-plus-fill me-1"></i>Daftarkan Akun
                </button>
            </form>

            <div class="text-center mt-3 small text-muted">
                Sudah memiliki akun? <a href="<?= site_url('login') ?>" class="text-primary fw-semibold text-decoration-none">Masuk di sini</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePass(fieldId, iconId) {
    var p = document.getElementById(fieldId);
    var i = document.getElementById(iconId);
    if (!p || !i) return;
    if (p.type === 'password') { p.type = 'text'; i.classList.replace('bi-eye', 'bi-eye-slash'); }
    else { p.type = 'password'; i.classList.replace('bi-eye-slash', 'bi-eye'); }
}
</script>
</body>
</html>