<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 
$offset = $offset ?? (($page - 1) * $perPage);
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title">
                <i class="bi bi-journal-text text-primary me-2"></i>Master Referensi Akun Belanja (SIPD RI)
            </h1>
            <p class="page-subtitle text-muted mb-0">
                Tabel referensi kode rekening belanja daerah (Permendagri 90 & Kepmendagri 050) terintegrasi SIPD RI.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAkun" onclick="openAddModal()">
                <i class="bi bi-plus-lg me-1"></i>Tambah Akun Belanja
            </button>
        </div>
    </div>
</div>

<!-- Kartu Statistik -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-journals"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Akun Belanja</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats->total, 0, ',', '.') ?></h3>
                    <small class="text-muted">Kode Rekening 5.x</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-info bg-opacity-10 text-info rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Belanja Operasi (5.1)</span>
                    <h3 class="fw-bold mb-0 text-info"><?= number_format($stats->operasi, 0, ',', '.') ?></h3>
                    <small class="text-muted">Barang, Jasa & Pegawai</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-warning bg-opacity-10 text-warning rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Belanja Modal (5.2)</span>
                    <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats->modal, 0, ',', '.') ?></h3>
                    <small class="text-muted">Aset Tetap & Peralatan</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-success bg-opacity-10 text-success rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Siap Dianggarkan</span>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($stats->leaf, 0, ',', '.') ?></h3>
                    <small class="text-muted">Sub Rincian Objek</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Box -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= site_url('master/akun_belanja') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1 text-muted">Cari Kode / Uraian Akun</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" value="<?= e($filter['q'] ?? '') ?>" placeholder="Misal: 5.1.02 atau Alat Tulis Kantor...">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1 text-muted">Kelompok Belanja</label>
                <select name="kelompok" class="form-select">
                    <option value="">Semua Kelompok Belanja</option>
                    <?php 
                    $kelompoks = ['Belanja Operasi', 'Belanja Modal', 'Belanja Tidak Terduga', 'Belanja Transfer'];
                    foreach ($kelompoks as $kel): ?>
                    <option value="<?= $kel ?>" <?= ($filter['kelompok'] ?? '') === $kel ? 'selected' : '' ?>><?= $kel ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1 text-muted">Status Input</label>
                <select name="is_leaf" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" <?= ($filter['is_leaf'] ?? '') === 1 ? 'selected' : '' ?>>Dapat Dianggarkan</option>
                    <option value="0" <?= ($filter['is_leaf'] ?? '') === 0 ? 'selected' : '' ?>>Header / Rekap</option>
                </select>
            </div>

            <div class="col-md-1">
                <label class="form-label small fw-semibold mb-1 text-muted">Per Hal</label>
                <select name="per_page" class="form-select">
                    <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill fw-semibold">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="<?= site_url('master/akun_belanja') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Akun Belanja -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold text-dark">
            <i class="bi bi-table me-2 text-primary"></i>Daftar Rekening Belanja Daerah
        </h6>
        <span class="badge bg-light text-secondary border px-3 py-2 font-monospace">
            Ditemukan: <?= number_format($totalRows, 0, ',', '.') ?> Akun
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="60">#</th>
                        <th width="200">Kode Rekening</th>
                        <th>Uraian Akun Belanja</th>
                        <th width="170">Kelompok</th>
                        <th width="110" class="text-center">Level</th>
                        <th width="150" class="text-center">Status</th>
                        <th width="100" class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Tidak ada data rekening belanja yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    <?php else: foreach ($list as $i => $item): 
                        $no = $offset + $i + 1;
                        $levelName = [
                            1 => 'Akun',
                            2 => 'Kelompok',
                            3 => 'Jenis',
                            4 => 'Objek',
                            5 => 'Rincian Objek',
                            6 => 'Sub Rincian'
                        ][$item->level] ?? "Lv {$item->level}";
                    ?>
                    <tr class="<?= $item->is_leaf == 0 ? 'bg-light bg-opacity-50 fw-semibold' : '' ?>">
                        <td class="ps-3 text-muted small"><?= $no ?></td>
                        <td>
                            <code class="px-2 py-1 bg-light border rounded text-dark fw-bold"><?= e($item->kode_akun) ?></code>
                        </td>
                        <td>
                            <div class="<?= $item->is_leaf == 0 ? 'fw-bold text-dark' : 'text-secondary' ?>" style="<?= $item->level > 2 ? 'padding-left: ' . (($item->level - 2) * 12) . 'px;' : '' ?>">
                                <?= e($item->nama_akun) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $item->kelompok === 'Belanja Modal' ? 'bg-warning-subtle text-warning border border-warning-subtle' : ($item->kelompok === 'Belanja Operasi' ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle') ?>">
                                <?= e($item->kelompok) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-muted border" title="Level <?= $item->level ?>">
                                <?= $levelName ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($item->is_leaf == 1): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Siap Dianggarkan
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <i class="bi bi-folder me-1"></i>Header
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-xs" title="Edit Akun" 
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs" title="Hapus Akun"
                                        onclick="openDeleteModal(<?= $item->id ?>, '<?= e($item->kode_akun) ?>', '<?= e(addslashes($item->nama_akun)) ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Footer -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Menampilkan data <strong><?= number_format(min($totalRows, $offset + 1), 0, ',', '.') ?></strong> - <strong><?= number_format(min($totalRows, $offset + count($list)), 0, ',', '.') ?></strong> dari <strong><?= number_format($totalRows, 0, ',', '.') ?></strong> data
        </div>
        
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <?php
                // Build query params
                $params = $filter;
                $params['per_page'] = $perPage;

                // Previous button
                if ($page > 1): 
                    $params['page'] = $page - 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= site_url('master/akun_belanja?' . http_build_query($params)) ?>">&laquo; Prev</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled"><span class="page-link">&laquo; Prev</span></li>
                <?php endif; ?>

                <?php
                // Display page numbers with smart sliding window
                $start = max(1, $page - 3);
                $end   = min($totalPages, $page + 3);

                if ($start > 1):
                    $params['page'] = 1;
                ?>
                <li class="page-item"><a class="page-link" href="<?= site_url('master/akun_belanja?' . http_build_query($params)) ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): 
                    $params['page'] = $p;
                ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= site_url('master/akun_belanja?' . http_build_query($params)) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($end < $totalPages): 
                    if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif;
                    $params['page'] = $totalPages;
                ?>
                <li class="page-item"><a class="page-link" href="<?= site_url('master/akun_belanja?' . http_build_query($params)) ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>

                <?php
                // Next button
                if ($page < $totalPages): 
                    $params['page'] = $page + 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= site_url('master/akun_belanja?' . http_build_query($params)) ?>">Next &raquo;</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah / Edit Akun Belanja -->
<div class="modal fade" id="modalAkun" tabindex="-1" aria-labelledby="modalAkunTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url('master/akun_belanja') ?>" method="post" id="formAkun">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="akun_id" value="">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalAkunTitle">Tambah Akun Belanja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="modal_kode_akun" class="form-label fw-semibold">Kode Rekening <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" id="modal_kode_akun" name="kode_akun" required placeholder="Contoh: 5.1.02.01.01.0024">
                            <div class="form-text">Gunakan format titik pemisah standar SIPD RI.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_kelompok" class="form-label fw-semibold">Kelompok Belanja <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_kelompok" name="kelompok" required>
                                <option value="Belanja Operasi">Belanja Operasi (5.1)</option>
                                <option value="Belanja Modal">Belanja Modal (5.2)</option>
                                <option value="Belanja Tidak Terduga">Belanja Tidak Terduga (5.3)</option>
                                <option value="Belanja Transfer">Belanja Transfer (5.4)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_nama_akun" class="form-label fw-semibold">Nama / Uraian Akun Belanja <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_nama_akun" name="nama_akun" rows="3" required placeholder="Contoh: Belanja Alat/Bahan untuk Kegiatan Kantor-Alat Tulis Kantor"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="modal_is_leaf" name="is_leaf" value="1" checked>
                                <label class="form-check-label fw-semibold" for="modal_is_leaf">
                                    Dapat Dianggarkan (Sub Rincian Objek)
                                </label>
                                <div class="form-text">Centang jika akun ini dapat dipilih saat membuat usulan SSH/SBU/RKBMD.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="modal_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="modal_is_active">
                                    Status Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-floppy me-1"></i>Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalDeleteAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url('master/akun_belanja') ?>" method="post">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_akun_id" value="">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="mb-2">Apakah Anda yakin ingin menghapus rekening belanja ini?</p>
                    <div class="p-3 bg-light rounded text-start">
                        <div class="fw-bold text-dark font-monospace" id="delete_akun_kode"></div>
                        <div class="text-secondary small mt-1" id="delete_akun_nama"></div>
                    </div>
                    <small class="text-danger mt-2 d-block">Tindakan ini tidak dapat dibatalkan.</small>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="bi bi-trash-fill me-1"></i>Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('formAkun').reset();
    document.getElementById('akun_id').value = '';
    document.getElementById('modalAkunTitle').innerText = 'Tambah Akun Belanja';
    document.getElementById('modal_is_leaf').checked = true;
    document.getElementById('modal_is_active').checked = true;
}

function openEditModal(item) {
    document.getElementById('akun_id').value = item.id;
    document.getElementById('modal_kode_akun').value = item.kode_akun;
    document.getElementById('modal_nama_akun').value = item.nama_akun;
    document.getElementById('modal_kelompok').value = item.kelompok;
    document.getElementById('modal_is_leaf').checked = (item.is_leaf == 1);
    document.getElementById('modal_is_active').checked = (item.is_active == 1);
    document.getElementById('modalAkunTitle').innerText = 'Edit Akun Belanja: ' + item.kode_akun;

    const modal = new bootstrap.Modal(document.getElementById('modalAkun'));
    modal.show();
}

function openDeleteModal(id, kode, nama) {
    document.getElementById('delete_akun_id').value = id;
    document.getElementById('delete_akun_kode').innerText = kode;
    document.getElementById('delete_akun_nama').innerText = nama;

    const modal = new bootstrap.Modal(document.getElementById('modalDeleteAkun'));
    modal.show();
}
</script>
