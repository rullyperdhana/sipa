<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<title><?= e($title ?? 'Login - SIPA Kabupaten Tapin') ?></title>

<!-- Google Fonts: Plus Jakarta Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Bootstrap 5.3 & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

<style>
:root {
    --primary: #0284c7;
    --primary-dark: #0369a1;
    --primary-deep: #0f2742;
    --accent: #f59e0b;
    --accent-emerald: #10b981;
    --bg-dark-1: #071526;
    --bg-dark-2: #0d2744;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: 
        radial-gradient(circle at 12% 18%, rgba(14, 165, 233, 0.22), transparent 42%),
        radial-gradient(circle at 88% 82%, rgba(245, 158, 11, 0.14), transparent 45%),
        radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.08), transparent 50%),
        linear-gradient(135deg, var(--bg-dark-1) 0%, var(--bg-dark-2) 55%, #05101d 100%);
    background-attachment: fixed;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 20px;
    position: relative;
    overflow-x: hidden;
    color: #334155;
}

/* Background grid overlay */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
}

/* Ambient glowing orbs */
.ambient-orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
    z-index: 0;
    opacity: 0.6;
    animation: floatOrb 16s ease-in-out infinite alternate;
}
.ambient-orb-1 {
    width: 380px;
    height: 380px;
    background: rgba(14, 165, 233, 0.18);
    top: -100px;
    left: -100px;
}
.ambient-orb-2 {
    width: 320px;
    height: 320px;
    background: rgba(245, 158, 11, 0.12);
    bottom: -80px;
    right: -80px;
    animation-delay: -6s;
}
@keyframes floatOrb {
    0% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, 40px) scale(1.08); }
    100% { transform: translate(-20px, 20px) scale(0.95); }
}

.login-wrapper {
    width: 100%;
    max-width: 1060px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin: auto;
    position: relative;
    z-index: 1;
}

/* ===== Schedule Banner with Countdown ===== */
.schedule-banner-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 20px;
    box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.schedule-header {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #ffffff;
    padding: 12px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.pulse-dot {
    display: inline-block;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #ffffff;
    animation: sipaPulse 1.6s infinite;
}
@keyframes sipaPulse {
    0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
    70% { transform: scale(1.15); box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
    100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}

.sipa-countdown-wrapper {
    display: flex;
    align-items: center;
    gap: 7px;
}
.sipa-countdown-unit {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    min-width: 52px;
    padding: 7px 6px 5px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}
.sipa-countdown-num {
    font-family: 'Plus Jakarta Sans', monospace;
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}
.sipa-countdown-label {
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-top: 4px;
    letter-spacing: 0.5px;
}
.sipa-countdown-sep {
    font-size: 20px;
    font-weight: 800;
    color: #94a3b8;
    align-self: center;
    margin-top: -10px;
}
.sipa-countdown-wrapper.urgent .sipa-countdown-unit {
    border-color: #fca5a5 !important;
    background: #fff5f5 !important;
    animation: urgentPulse 1.8s infinite;
}
.sipa-countdown-wrapper.urgent .sipa-countdown-num {
    color: #dc2626 !important;
}
.sipa-countdown-wrapper.urgent .sipa-countdown-label {
    color: #ef4444 !important;
}
@keyframes urgentPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
    50% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
}

/* ===== Main Login Container (Glassmorphism) ===== */
.login-container {
    background: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 24px;
    box-shadow: 0 30px 80px -15px rgba(0, 0, 0, 0.55), 0 0 50px rgba(14, 165, 233, 0.1);
    overflow: hidden;
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    min-height: 620px;
}

/* Left Brand Panel */
.login-banner {
    background: 
        radial-gradient(circle at 15% 20%, rgba(14, 165, 233, 0.28), transparent 45%),
        radial-gradient(circle at 85% 85%, rgba(245, 158, 11, 0.2), transparent 50%),
        linear-gradient(135deg, #092038 0%, #103357 55%, #0d2847 100%);
    color: #ffffff;
    padding: 56px 44px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.login-banner::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.08), transparent 60%);
    pointer-events: none;
}
.login-banner > * { position: relative; z-index: 2; }

/* Brand Badge */
.brand-badge-wrapper {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
}
.banner-logo {
    width: 74px;
    height: 74px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.08));
    border: 1.5px solid rgba(255, 255, 255, 0.35);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35), inset 0 0 15px rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(12px);
    flex-shrink: 0;
    padding: 7px;
}
.tapin-logo-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.4));
}
.brand-text-header .gov-title {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #fcd34d;
    margin-bottom: 2px;
}
.brand-text-header .app-name {
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.05;
    background: linear-gradient(135deg, #ffffff 30%, #e0f2fe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.banner-subtitle {
    font-size: 14px;
    line-height: 1.6;
    color: #cbd5e1;
    margin-bottom: 28px;
}
.banner-subtitle strong {
    color: #ffffff;
}

/* Feature Cards (Pill Cards) */
.feature-cards {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 28px;
}
.feature-card-item {
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    backdrop-filter: blur(10px);
    transition: all 0.25s ease;
}
.feature-card-item:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.25);
    transform: translateX(4px);
}
.feature-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
}
.feature-icon-blue { background: rgba(14, 165, 233, 0.25); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.4); }
.feature-icon-amber { background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
.feature-icon-emerald { background: rgba(16, 185, 129, 0.25); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }

.feature-info .title {
    font-size: 13.5px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 2px;
}
.feature-info .desc {
    font-size: 11.5px;
    color: #94a3b8;
    line-height: 1.35;
}

.banner-footer-secure {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11.5px;
    color: #94a3b8;
    padding-top: 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.banner-footer-secure .status-light {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
}

/* Right Login Form Panel */
.login-form {
    padding: 56px 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #ffffff;
}
.form-title {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.4px;
    margin: 0 0 6px;
}
.form-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin-bottom: 28px;
    line-height: 1.5;
}

/* Modern Form Controls */
.form-floating {
    position: relative;
}
.form-floating > .form-control,
.form-floating > .form-select {
    border-radius: 12px;
    height: 58px;
    min-height: 58px;
    border: 1.5px solid #e2e8f0;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    transition: all 0.2s ease;
    background-color: #f8fafc;
}

/* Base input paddings with room for left icon (44px) */
.form-floating > .form-control {
    padding: 1rem 1rem 1rem 44px;
}
.form-floating > .form-control:focus,
.form-floating > .form-control:not(:placeholder-shown) {
    padding-top: 1.625rem;
    padding-bottom: 0.625rem;
    padding-left: 44px;
    padding-right: 1rem;
}
.form-floating > #password {
    padding-right: 46px;
}

/* Dedicated styling for Select Dropdown (Tahun Anggaran) to ensure perfect vertical symmetry & zero label collision */
.form-floating > .form-select {
    padding-top: 1.625rem !important;
    padding-bottom: 0.625rem !important;
    padding-left: 44px !important;
    padding-right: 36px !important;
    line-height: 1.35;
    background-position: right 14px center;
}

.form-floating > .form-control:focus,
.form-floating > .form-select:focus {
    border-color: var(--primary);
    background-color: #ffffff;
    box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
}

/* Unified Floating Labels aligned with 44px icon offset */
.form-floating > label {
    position: absolute;
    top: 0;
    left: 44px;
    width: auto;
    max-width: calc(100% - 54px);
    height: 100%;
    padding: 18px 0;
    color: #64748b;
    font-size: 13.5px;
    font-weight: 500;
    pointer-events: none;
    border: none;
    transform-origin: 0 0;
    transition: opacity 0.15s ease-in-out, transform 0.15s ease-in-out;
}

/* Shrunk label state: strictly horizontally aligned with left: 44px and clean top breathing room */
.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label,
.form-floating > .form-select ~ label {
    opacity: 0.75;
    transform: scale(0.80) translateY(-0.6rem);
}

.input-icon-lead {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 17px;
    color: #94a3b8;
    z-index: 5;
    pointer-events: none;
    transition: color 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
}
.form-floating:focus-within .input-icon-lead {
    color: var(--primary);
}

.password-toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: 0;
    color: #94a3b8;
    cursor: pointer;
    z-index: 10;
    font-size: 17px;
    padding: 6px;
    border-radius: 8px;
    transition: color 0.15s ease, background 0.15s ease;
}
.password-toggle:hover {
    color: #0f172a;
    background: #f1f5f9;
}

.btn-login {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    border: 0;
    padding: 14px;
    border-radius: 12px;
    font-weight: 700;
    width: 100%;
    transition: all 0.25s ease;
    font-size: 15px;
    box-shadow: 0 4px 15px rgba(2, 132, 199, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-login:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    transform: translateY(-1.5px);
    box-shadow: 0 8px 24px rgba(2, 132, 199, 0.45);
    color: #ffffff;
}
.btn-login:active {
    transform: translateY(0);
}

.form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .login-container {
        grid-template-columns: 1fr;
        max-width: 500px;
    }
    .login-banner {
        padding: 36px 30px;
        min-height: auto;
    }
    .feature-cards {
        display: none; /* Hide feature cards on mobile for compact layout */
    }
    .login-form {
        padding: 36px 30px;
    }
    .login-wrapper {
        max-width: 500px;
    }
}
</style>
</head>
<body>

<!-- Ambient background lighting -->
<div class="ambient-orb ambient-orb-1"></div>
<div class="ambient-orb ambient-orb-2"></div>

<div class="login-wrapper">

    <?php if (!empty($active_schedules)): ?>
    <!-- Banner Jadwal Pengusulan Aktif (Publik SKPD) -->
    <div class="schedule-banner-card">
        <div class="schedule-header">
            <div class="d-flex align-items-center gap-2">
                <span class="pulse-dot"></span>
                <strong class="text-uppercase" style="letter-spacing: 0.6px; font-size: 12px;">
                    <i class="bi bi-broadcast me-1"></i> Jadwal Pengusulan Resmi Sedang Berjalan (Terbuka)
                </strong>
            </div>
            <span class="badge bg-white text-success fw-bold px-2.5 py-1" style="font-size: 11px;">
                <i class="bi bi-clock-history me-1"></i><?= count($active_schedules) ?> Jadwal Aktif
            </span>
        </div>
        <div class="p-3 p-md-4">
            <?php foreach ($active_schedules as $idx => $sch): ?>
            <?php if ($idx > 0): ?><hr class="my-3 text-muted opacity-25"><?php endif; ?>
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                        <span class="badge bg-<?= $sch['badge_color'] ?> text-white fw-semibold px-2 py-1">
                            <i class="bi <?= $sch['icon'] ?> me-1"></i><?= $sch['kategori'] ?>
                        </span>
                        <span class="badge bg-light text-dark border fw-bold px-2 py-1">
                            TA <?= $sch['tahun_anggaran'] ?>
                        </span>
                        <span class="text-success fw-semibold small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-unlock-fill"></i> Dibuka
                        </span>
                    </div>
                    <h5 class="fw-bold mb-1 text-dark" style="font-size: 16px;">
                        <?= e($sch['nama_jadwal']) ?>
                    </h5>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar2-range me-1 text-primary"></i>
                        <?= !empty($sch['tanggal_mulai']) ? date('d M Y', strtotime($sch['tanggal_mulai'])) : '' ?> s.d 
                        <strong class="text-dark"><?= date('d M Y', strtotime($sch['tanggal_selesai'])) ?> (23:59 WITA)</strong>
                        <?php if (!empty($sch['keterangan'])): ?>
                            &bull; <span class="text-secondary"><?= e($sch['keterangan']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="d-inline-flex flex-column align-items-lg-end">
                        <div class="text-uppercase fw-bold text-muted mb-1.5" style="font-size: 10px; letter-spacing: 0.5px;">
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
        <div class="bg-light py-2 px-3 text-muted small border-top d-flex flex-wrap align-items-center justify-content-between gap-2" style="font-size: 12px;">
            <div>
                <i class="bi bi-info-circle text-primary me-1"></i>
                SKPD diimbau segera menyelesaikan usulan dan mengunggah berkas surat sebelum batas waktu berakhir.
            </div>
            <div class="d-none d-md-flex align-items-center text-secondary fw-semibold">
                <img src="<?= base_url('assets/img/logo_tapin.png') ?>" alt="Logo Kabupaten Tapin" style="height: 20px; width: auto; margin-right: 6px;">
                BKAD Kabupaten Tapin
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main 2-Column Login Container -->
    <div class="login-container">
        
        <!-- Left Brand & Information Panel -->
        <div class="login-banner">
            <div>
                <div class="brand-badge-wrapper">
                    <div class="banner-logo">
                        <img src="<?= base_url('assets/img/logo_tapin.png') ?>" alt="Lambang Kabupaten Tapin" class="tapin-logo-img">
                    </div>
                    <div class="brand-text-header">
                        <div class="gov-title">Pemerintah Kabupaten Tapin</div>
                        <div class="app-name">SIPA</div>
                    </div>
                </div>

                <div class="banner-subtitle">
                    <strong>Sistem Informasi Pengelolaan Aset</strong><br>
                    Portal resmi perencanaan, standardisasi harga (SSH/SBU), dan verifikasi aset daerah terpadu BKAD Kabupaten Tapin.
                </div>

                <!-- Feature Pills -->
                <div class="feature-cards">
                    <div class="feature-card-item">
                        <div class="feature-icon-box feature-icon-blue">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <div class="feature-info">
                            <div class="title">Standar Satuan Harga (SSH &amp; SBU)</div>
                            <div class="desc">Sinkronisasi kode barang resmi &amp; ekspor format SIPD RI.</div>
                        </div>
                    </div>
                    <div class="feature-card-item">
                        <div class="feature-icon-box feature-icon-amber">
                            <i class="bi bi-clipboard2-check-fill"></i>
                        </div>
                        <div class="feature-info">
                            <div class="title">Perencanaan Aset RKBMD</div>
                            <div class="desc">Pengadaan, pemeliharaan, pemanfaatan &amp; penghapusan BMD.</div>
                        </div>
                    </div>
                    <div class="feature-card-item">
                        <div class="feature-icon-box feature-icon-emerald">
                            <i class="bi bi-telegram"></i>
                        </div>
                        <div class="feature-info">
                            <div class="title">Notifikasi Instan Multi-Kanal</div>
                            <div class="desc">Pemberitahuan otomatis real-time ke Telegram &amp; WhatsApp.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Security Indicator -->
            <div class="banner-footer-secure">
                <span class="status-light"></span>
                <span>Akses aman terenkripsi SSL 256-Bit • Sesi terproteksi CSRF</span>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="login-form">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 11px;">
                        <i class="bi bi-shield-check me-1"></i>Portal Autentikasi Pengguna
                    </span>
                    <img src="<?= base_url('assets/img/logo_tapin.png') ?>" alt="Logo Tapin" class="d-lg-none" style="height: 32px; width: auto;" title="Pemerintah Kabupaten Tapin">
                </div>
                <h1 class="form-title">Masuk ke Sistem</h1>
                <p class="form-subtitle">Silakan masuk dengan akun resmi yang telah terdaftar di BKAD Kabupaten Tapin.</p>
            </div>

            <!-- Flash Session Alerts -->
            <?php if ($msg = $this->session->flashdata('danger')): ?>
                <div class="alert alert-danger py-2.5 px-3 rounded-3 d-flex align-items-center mb-3" style="font-size: 13px;">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2 flex-shrink-0"></i>
                    <div><?= $msg ?></div>
                </div>
            <?php endif; ?>
            <?php if ($msg = $this->session->flashdata('success')): ?>
                <div class="alert alert-success py-2.5 px-3 rounded-3 d-flex align-items-center mb-3" style="font-size: 13px;">
                    <i class="bi bi-check-circle-fill text-success fs-5 me-2 flex-shrink-0"></i>
                    <div><?= e($msg) ?></div>
                </div>
            <?php endif; ?>
            <?php if ($msg = $this->session->flashdata('warning')): ?>
                <div class="alert alert-warning py-2.5 px-3 rounded-3 d-flex align-items-center mb-3" style="font-size: 13px;">
                    <i class="bi bi-info-circle-fill text-warning fs-5 me-2 flex-shrink-0"></i>
                    <div><?= e($msg) ?></div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="<?= site_url('login') ?>" autocomplete="off" id="formLogin" novalidate>
                <?= csrf_input() ?>

                <!-- Input Username -->
                <div class="form-floating position-relative mb-3">
                    <i class="bi bi-person-badge input-icon-lead"></i>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Username Anda"
                           value="<?= e(set_value('username')) ?>" required maxlength="50" autofocus>
                    <label for="username">Username</label>
                </div>

                <!-- Input Password -->
                <div class="form-floating position-relative mb-3">
                    <i class="bi bi-shield-lock input-icon-lead"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Kata Sandi"
                           required maxlength="200">
                    <label for="password">Kata Sandi</label>
                    <button type="button" class="password-toggle" onclick="togglePass()" aria-label="Lihat / Sembunyikan Kata Sandi" title="Lihat/Sembunyikan Password">
                        <i class="bi bi-eye" id="eye-icon"></i>
                    </button>
                </div>

                <!-- Dropdown Tahun Anggaran -->
                <div class="form-floating position-relative mb-3">
                    <i class="bi bi-calendar2-range-fill input-icon-lead text-primary"></i>
                    <select name="tahun_anggaran" id="tahun_anggaran" class="form-select fw-semibold text-primary" required>
                        <?php 
                        $years = !empty($available_years) ? $available_years : [2028, 2027, 2026, 2025];
                        $curSel = !empty($selected_year) ? (int)$selected_year : 2027;
                        foreach ($years as $y): 
                            $tag = '';
                            if ($y == 2027) $tag = ' ⭐ (Perencanaan)';
                            elseif ($y == (int)date('Y')) $tag = ' (Berjalan)';
                            elseif ($y < (int)date('Y')) $tag = ' (Arsip)';
                        ?>
                            <option value="<?= $y ?>" <?= ($y === $curSel) ? 'selected' : '' ?>>
                                Tahun Anggaran <?= $y ?><?= $tag ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="tahun_anggaran">Tahun Anggaran Periode</label>
                </div>

                <!-- Input Captcha Keamanan jika gagal berulang -->
                <?php if (!empty($show_captcha)): ?>
                <div class="form-floating position-relative mb-3">
                    <i class="bi bi-shield-check input-icon-lead text-warning"></i>
                    <input type="number" name="captcha" id="captcha" class="form-control border-warning bg-warning-subtle" placeholder="Jawaban Keamanan" required autocomplete="off">
                    <label for="captcha">Verifikasi Keamanan: <?= e($captcha_question) ?></label>
                </div>
                <?php endif; ?>

                <!-- Remember Me & Helpdesk Link -->
                <div class="d-flex justify-content-between align-items-center mb-4 pt-1">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" style="cursor: pointer;">
                        <label class="form-check-label text-muted small user-select-none" for="remember" style="cursor: pointer;">
                            Ingat saya di perangkat ini
                        </label>
                    </div>
                    <a href="javascript:void(0)" class="text-primary fw-semibold small text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalHelpdesk">
                        <i class="bi bi-question-circle me-1"></i>Bantuan Operator?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-login" id="btnSubmitLogin">
                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                    <span id="btnSubmitText">Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Registration Notice -->
            <div class="text-center mt-3 text-muted" style="font-size: 12.5px;">
                <?php if (!empty($registration_enabled)): ?>
                    Belum memiliki akun operator? <a href="<?= site_url('register') ?>" class="text-primary fw-bold text-decoration-none">Daftar di sini</a>
                <?php else: ?>
                    <span>Pendaftaran mandiri ditutup. Akun diterbitkan oleh Administrator BKAD.</span>
                <?php endif; ?>
            </div>

            <!-- Footer info -->
            <div class="text-center mt-4 text-muted small pt-2 border-top" style="font-size: 11.5px;">
                Badan Pengelolaan Keuangan dan Aset Daerah &bull; Kabupaten Tapin
                <div class="mt-0.5 opacity-75">
                    SIPA v<?= $this->config->item('app_version') ?? '2.10.2' ?> &copy; <?= date('Y') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bantuan Operator SKPD -->
<div class="modal fade" id="modalHelpdesk" tabindex="-1" aria-labelledby="modalHelpdeskLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <img src="<?= base_url('assets/img/logo_tapin.png') ?>" alt="Logo Tapin" style="height: 38px; width: auto;" class="p-1 bg-white rounded-2 shadow-sm me-1">
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="modalHelpdeskLabel">Layanan Bantuan Operator SKPD</h6>
                        <small class="opacity-75" style="font-size: 11px;">BKAD Kabupaten Tapin</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary small mb-3">
                    Jika Anda mengalami kendala saat masuk ke sistem, lupa kata sandi, atau memerlukan verifikasi akun baru SKPD, silakan hubungi tim administrator/verifikator BKAD melalui kanal resmi berikut:
                </p>

                <div class="vstack gap-2.5">
                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 bg-success-subtle text-success rounded-circle fs-4">
                                <i class="bi bi-whatsapp"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">Helpdesk WhatsApp BKAD</div>
                                <div class="text-muted" style="font-size: 12px;">Konsultasi usulan &amp; reset sandi</div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Aktif</span>
                    </div>

                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 bg-info-subtle text-info rounded-circle fs-4">
                                <i class="bi bi-telegram"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">Bot Notifikasi Telegram SIPA</div>
                                <div class="text-muted" style="font-size: 12px;">Pemberitahuan telaah berkas usulan</div>
                            </div>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 small">Real-time</span>
                    </div>

                    <div class="p-3 rounded-3 bg-light border">
                        <div class="fw-bold text-dark small mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Kantor Pelayanan:</div>
                        <div class="text-muted small">
                            Bidang Pengelolaan Aset Daerah - BKAD Kabupaten Tapin<br>
                            Kawasan Perkantoran Jl. Brigjen H. Hasan Basry, Rantau.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 border-top">
                <button type="button" class="btn btn-secondary btn-sm px-3 fw-semibold rounded-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Toggle Password Visibility
function togglePass() {
    var p = document.getElementById('password');
    var i = document.getElementById('eye-icon');
    if (!p || !i) return;
    if (p.type === 'password') { 
        p.type = 'text'; 
        i.classList.replace('bi-eye', 'bi-eye-slash'); 
    } else { 
        p.type = 'password'; 
        i.classList.replace('bi-eye-slash', 'bi-eye'); 
    }
}

// Form Submit State Spinner
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formLogin');
    var btn = document.getElementById('btnSubmitLogin');
    var btnText = document.getElementById('btnSubmitText');

    if (form && btn) {
        form.addEventListener('submit', function() {
            var user = document.getElementById('username');
            var pass = document.getElementById('password');
            if (user && pass && user.value.trim() && pass.value) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span><span>Memverifikasi Akun...</span>';
                // Lanjutkan submit form secara normal
                setTimeout(function() { form.submit(); }, 50);
            }
        });
    }

    // Countdown Timer logic
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
