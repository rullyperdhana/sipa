<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Login') ?></title>
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
    padding: 20px;
}
.login-container {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    width: 100%;
    max-width: 980px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 580px;
}
.login-banner {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff;
    padding: 60px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.login-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 20% 80%, rgba(245,158,11,.2), transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,.08), transparent 50%);
}
.login-banner > * { position: relative; z-index: 1; }
.banner-logo {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,.15);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    margin-bottom: 28px;
    backdrop-filter: blur(10px);
}
.banner-title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 12px;
    letter-spacing: -.5px;
}
.banner-subtitle {
    opacity: .9;
    line-height: 1.6;
    margin-bottom: 24px;
}
.feature-list { list-style: none; padding: 0; margin: 0; }
.feature-list li {
    padding: 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    opacity: .9;
}
.feature-list i { color: var(--accent); }

.login-form {
    padding: 60px 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.form-title {
    font-size: 26px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px;
}
.form-subtitle {
    color: #6b7280;
    margin-bottom: 32px;
}
.form-floating > .form-control {
    border-radius: 10px;
    padding: 16px;
    height: 58px;
    border-color: #e5e7eb;
}
.form-floating > .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(30,96,145,.1);
}
.form-floating { margin-bottom: 16px; }
.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: 0;
    color: #6b7280;
    cursor: pointer;
    z-index: 10; /* Ensure it's above form-control */
}
.btn-login {
    background: var(--primary);
    color: #fff;
    border: 0;
    padding: 14px;
    border-radius: 10px;
    font-weight: 600;
    width: 100%;
    transition: all .2s;
    font-size: 15px;
}
.btn-login:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 8px 20px rgba(30,96,145,.3); }

.alert { border-radius: 10px; border: 0; }

@media (max-width: 800px) {
    .login-container { grid-template-columns: 1fr; max-width: 480px; }
    .login-banner { padding: 32px; min-height: auto; }
    .login-form { padding: 32px; }
}
</style>
</head>
<body>

<div class="login-container">
    <div class="login-banner">
        <div>
            <div class="banner-logo"><i class="bi bi-building-fill-check"></i></div>
            <h1 class="banner-title">SIPA</h1>
            <p class="banner-subtitle">
                Sistem Informasi Pengelolaan Aset<br>
                <strong>Pemerintah Kabupaten Tapin</strong>
            </p>
            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill"></i> Pengadaan, Pemeliharaan, Pemanfaatan</li>
                <li><i class="bi bi-check-circle-fill"></i> Pemindahtanganan & Penghapusan</li>
                <li><i class="bi bi-check-circle-fill"></i> Verifikasi Online oleh BPKAD</li>
                <li><i class="bi bi-check-circle-fill"></i> Rekap & Cetak Laporan Otomatis</li>
            </ul>
        </div>
        <div style="opacity:.7; font-size: 13px;">
            <i class="bi bi-shield-lock-fill"></i> Akses sistem dilindungi dan dipantau.
        </div>
    </div>

    <div class="login-form">
        <h2 class="form-title">Masuk Sistem</h2>
        <p class="form-subtitle">Silakan masuk dengan akun yang telah diberikan oleh administrator BPKAD.</p>

        <?php if ($msg = $this->session->flashdata('danger')): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($msg = $this->session->flashdata('success')): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = $this->session->flashdata('warning')): ?>
            <div class="alert alert-warning"><i class="bi bi-info-circle me-2"></i><?= e($msg) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('login') ?>" autocomplete="off" novalidate>
            <?= csrf_input() ?>

            <div class="form-floating">
                <input type="text" name="username" id="username" class="form-control" placeholder="Username"
                       value="<?= e(set_value('username')) ?>" required maxlength="50" autofocus>
                <label for="username"><i class="bi bi-person me-2"></i>Username</label>
            </div>

            <div class="form-floating position-relative">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password"
                       required maxlength="200">
                <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                <button type="button" class="password-toggle" onclick="togglePass()" aria-label="Show password">
                    <i class="bi bi-eye" id="eye-icon"></i>
                </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <div class="text-center mt-3 small">
            Belum punya akun? <a href="<?= site_url('register') ?>" class="text-decoration-none">Daftar di sini</a>
        </div>

        <div class="text-center mt-4 small text-muted">
            Butuh bantuan? Hubungi administrator BPKAD Kabupaten Tapin.
        </div>
    </div>
</div>

<script>
function togglePass() {
    var p = document.getElementById('password');
    var i = document.getElementById('eye-icon');
    if (p.type === 'password') { p.type = 'text'; i.classList.replace('bi-eye', 'bi-eye-slash'); }
    else { p.type = 'password'; i.classList.replace('bi-eye-slash', 'bi-eye'); }
}
</script>
</body>
</html>
