<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';

$page = $page ?? 1;
$perPage = $perPage ?? 25;
$totalRows = $totalRows ?? count($list);
$totalPages = $totalPages ?? 1;
$startItem = ($totalRows > 0) ? (($page - 1) * $perPage + 1) : 0;
$endItem = min($totalRows, $page * $perPage);
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi <?= $isSbu ? 'bi-journal-bookmark-fill text-info' : 'bi-journal-check text-primary' ?> me-2"></i>
            Master Data <?= $modTitle ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            <?= $isSbu 
                ? 'Katalog resmi Standar Biaya Umum (Honorarium, Jasa, Sewa & Tarif Operasional) Pemerintah Kabupaten Tapin Tahun 2027 sebagai acuan resmi usulan.' 
                : 'Katalog resmi Standar Satuan Harga (Barang, Peralatan & Material Fisik) Pemerintah Kabupaten Tapin Tahun 2027 sebagai acuan resmi usulan.' ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if (in_array($this->currentUser->role, ['operator_skpd', 'skpd', 'admin'], TRUE)): ?>
        <a href="<?= site_url("{$prefixUrl}/usulan") ?>" class="btn btn-outline-primary">
            <i class="bi bi-box-seam me-1"></i> Daftar Usulan <?= $tipe ?>
        </a>
        <?php endif; ?>
        <?php if (in_array($this->currentUser->role, ['admin', 'verifikator'], TRUE)): ?>
        <a href="<?= site_url("{$prefixUrl}/jadwal") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-calendar-range me-1"></i> Jadwal Pengusulan
        </a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Katalog
        </button>
    </div>
</div>

<!-- Banner Interkoneksi Usulan & Master 2027 -->
<div class="alert alert-primary border-primary shadow-sm d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-link-45deg fs-3 text-primary flex-shrink-0 mt-1"></i>
    <div class="small">
        <h6 class="alert-heading fw-bold mb-1 text-primary">Master Data Acuan Usulan Tahun Anggaran 2027:</h6>
        <div>
            Katalog ini memuat data standar resmi Pemerintah Kabupaten Tapin yang terhubung langsung dengan kode rekening belanja <strong>SIPD RI</strong>.
            SKPD dapat langsung mengklik tombol <strong><i class="bi bi-pencil-square"></i> Usulkan</strong> pada setiap baris item untuk mengajukan usulan penyesuaian harga tahun anggaran baru secara otomatis tanpa perlu mengetik ulang spesifikasi dan rekening belanja.
        </div>
    </div>
</div>

<!-- Card Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="<?= site_url("{$prefixUrl}/master_data") ?>" class="row g-2 align-items-end">
            <!-- Filter Tahun Anggaran -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Tahun Anggaran</label>
                <select class="form-select form-select-sm" name="tahun">
                    <?php 
                    $currTahun = $filter['tahun'] ?? 2027;
                    foreach ($tahunList as $th): 
                    ?>
                    <option value="<?= (int)$th ?>" <?= (int)$currTahun === (int)$th ? 'selected' : '' ?>>
                        TA <?= (int)$th ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Kategori -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Filter Kategori <?= $tipe ?></label>
                <select class="form-select form-select-sm" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $kat): ?>
                    <option value="<?= e($kat) ?>" <?= ($filter['kategori'] ?? '') === $kat ? 'selected' : '' ?>>
                        <?= e($kat) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pencarian (Search) -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Pencarian <?= $isSbu ? 'Biaya / Jasa / Rekening' : 'Barang / Spek / Rekening' ?></label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" value="<?= e($filter['q'] ?? '') ?>" placeholder="Ketik kata kunci atau kode rekening...">
                </div>
            </div>

            <!-- Pilihan Limit Data Per Halaman -->
            <div class="col-md-1">
                <label class="form-label small fw-semibold text-muted mb-1">Per Hal.</label>
                <select class="form-select form-select-sm" name="per_page">
                    <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>

            <!-- Tombol Filter & Reset -->
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm <?= $isSbu ? 'btn-info text-white' : 'btn-primary' ?> w-100" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="<?= site_url("{$prefixUrl}/master_data") ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Katalog Master Data (Read-Only) -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title mb-0 fw-bold">
                <i class="bi bi-table me-2 <?= $isSbu ? 'text-info' : 'text-primary' ?>"></i>
                Katalog Resmi <?= $modTitle ?> TA <?= e($currTahun) ?>
            </h6>
            <small class="text-muted">Menampilkan data resmi hasil standarisasi harga Kabupaten Tapin.</small>
        </div>
        <span class="badge bg-success py-2 px-3">
            <i class="bi bi-check-circle me-1"></i><?= number_format($totalRows, 0, ',', '.') ?> Total Item
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="tabelMasterDataSsh">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Kode Standar / Kelompok</th>
                        <th><?= $isSbu ? 'Uraian Biaya / Honor / Jasa' : 'Uraian Barang Fisik' ?></th>
                        <th>Kategori</th>
                        <th>Rekening Belanja SIPD RI</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-end">Harga Standar (Rp)</th>
                        <th class="text-center" width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                            <span class="fw-semibold">Tidak ada data <?= $tipe ?> yang sesuai dengan kriteria pencarian/filter.</span>
                        </td>
                    </tr>
                    <?php else: foreach ($list as $idx => $row): ?>
                    <tr>
                        <td class="text-center text-muted small"><?= ($page - 1) * $perPage + $idx + 1 ?></td>
                        <td>
                            <strong class="font-monospace text-dark d-block"><?= e($row->kode_standar) ?></strong>
                            <small class="font-monospace text-muted"><?= e($row->kode_kelompok) ?></small>
                        </td>
                        <td>
                            <strong class="text-dark d-block"><?= e($row->uraian) ?></strong>
                            <div class="small text-muted text-truncate" style="max-width: 320px;" title="<?= e($row->spesifikasi) ?>">
                                <?= e($row->spesifikasi) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border"><?= e($row->kategori) ?></span>
                        </td>
                        <td class="small" style="max-width: 260px;">
                            <?php if (!empty($row->kode_rekening)): ?>
                                <div class="font-monospace fw-semibold text-primary"><?= e($row->kode_rekening) ?></div>
                                <div class="text-muted text-truncate" title="<?= e($row->nama_rekening) ?>"><?= e($row->nama_rekening) ?></div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= e($row->satuan) ?></span>
                        </td>
                        <td class="text-end fw-bold text-success fs-6">
                            <?= rupiah($row->harga_satuan) ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if (in_array($this->currentUser->role, ['operator_skpd', 'skpd', 'admin'], TRUE)): ?>
                                <a href="<?= site_url("{$prefixUrl}/tambah?master_id={$row->id}") ?>" 
                                   class="btn btn-outline-primary" 
                                   title="Usulkan Penyesuaian dari Master Ini">
                                    <i class="bi bi-pencil-square me-1"></i>Usulkan
                                </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick='showDetailMaster(<?= json_encode($row) ?>)' 
                                        title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Footer Paginasi -->
    <div class="card-footer bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small text-muted">
            Menampilkan <strong><?= $startItem ?></strong> sampai <strong><?= $endItem ?></strong> dari <strong><?= number_format($totalRows, 0, ',', '.') ?></strong> item katalog
        </div>
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Navigasi Halaman Katalog">
            <ul class="pagination pagination-sm mb-0">
                <!-- Tombol Prev -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= site_url("{$prefixUrl}/master_data?" . http_build_query(array_merge($filter, ['per_page' => $perPage, 'page' => max(1, $page - 1)]))) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <!-- Range Nomor Halaman -->
                <?php
                $startP = max(1, $page - 2);
                $endP = min($totalPages, $page + 2);
                if ($startP > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . site_url("{$prefixUrl}/master_data?" . http_build_query(array_merge($filter, ['per_page' => $perPage, 'page' => 1]))) . '">1</a></li>';
                    if ($startP > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                for ($p = $startP; $p <= $endP; $p++) {
                    $active = ($p == $page) ? 'active' : '';
                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . site_url("{$prefixUrl}/master_data?" . http_build_query(array_merge($filter, ['per_page' => $perPage, 'page' => $p]))) . '">' . $p . '</a></li>';
                }
                if ($endP < $totalPages) {
                    if ($endP < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    echo '<li class="page-item"><a class="page-link" href="' . site_url("{$prefixUrl}/master_data?" . http_build_query(array_merge($filter, ['per_page' => $perPage, 'page' => $totalPages]))) . '">' . $totalPages . '</a></li>';
                }
                ?>

                <!-- Tombol Next -->
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= site_url("{$prefixUrl}/master_data?" . http_build_query(array_merge($filter, ['per_page' => $perPage, 'page' => min($totalPages, $page + 1)]))) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Item Master -->
<div class="modal fade" id="modalDetailMaster" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Master <?= $modTitle ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailMasterBody">
                <!-- Diisi via JS -->
            </div>
            <div class="modal-footer bg-light">
                <a href="#" id="btnModalUsulkan" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Usulkan Penyesuaian dari Item Ini
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showDetailMaster(data) {
    const rupiah = function(num) {
        return 'Rp ' + parseInt(num, 10).toLocaleString('id-ID');
    };

    let html = `
        <div class="row g-3">
            <div class="col-md-6">
                <label class="text-muted small fw-semibold">Kode Standar</label>
                <div class="font-monospace fw-bold fs-6">${data.kode_standar}</div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small fw-semibold">Kode Kelompok Barang</label>
                <div class="font-monospace fw-bold fs-6">${data.kode_kelompok}</div>
            </div>
            <div class="col-12">
                <label class="text-muted small fw-semibold">Uraian Barang / Biaya</label>
                <div class="fw-bold fs-5 text-dark">${data.uraian}</div>
            </div>
            <div class="col-12">
                <label class="text-muted small fw-semibold">Spesifikasi Teknis / Ketentuan</label>
                <div class="p-3 bg-light rounded border">${data.spesifikasi || '-'}</div>
            </div>
            <div class="col-md-4">
                <label class="text-muted small fw-semibold">Kategori</label>
                <div><span class="badge bg-primary-subtle text-primary border">${data.kategori}</span></div>
            </div>
            <div class="col-md-4">
                <label class="text-muted small fw-semibold">Satuan</label>
                <div><span class="badge bg-light text-dark border fs-6">${data.satuan}</span></div>
            </div>
            <div class="col-md-4">
                <label class="text-muted small fw-semibold">Harga Standar Resmi</label>
                <div class="fw-bold text-success fs-5">${rupiah(data.harga_satuan)}</div>
            </div>
            <div class="col-12">
                <label class="text-muted small fw-semibold">Rekening Belanja SIPD RI</label>
                <div class="p-2.5 bg-light rounded border font-monospace">
                    <strong>${data.kode_rekening || '-'}</strong>
                    <div class="text-muted small mt-1">${data.nama_rekening || ''}</div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="text-muted small fw-semibold">Tahun Anggaran Berlaku</label>
                <div>TA ${data.tahun_anggaran}</div>
            </div>
        </div>
    `;

    document.getElementById('modalDetailMasterBody').innerHTML = html;
    document.getElementById('btnModalUsulkan').href = '<?= site_url("{$prefixUrl}/tambah?master_id=") ?>' + data.id;

    var modal = new bootstrap.Modal(document.getElementById('modalDetailMaster'));
    modal.show();
}
</script>
