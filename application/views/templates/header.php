<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<meta name="csrf-token" content="<?= e($this->security->get_csrf_hash()) ?>">
<meta name="csrf-name" content="<?= e($this->security->get_csrf_token_name()) ?>">
<title><?= e($title ?? 'SIPA') ?></title>

<!-- Bootstrap 5.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<!-- Custom CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<script>
(function() {
    try {
        if (localStorage.getItem('sipa_sidebar_collapsed') === '1' && window.innerWidth >= 992) {
            document.body.classList.add('sidebar-collapsed');
        }
    } catch (e) {}
})();
</script>

<?php $user = $this->auth->user(); ?>

<!-- ========== SIDEBAR ========== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-building-fill-check"></i>
        </div>
        <div class="brand-text flex-grow-1">
            <span class="brand-name">SIPA</span>
            <small>Kabupaten Tapin</small>
        </div>
        <button type="button" class="btn btn-link d-lg-none ms-auto p-0" id="sidebarClose" aria-label="Tutup Menu" title="Tutup Menu">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Menu Utama</div>
        <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $this->uri->segment(1) === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
        </a>

        <?php 
        $hasRkbmd = can_access('rkbmd_pengadaan') || can_access('rkbmd_pemeliharaan') || can_access('rkbmd_pemanfaatan') || can_access('rkbmd_pemindahtanganan') || can_access('rkbmd_penghapusan');
        if ($hasRkbmd && in_array($user->role, ['admin', 'skpd', 'operator_skpd', 'pimpinan'])): 
        ?>
        <div class="nav-section">RKBMD</div>
        <?php if (can_access('rkbmd_pengadaan')): ?>
        <a href="<?= site_url('rkbmd/pengadaan') ?>" class="nav-link <?= $this->uri->segment(2) === 'pengadaan' ? 'active' : '' ?>">
            <i class="bi bi-cart-plus-fill"></i> <span>Pengadaan</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('rkbmd_pemeliharaan')): ?>
        <a href="<?= site_url('rkbmd/pemeliharaan') ?>" class="nav-link <?= $this->uri->segment(2) === 'pemeliharaan' ? 'active' : '' ?>">
            <i class="bi bi-tools"></i> <span>Pemeliharaan</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('rkbmd_pemanfaatan')): ?>
        <a href="<?= site_url('rkbmd/pemanfaatan') ?>" class="nav-link <?= $this->uri->segment(2) === 'pemanfaatan' ? 'active' : '' ?>">
            <i class="bi bi-share-fill"></i> <span>Pemanfaatan</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('rkbmd_pemindahtanganan')): ?>
        <a href="<?= site_url('rkbmd/pemindahtanganan') ?>" class="nav-link <?= $this->uri->segment(2) === 'pemindahtanganan' ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right"></i> <span>Pemindahtanganan</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('rkbmd_penghapusan')): ?>
        <a href="<?= site_url('rkbmd/penghapusan') ?>" class="nav-link <?= $this->uri->segment(2) === 'penghapusan' ? 'active' : '' ?>">
            <i class="bi bi-trash3-fill"></i> <span>Penghapusan</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (can_access('verifikasi_rkbmd') && in_array($user->role, ['admin', 'verifikator'])): ?>
        <div class="nav-section">Verifikasi BPKAD</div>
        <a href="<?= site_url('verifikasi') ?>" class="nav-link <?= $this->uri->segment(1) === 'verifikasi' ? 'active' : '' ?>">
            <i class="bi bi-check2-square"></i> <span>Verifikasi Usulan</span>
        </a>
        <?php endif; ?>

        <!-- ========== MODUL 1: STANDAR SATUAN HARGA (SSH) ========== -->
        <?php if (can_access('ssh')): ?>
        <div class="nav-section">Standar Satuan Harga (SSH)</div>

        <?php if (in_array($user->role, ['operator_skpd', 'skpd', 'admin'])): ?>
        <a href="<?= site_url('ssh/usulan') ?>" class="nav-link <?= ($this->uri->segment(1) === 'ssh' && in_array($this->uri->segment(2), ['usulan', 'tambah', 'edit'])) ? 'active' : '' ?>">
            <i class="bi bi-box-seam-fill"></i> <span>Usulan SSH</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('verifikasi_standar') && in_array($user->role, ['verifikator', 'admin'])): ?>
        <a href="<?= site_url('ssh/verifikasi') ?>" class="nav-link <?= ($this->uri->segment(1) === 'ssh' && $this->uri->segment(2) === 'verifikasi') ? 'active' : '' ?>">
            <i class="bi bi-patch-check-fill"></i> <span>Verifikasi SSH</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('penetapan_standar') && in_array($user->role, ['penetap', 'pimpinan', 'admin'])): ?>
        <a href="<?= site_url('ssh/penetapan') ?>" class="nav-link <?= ($this->uri->segment(1) === 'ssh' && $this->uri->segment(2) === 'penetapan') ? 'active' : '' ?>">
            <i class="bi bi-award-fill"></i> <span>Penetapan SSH</span>
        </a>
        <?php endif; ?>

        <a href="<?= site_url('ssh/master_data') ?>" class="nav-link <?= ($this->uri->segment(1) === 'ssh' && $this->uri->segment(2) === 'master_data') ? 'active' : '' ?>">
            <i class="bi bi-journal-check"></i> <span>Master Data SSH</span>
        </a>

        <?php if (can_access('jadwal_standar') && in_array($user->role, ['admin', 'verifikator'])): ?>
        <a href="<?= site_url('ssh/jadwal') ?>" class="nav-link <?= ($this->uri->segment(1) === 'ssh' && $this->uri->segment(2) === 'jadwal') ? 'active' : '' ?>">
            <i class="bi bi-calendar-check-fill"></i> <span>Jadwal Pengusulan</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <!-- ========== MODUL 2: STANDAR BIAYA UMUM (SBU) ========== -->
        <?php if (can_access('sbu')): ?>
        <div class="nav-section">Standar Biaya Umum (SBU)</div>

        <?php if (in_array($user->role, ['operator_skpd', 'skpd', 'admin'])): ?>
        <a href="<?= site_url('sbu/usulan') ?>" class="nav-link <?= ($this->uri->segment(1) === 'sbu' && in_array($this->uri->segment(2), ['usulan', 'tambah', 'edit'])) ? 'active' : '' ?>">
            <i class="bi bi-receipt-cutoff"></i> <span>Usulan SBU</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('verifikasi_standar') && in_array($user->role, ['verifikator', 'admin'])): ?>
        <a href="<?= site_url('sbu/verifikasi') ?>" class="nav-link <?= ($this->uri->segment(1) === 'sbu' && $this->uri->segment(2) === 'verifikasi') ? 'active' : '' ?>">
            <i class="bi bi-check2-circle"></i> <span>Verifikasi SBU</span>
        </a>
        <?php endif; ?>

        <?php if (can_access('penetapan_standar') && in_array($user->role, ['penetap', 'pimpinan', 'admin'])): ?>
        <a href="<?= site_url('sbu/penetapan') ?>" class="nav-link <?= ($this->uri->segment(1) === 'sbu' && $this->uri->segment(2) === 'penetapan') ? 'active' : '' ?>">
            <i class="bi bi-shield-check"></i> <span>Penetapan SBU</span>
        </a>
        <?php endif; ?>

        <a href="<?= site_url('sbu/master_data') ?>" class="nav-link <?= ($this->uri->segment(1) === 'sbu' && $this->uri->segment(2) === 'master_data') ? 'active' : '' ?>">
            <i class="bi bi-journal-bookmark-fill"></i> <span>Master Data SBU</span>
        </a>

        <?php if (can_access('jadwal_standar') && in_array($user->role, ['admin', 'verifikator'])): ?>
        <a href="<?= site_url('sbu/jadwal') ?>" class="nav-link <?= ($this->uri->segment(1) === 'sbu' && $this->uri->segment(2) === 'jadwal') ? 'active' : '' ?>">
            <i class="bi bi-calendar-check-fill"></i> <span>Jadwal Pengusulan</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (can_access('laporan')): ?>
        <div class="nav-section">Laporan</div>
        <a href="<?= site_url('laporan') ?>" class="nav-link <?= $this->uri->segment(1) === 'laporan' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i> <span>Laporan & Rekap</span>
        </a>
        <?php endif; ?>

        <?php if ($user->role === 'admin'): ?>
        <div class="nav-section">Master Data</div>
        <a href="<?= site_url('master/skpd') ?>" class="nav-link <?= $this->uri->segment(2) === 'skpd' ? 'active' : '' ?>">
            <i class="bi bi-bank"></i> <span>SKPD</span>
        </a>
        <a href="<?= site_url('master/barang') ?>" class="nav-link <?= $this->uri->segment(2) === 'barang' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i> <span>Barang BMD</span>
        </a>
        <a href="<?= site_url('master/akun_belanja') ?>" class="nav-link <?= in_array($this->uri->segment(2), ['akun_belanja', 'akun']) ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i> <span>Akun Belanja SIPD</span>
        </a>
        <a href="<?= site_url('master/periode') ?>" class="nav-link <?= $this->uri->segment(2) === 'periode' ? 'active' : '' ?>">
            <i class="bi bi-calendar3"></i> <span>Periode</span>
        </a>
        <a href="<?= site_url('master/user') ?>" class="nav-link <?= $this->uri->segment(2) === 'user' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> <span>Pengguna</span>
        </a>
        <a href="<?= site_url('wa/settings') ?>" class="nav-link <?= $this->uri->segment(1) === 'wa' ? 'active' : '' ?>">
            <i class="bi bi-whatsapp"></i> <span>Integrasi WhatsApp</span>
        </a>
        <a href="<?= site_url('dokumentasi') ?>" class="nav-link <?= $this->uri->segment(1) === 'dokumentasi' ? 'active' : '' ?>">
            <i class="bi bi-journal-code"></i> <span>Dokumentasi & Update</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <?php if (!empty($user) && $user->role === 'admin'): ?>
        <a href="<?= site_url('dokumentasi') ?>" class="text-decoration-none text-muted" title="Buka Catatan Rilis & Dokumentasi Sistem">
            <small class="d-inline-flex align-items-center gap-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0.5">v<?= $this->config->item('app_version') ?></span>
                <span>&copy; BPKAD <?= date('Y') ?></span>
                <i class="bi bi-info-circle ms-0.5"></i>
            </small>
        </a>
        <?php else: ?>
        <small>v<?= $this->config->item('app_version') ?> &copy; BPKAD <?= date('Y') ?></small>
        <?php endif; ?>
    </div>
</aside>

<!-- ========== MAIN ========== -->
<main class="main-wrapper">
    <header class="topbar">
        <button class="btn btn-link sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" title="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-title d-none d-md-block">
            <?= e($title ?? '') ?>
        </div>

        <div class="topbar-actions">
            <!-- Pemilih Tahun Periode Anggaran Global -->
            <?php 
            $currYear = function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027;
            $allYears = function_exists('get_daftar_tahun_anggaran') ? get_daftar_tahun_anggaran() : [2028, 2027, 2026, 2025];
            ?>
            <div class="dropdown me-1">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill fw-bold" 
                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Tahun Anggaran Aktif: Klik untuk beralih tahun">
                    <i class="bi bi-calendar2-check-fill text-primary"></i>
                    <span>TA <?= (int) $currYear ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="min-width: 220px;">
                    <li class="dropdown-header text-uppercase small fw-bold text-muted px-3 d-flex align-items-center justify-content-between">
                        <span>Pilih Tahun Anggaran</span>
                        <span class="badge bg-primary text-white font-monospace" style="font-size:10px;">TA <?= (int)$currYear ?></span>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <?php foreach ($allYears as $y): ?>
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between py-2 <?= ($y === $currYear) ? 'active fw-bold' : '' ?>" 
                           href="<?= site_url('switch-year/' . $y) ?>">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-<?= ($y === $currYear) ? 'check-circle-fill' : 'circle' ?>"></i>
                                <span>Tahun <?= $y ?></span>
                            </div>
                            <?php if ($y == 2027): ?>
                                <span class="badge bg-warning text-dark font-monospace" style="font-size:10px;">Perencanaan</span>
                            <?php elseif ($y == (int)date('Y')): ?>
                                <span class="badge bg-info-subtle text-info border font-monospace" style="font-size:10px;">Berjalan</span>
                            <?php elseif ($y < (int)date('Y')): ?>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:10px;">Arsip</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Notifikasi -->
            <div class="dropdown">
                <button class="btn btn-link position-relative" data-bs-toggle="dropdown" aria-label="Notifikasi">
                    <i class="bi bi-bell-fill"></i>
                    <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <div class="dropdown-header d-flex justify-content-between">
                        <strong>Notifikasi</strong>
                        <small class="text-muted" id="notif-count">0 belum dibaca</small>
                    </div>
                    <div id="notif-list" class="notif-list">
                        <div class="text-center text-muted py-3 small">Memuat...</div>
                    </div>
                </div>
            </div>

            <!-- User menu -->
            <div class="dropdown">
                <button class="btn btn-link user-menu" data-bs-toggle="dropdown">
                    <div class="user-avatar"><?= strtoupper(substr($user->nama_lengkap, 0, 1)) ?></div>
                    <div class="user-info d-none d-md-block">
                        <strong><?= e($user->nama_lengkap) ?></strong>
                        <small class="text-muted"><?= e(ucfirst($user->role)) ?></small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= site_url('profile') ?>"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="content-wrapper">
        <?= flash_alert() ?>
