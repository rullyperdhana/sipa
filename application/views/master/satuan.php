<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 
$offset = $offset ?? (($page - 1) * $perPage);
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title">
                <i class="bi bi-rulers text-primary me-2"></i>Master Referensi Satuan Standar
            </h1>
            <p class="page-subtitle text-muted mb-0">
                Tabel referensi ukuran dan satuan standar barang/jasa untuk modul Standar Satuan Harga (SSH), Standar Biaya Umum (SBU), dan RKBMD.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSatuan" onclick="openAddModal()">
                <i class="bi bi-plus-lg me-1"></i>Tambah Satuan
            </button>
        </div>
    </div>
</div>

<!-- Kartu Statistik -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-collection"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Satuan Terdaftar</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats->total, 0, ',', '.') ?></h3>
                    <small class="text-muted">Master Ukuran Satuan</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-success bg-opacity-10 text-success rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Satuan Aktif</span>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($stats->active, 0, ',', '.') ?></h3>
                    <small class="text-muted">Dapat Digunakan di Form</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-dash-circle-fill"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Satuan Nonaktif</span>
                    <h3 class="fw-bold mb-0 text-secondary"><?= number_format($stats->non, 0, ',', '.') ?></h3>
                    <small class="text-muted">Diarsipkan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Box -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= site_url('master/satuan') ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1 text-muted">Cari Nama Satuan</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" value="<?= e($filter['q'] ?? '') ?>" placeholder="Misal: Buah, Rim, Botol, Orang/Hari...">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1 text-muted">Status</label>
                <select name="is_active" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" <?= (isset($filter['is_active']) && $filter['is_active'] === 1) ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= (isset($filter['is_active']) && $filter['is_active'] === 0) ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1 text-muted">Per Halaman</label>
                <select name="per_page" class="form-select">
                    <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25 data</option>
                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50 data</option>
                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100 data</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
                <?php if (!empty($filter['q']) || isset($filter['is_active']) || $perPage != 25): ?>
                <a href="<?= site_url('master/satuan') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Table Container -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="card-title fw-bold mb-0 text-dark">
            <i class="bi bi-table me-2 text-primary"></i>Daftar Satuan Standar
        </h6>
        <span class="badge bg-light text-muted border">
            Total Hasil: <?= number_format($totalRows, 0, ',', '.') ?> data
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="60">#</th>
                        <th>Nama Satuan Standar</th>
                        <th>Keterangan</th>
                        <th width="140" class="text-center">Status</th>
                        <th width="100" class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Tidak ada data satuan yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    <?php else: foreach ($list as $i => $item): 
                        $no = $offset + $i + 1;
                    ?>
                    <tr>
                        <td class="ps-3 text-muted small"><?= $no ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-6 fw-bold">
                                    <?= e($item->nama_satuan) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="text-secondary small">
                                <?= !empty($item->keterangan) ? e($item->keterangan) : '<em class="text-muted">-</em>' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($item->is_active == 1): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-dash-circle me-1"></i>Nonaktif
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-xs" title="Edit Satuan" 
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs" title="Hapus Satuan"
                                        onclick="openDeleteModal(<?= $item->id ?>, '<?= e(addslashes($item->nama_satuan)) ?>')">
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
                    <a class="page-link" href="<?= site_url('master/satuan?' . http_build_query($params)) ?>">&laquo; Prev</a>
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
                <li class="page-item"><a class="page-link" href="<?= site_url('master/satuan?' . http_build_query($params)) ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): 
                    $params['page'] = $p;
                ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= site_url('master/satuan?' . http_build_query($params)) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($end < $totalPages): 
                    if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif;
                    $params['page'] = $totalPages;
                ?>
                <li class="page-item"><a class="page-link" href="<?= site_url('master/satuan?' . http_build_query($params)) ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>

                <?php
                // Next button
                if ($page < $totalPages): 
                    $params['page'] = $page + 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= site_url('master/satuan?' . http_build_query($params)) ?>">Next &raquo;</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah / Edit Satuan -->
<div class="modal fade" id="modalSatuan" tabindex="-1" aria-labelledby="modalSatuanTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url('master/satuan') ?>" method="post" id="formSatuan">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="satuan_id" value="">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalSatuanTitle">Tambah Satuan Standar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal_nama_satuan" class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_nama_satuan" name="nama_satuan" required placeholder="Contoh: Buah, Unit, Box, Orang/Hari...">
                        <div class="form-text">Masukkan nama satuan standar yang jelas dan lazim digunakan.</div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_keterangan" class="form-label fw-semibold">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="modal_keterangan" name="keterangan" rows="2" placeholder="Contoh: Satuan standar untuk kemasan obat / alat tulis..."></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="modal_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="modal_is_active">
                                    Status Aktif
                                </label>
                                <div class="form-text">Satuan aktif otomatis muncul dalam daftar pilihan saat membuat usulan barang/jasa.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-floppy me-1"></i>Simpan Satuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url('master/satuan') ?>" method="post">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id" value="">

                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle display-4"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Hapus Satuan Standar?</h5>
                    <p class="text-muted mb-3">
                        Apakah Anda yakin ingin menghapus data satuan:
                        <br><strong class="fs-5 text-dark" id="delete_nama"></strong>?
                    </p>
                    <div class="alert alert-warning py-2 px-3 small text-start mb-0">
                        <i class="bi bi-info-circle me-1"></i> Satuan yang terhubung dengan usulan standar harga atau barang eksisting mungkin akan terpengaruh.
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="bi bi-trash me-1"></i>Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('formSatuan').reset();
    document.getElementById('modalSatuanTitle').innerText = 'Tambah Satuan Standar';
    document.getElementById('satuan_id').value = '';
    document.getElementById('modal_is_active').checked = true;
}

function openEditModal(item) {
    document.getElementById('modalSatuanTitle').innerText = 'Edit Satuan Standar';
    document.getElementById('satuan_id').value = item.id;
    document.getElementById('modal_nama_satuan').value = item.nama_satuan;
    document.getElementById('modal_keterangan').value = item.keterangan || '';
    document.getElementById('modal_is_active').checked = (item.is_active == 1);

    var modal = new bootstrap.Modal(document.getElementById('modalSatuan'));
    modal.show();
}

function openDeleteModal(id, nama) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_nama').innerText = nama;

    var modal = new bootstrap.Modal(document.getElementById('modalDelete'));
    modal.show();
}
</script>
