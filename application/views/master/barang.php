<?php 
defined('BASEPATH') OR exit('No direct script access allowed'); 
$offset = $offset ?? (($page - 1) * $perPage);
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title">
                <i class="bi bi-box-seam-fill text-primary me-2"></i>Master Data Barang BMD
            </h1>
            <p class="page-subtitle text-muted mb-0">
                Standarisasi kodefikasi dan katalog Barang Milik Daerah (BMD) Kabupaten Tapin.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportBarang">
                <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBarang" onclick="openAddModal()">
                <i class="bi bi-plus-lg me-1"></i>Tambah Barang
            </button>
        </div>
    </div>
</div>

<!-- Kartu Statistik KPI -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Barang BMD</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats->total, 0, ',', '.') ?></h3>
                    <small class="text-muted">Item Terdaftar</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="brand-icon bg-info bg-opacity-10 text-info rounded-3 p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-cpu"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold d-block">Peralatan & Mesin</span>
                    <h3 class="fw-bold mb-0 text-info"><?= number_format($stats->mesin, 0, ',', '.') ?></h3>
                    <small class="text-muted">Elektronik, Kendaraan, Mesin</small>
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
                    <span class="text-muted small fw-semibold d-block">Gedung & Bangunan</span>
                    <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats->gedung, 0, ',', '.') ?></h3>
                    <small class="text-muted">Kantor & Fasilitas Fisik</small>
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
                    <span class="text-muted small fw-semibold d-block">Barang Aktif</span>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($stats->aktif, 0, ',', '.') ?></h3>
                    <small class="text-muted">Siap Digunakan di RKBMD</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Box -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= site_url('master/barang') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1 text-muted">Cari Kode / Nama Barang</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" value="<?= e($filter['q'] ?? '') ?>" placeholder="Misal: PC Unit atau 1.3.2...">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1 text-muted">Kategori Barang</label>
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php 
                    $categories = [
                        'Peralatan dan Mesin',
                        'Gedung dan Bangunan',
                        'Tanah',
                        'Jalan, Irigasi dan Jaringan',
                        'Aset Tetap Lainnya',
                        'Konstruksi Dalam Pengerjaan'
                    ];
                    foreach ($categories as $k): ?>
                    <option value="<?= $k ?>" <?= ($filter['kategori'] ?? '') === $k ? 'selected' : '' ?>><?= $k ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1 text-muted">Harga Standar</label>
                <select name="has_harga" class="form-select">
                    <option value="">Semua Nilai</option>
                    <option value="1" <?= ($filter['has_harga'] ?? '') === '1' ? 'selected' : '' ?>>Ada Harga (> Rp 0)</option>
                    <option value="0" <?= ($filter['has_harga'] ?? '') === '0' ? 'selected' : '' ?>>Belum Ada Harga (Rp 0)</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1 text-muted">Status</label>
                <select name="is_active" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" <?= ($filter['is_active'] ?? '') === 1 ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($filter['is_active'] ?? '') === 0 ? 'selected' : '' ?>>Nonaktif</option>
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

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill fw-semibold" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="<?= site_url('master/barang') ?>" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Barang BMD -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold text-dark">
            <i class="bi bi-table me-2 text-primary"></i>Daftar Barang Milik Daerah (BMD)
        </h6>
        <span class="badge bg-light text-secondary border px-3 py-2 font-monospace">
            Ditemukan: <?= number_format($totalRows, 0, ',', '.') ?> Barang
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="60">#</th>
                        <th width="200">Kode Barang</th>
                        <th>Nama Barang BMD</th>
                        <th width="110">Satuan</th>
                        <th width="200">Kategori</th>
                        <th width="160" class="text-end">Harga Standar</th>
                        <th width="110" class="text-center">Status</th>
                        <th width="110" class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Tidak ada data barang yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    <?php else: foreach ($list as $i => $b): 
                        $no = $offset + $i + 1;
                        
                        $katClass = match ($b->kategori) {
                            'Peralatan dan Mesin'         => 'bg-primary-subtle text-primary border border-primary-subtle',
                            'Gedung dan Bangunan'         => 'bg-warning-subtle text-warning border border-warning-subtle',
                            'Tanah'                       => 'bg-success-subtle text-success border border-success-subtle',
                            'Jalan, Irigasi dan Jaringan' => 'bg-info-subtle text-info border border-info-subtle',
                            'Konstruksi Dalam Pengerjaan' => 'bg-danger-subtle text-danger border border-danger-subtle',
                            default                       => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                        };
                    ?>
                    <tr>
                        <td class="ps-3 text-muted small"><?= $no ?></td>
                        <td>
                            <code class="px-2 py-1 bg-light border rounded text-dark fw-bold"><?= e($b->kode_barang) ?></code>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($b->nama_barang) ?></div>
                            <?php if (!empty($b->keterangan)): ?>
                            <small class="text-muted d-block"><?= e($b->keterangan) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= e($b->satuan ?: 'Unit') ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $katClass ?>">
                                <?= e($b->kategori ?: 'Lainnya') ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold <?= $b->harga_standar > 0 ? 'text-primary' : 'text-muted' ?>">
                            <?= rupiah($b->harga_standar) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($b->is_active): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-xs" title="Edit Barang"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs" title="Hapus Barang"
                                        onclick="openDeleteModal(<?= $b->id ?>, '<?= e($b->kode_barang) ?>', '<?= e(addslashes($b->nama_barang)) ?>')">
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
            Menampilkan data <strong><?= number_format(min($totalRows, $offset + 1), 0, ',', '.') ?></strong> - <strong><?= number_format(min($totalRows, $offset + count($list)), 0, ',', '.') ?></strong> dari <strong><?= number_format($totalRows, 0, ',', '.') ?></strong> barang
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
                    <a class="page-link" href="<?= site_url('master/barang?' . http_build_query($params)) ?>">&laquo; Prev</a>
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
                <li class="page-item"><a class="page-link" href="<?= site_url('master/barang?' . http_build_query($params)) ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): 
                    $params['page'] = $p;
                ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= site_url('master/barang?' . http_build_query($params)) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($end < $totalPages): 
                    if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif;
                    $params['page'] = $totalPages;
                ?>
                <li class="page-item"><a class="page-link" href="<?= site_url('master/barang?' . http_build_query($params)) ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>

                <?php
                // Next button
                if ($page < $totalPages): 
                    $params['page'] = $page + 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= site_url('master/barang?' . http_build_query($params)) ?>">Next &raquo;</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah / Edit Barang -->
<div class="modal fade" id="modalBarang" tabindex="-1" aria-labelledby="modalBarangTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= site_url('master/barang') ?>" id="formBarang">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="barang_id" value="">

            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalBarangTitle">Tambah Barang BMD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label for="kode_barang" class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_barang" id="kode_barang" class="form-control font-monospace" required maxlength="50" placeholder="Contoh: 1.3.2.10.01.02.001">
                            <div class="form-text">Gunakan format kodefikasi BMD baku.</div>
                        </div>
                        <div class="col-md-7">
                            <label for="kategori_barang" class="form-label fw-semibold">Kategori Barang <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategori_barang" class="form-select" required>
                                <option value="Peralatan dan Mesin">Peralatan dan Mesin</option>
                                <option value="Gedung dan Bangunan">Gedung dan Bangunan</option>
                                <option value="Tanah">Tanah</option>
                                <option value="Jalan, Irigasi dan Jaringan">Jalan, Irigasi dan Jaringan</option>
                                <option value="Aset Tetap Lainnya">Aset Tetap Lainnya</option>
                                <option value="Konstruksi Dalam Pengerjaan">Konstruksi Dalam Pengerjaan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_barang" class="form-label fw-semibold">Nama Barang BMD <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" id="nama_barang" class="form-control form-control-lg" required maxlength="200" placeholder="Contoh: PC Unit Core i7, Kursi Kerja Pejabat Eselon III, dll">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="satuan_barang" class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="satuan_barang" class="form-control" required maxlength="30" value="Unit" placeholder="Unit, M2, Buah, Set...">
                        </div>
                        <div class="col-md-4">
                            <label for="harga_standar_b" class="form-label fw-semibold">Harga Standar (Rp)</label>
                            <input type="number" name="harga_standar" id="harga_standar_b" class="form-control" min="0" step="1000" value="0">
                            <div class="form-text">Harga acuan standar BMD (opsional).</div>
                        </div>
                        <div class="col-md-4">
                            <label for="is_active_b" class="form-label fw-semibold">Status Barang</label>
                            <select name="is_active" id="is_active_b" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="ket_barang" class="form-label fw-semibold">Keterangan / Spesifikasi Ringkas</label>
                        <textarea name="keterangan" id="ket_barang" class="form-control" rows="2" placeholder="Catatan tambahan spesifikasi atau acuan teknis barang..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-floppy me-1"></i>Simpan Barang
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportBarang" tabindex="-1" aria-labelledby="modalImportTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('master/barang') ?>" enctype="multipart/form-data">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="import">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalImportTitle">
                        <i class="bi bi-file-earmark-excel text-success me-2"></i>Import Data Barang Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small border-0 bg-info bg-opacity-10 text-info-emphasis">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Format file: <strong>.xlsx</strong> atau <strong>.xls</strong>.<br>
                        Urutan kolom baris 1 (Header): 
                        <strong>A: Kode Barang, B: Nama Barang, C: Satuan, D: Kategori, E: Harga Standar, F: Keterangan</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File Excel</label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="skip_duplicate" value="1" id="skipCheck" checked>
                        <label class="form-check-label fw-semibold" for="skipCheck">
                            Lompati jika Kode Barang sudah ada di database
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-upload me-1"></i>Proses Import Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalDeleteBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= site_url('master/barang') ?>">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_barang_id" value="">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Barang</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="mb-2">Apakah Anda yakin ingin menghapus data barang BMD ini?</p>
                    <div class="p-3 bg-light rounded text-start">
                        <div class="fw-bold text-dark font-monospace" id="delete_barang_kode"></div>
                        <div class="text-secondary small mt-1" id="delete_barang_nama"></div>
                    </div>
                    <small class="text-danger mt-2 d-block">Tindakan ini tidak dapat dibatalkan.</small>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="bi bi-trash-fill me-1"></i>Ya, Hapus Barang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('formBarang').reset();
    document.getElementById('barang_id').value = '';
    document.getElementById('modalBarangTitle').innerText = 'Tambah Barang BMD';
    document.getElementById('satuan_barang').value = 'Unit';
    document.getElementById('kategori_barang').value = 'Peralatan dan Mesin';
    document.getElementById('harga_standar_b').value = '0';
    document.getElementById('is_active_b').value = '1';
}

function openEditModal(item) {
    document.getElementById('barang_id').value = item.id;
    document.getElementById('kode_barang').value = item.kode_barang;
    document.getElementById('nama_barang').value = item.nama_barang;
    document.getElementById('satuan_barang').value = item.satuan || 'Unit';
    document.getElementById('kategori_barang').value = item.kategori || 'Peralatan dan Mesin';
    document.getElementById('harga_standar_b').value = item.harga_standar || 0;
    document.getElementById('ket_barang').value = item.keterangan || '';
    document.getElementById('is_active_b').value = item.is_active;
    document.getElementById('modalBarangTitle').innerText = 'Edit Barang: ' + item.kode_barang;

    const modal = new bootstrap.Modal(document.getElementById('modalBarang'));
    modal.show();
}

function openDeleteModal(id, kode, nama) {
    document.getElementById('delete_barang_id').value = id;
    document.getElementById('delete_barang_kode').innerText = kode;
    document.getElementById('delete_barang_nama').innerText = nama;

    const modal = new bootstrap.Modal(document.getElementById('modalDeleteBarang'));
    modal.show();
}
</script>
