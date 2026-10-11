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

.login-wrapper {
    width: 100%;
    max-width: 980px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin: auto;
}

/* ===== Schedule Active Banner with Countdown (Login Page) ===== */
.pulse-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ffffff;
    animation: sipaPulse 1.6s infinite;
}
@keyframes sipaPulse {
    0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
    100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}
.sipa-countdown-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
}
.sipa-countdown-unit {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    min-width: 50px;
    padding: 6px 6px 4px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}
.sipa-countdown-num {
    font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace;
    font-size: 19px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}
.sipa-countdown-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-top: 3px;
    letter-spacing: 0.5px;
}
.sipa-countdown-sep {
    font-size: 18px;
    font-weight: 700;
    color: #94a3b8;
    align-self: center;
    margin-top: -10px;
}
.sipa-countdown-wrapper.urgent .sipa-countdown-unit {
    border-color: #fca5a5 !important;
    background: #fff5f5 !important;
}
.sipa-countdown-wrapper.urgent .sipa-countdown-num {
    color: #dc2626 !important;
}
.sipa-countdown-wrapper.urgent .sipa-countdown-label {
    color: #ef4444 !important;
}

@media (max-width: 800px) {
    .login-container { grid-template-columns: 1fr; max-width: 480px; }
    .login-banner { padding: 32px; min-height: auto; }
    .login-form { padding: 32px; }
    .login-wrapper { max-width: 480px; }
}
</style>
</head>
<body>

<div class="login-wrapper">

    <?php if (!empty($active_schedules)): ?>
    <!-- Banner Jadwal Pengusulan Aktif (Publik SKPD) -->
    <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 16px; background: #ffffff;">
        <div class="card-header bg-success text-white py-2.5 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="pulse-dot"></span>
                <strong class="text-uppercase" style="letter-spacing: 0.5px; font-size: 12.5px;">
                    <i class="bi bi-broadcast me-1"></i> Jadwal Pengusulan Sedang Berjalan (Terbuka)
                </strong>
            </div>
            <span class="badge bg-white text-success fw-bold px-2.5 py-1">
                <?= count($active_schedules) ?> Jadwal Dibuka
            </span>
        </div>
        <div class="card-body p-3 p-md-4">
            <?php foreach ($active_schedules as $idx => $sch): ?>
            <?php if ($idx > 0): ?><hr class="my-3 text-muted opacity-25"><?php endif; ?>
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                        <span class="badge bg-<?= $sch['badge_color'] ?> text-white fw-semibold">
                            <i class="bi <?= $sch['icon'] ?> me-1"></i><?= $sch['kategori'] ?>
                        </span>
                        <span class="badge bg-light text-dark border">
                            TA <?= $sch['tahun_anggaran'] ?>
                        </span>
                        <span class="text-success fw-semibold small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-unlock-fill"></i> Dibuka Resmi
                        </span>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark" style="font-size: 16.5px;">
                        <?= e($sch['nama_jadwal']) ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar2-range me-1 text-primary"></i>
                        <?= !empty($sch['tanggal_mulai']) ? date('d M Y', strtotime($sch['tanggal_mulai'])) : '' ?> s.d 
                        <strong><?= date('d M Y', strtotime($sch['tanggal_selesai'])) ?> (23:59 WITA)</strong>
                        <?php if (!empty($sch['keterangan'])): ?>
                            &bull; <span class="text-secondary"><?= e($sch['keterangan']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="d-inline-flex flex-column align-items-lg-end">
                        <div class="text-uppercase fw-bold text-muted mb-1.5" style="font-size: 10.5px; letter-spacing: 0.5px;">
                            <i class="bi bi-hourglass-split text-warning me-1"></i> Sisa Waktu Pengusulan:
                        </div>
                        <div class="sipa-countdown-wrapper login-countdown" data-target="<?= $sch['target_iso'] ?>">
                            <div class="sipa-countdown-unit">
                                <div class="sipa-countdown-num cd-val-days">00</div>
                                <div class="sipa-countdown-label">Hari</div>
                            </div>
                            <div class="sipa-countdown-sep">:</div>
                            <div class="sipa-countdown-unit">
                                <div class="sipa-countdown-num cd-val-hours">00</div>
                                <div class="sipa-countdown-label">Jam</div>
                            </div>
                            <div class="sipa-countdown-sep">:</div>
                            <div class="sipa-countdown-unit">
                                <div class="sipa-countdown-num cd-val-mins">00</div>
                                <div class="sipa-countdown-label">Menit</div>
                            </div>
                            <div class="sipa-countdown-sep">:</div>
                            <div class="sipa-countdown-unit">
                                <div class="sipa-countdown-num cd-val-secs">00</div>
                                <div class="sipa-countdown-label">Detik</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="card-footer bg-light py-2 px-3 text-muted small border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <i class="bi bi-info-circle text-primary me-1"></i>
                SKPD diimbau segera masuk ke sistem dan mengunggah berkas surat usulan resmi sebelum batas waktu berakhir.
            </div>
            <div class="d-none d-md-block text-secondary">
                BPKAD Kabupaten Tapin
            </div>
        </div>
    </div>
    <?php endif; ?>

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

            <div class="form-floating mb-3">
                <select name="tahun_anggaran" id="tahun_anggaran" class="form-select fw-semibold text-primary" required>
                    <?php 
                    $years = !empty($available_years) ? $available_years : [2028, 2027, 2026, 2025];
                    $curSel = !empty($selected_year) ? (int)$selected_year : 2027;
                    foreach ($years as $y): 
                        $tag = '';
                        if ($y == 2027) $tag = ' (Perencanaan)';
                        elseif ($y == (int)date('Y')) $tag = ' (Berjalan)';
                        elseif ($y < (int)date('Y')) $tag = ' (Arsip)';
                    ?>
                        <option value="<?= $y ?>" <?= ($y === $curSel) ? 'selected' : '' ?>>
                            TA <?= $y ?><?= $tag ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="tahun_anggaran"><i class="bi bi-calendar2-range-fill me-2 text-primary"></i>Tahun Anggaran Periode</label>
            </div>

            <?php if (!empty($show_captcha)): ?>
            <div class="form-floating mb-3">
                <input type="number" name="captcha" id="captcha" class="form-control border-warning" placeholder="Jawaban Keamanan" required autocomplete="off">
                <label for="captcha"><i class="bi bi-shield-check me-2 text-warning"></i>Keamanan: <?= e($captcha_question) ?></label>
            </div>
            <?php endif; ?>

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

        <div class="text-center mt-3 small text-muted">
            <?php if (!empty($registration_enabled)): ?>
                Belum punya akun? <a href="<?= site_url('register') ?>" class="text-primary fw-semibold text-decoration-none">Daftar di sini</a>
            <?php else: ?>
                <span>Pendaftaran akun mandiri ditutup. Hubungi administrator BPKAD.</span>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4 small text-muted">
            Butuh bantuan? Hubungi administrator BPKAD Kabupaten Tapin.
            <div class="mt-1 opacity-75">v<?= $this->config->item('app_version') ?> &copy; <?= date('Y') ?></div>
        </div>
    </div>
</div>
</div><!-- /login-wrapper -->

<script>
function togglePass() {
    var p = document.getElementById('password');
    var i = document.getElementById('eye-icon');
    if (p.type === 'password') { p.type = 'text'; i.classList.replace('bi-eye', 'bi-eye-slash'); }
    else { p.type = 'password'; i.classList.replace('bi-eye-slash', 'bi-eye'); }
}

document.addEventListener('DOMContentLoaded', function () {
    const countdownEls = document.querySelectorAll('.login-countdown');
    if (!countdownEls.length) return;

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    function updateAllCountdowns() {
        const now = new Date().getTime();
        countdownEls.forEach(function (el) {
            const targetStr = el.getAttribute('data-target');
            if (!targetStr) return;
            const targetTime = new Date(targetStr).getTime();
            if (isNaN(targetTime)) return;

            const diff = targetTime - now;
            const daysEl = el.querySelector('.cd-val-days');
            const hoursEl = el.querySelector('.cd-val-hours');
            const minsEl = el.querySelector('.cd-val-mins');
            const secsEl = el.querySelector('.cd-val-secs');

            if (diff <= 0) {
                el.innerHTML = '<span class="badge bg-danger px-3 py-2 fw-bold fs-6 shadow-sm"><i class="bi bi-alarm-fill me-1"></i> Batas Waktu Telah Berakhir</span>';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diff % (1000 * 60)) / 1000);

            if (daysEl) daysEl.textContent = pad(days);
            if (hoursEl) hoursEl.textContent = pad(hours);
            if (minsEl) minsEl.textContent = pad(mins);
            if (secsEl) secsEl.textContent = pad(secs);

            if (diff < 24 * 60 * 60 * 1000) {
                el.classList.add('urgent');
            }
        });
    }

    updateAllCountdowns();
    setInterval(updateAllCountdowns, 1000);
});
</script>
</body>
</html>
