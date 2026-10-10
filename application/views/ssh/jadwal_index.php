<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$tipe = $tipe ?? 'SSH';
$isSbu = ($tipe === 'SBU');
$prefixUrl = $prefixUrl ?? strtolower($tipe);
$modTitle = $isSbu ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';
?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">
            <i class="bi bi-calendar-range text-primary me-2"></i>
            Jadwal Pengusulan <?= $modTitle ?>
        </h1>
        <p class="page-subtitle text-muted mb-0">
            Kelola tahapan dan jadwal pengusulan <?= $tipe ?> untuk seluruh SKPD Pemerintah Kabupaten Tapin.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url("{$prefixUrl}/usulan") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Usulan
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalJadwal" onclick="resetFormJadwal()">
            <i class="bi bi-plus-circle me-1"></i> Buat Jadwal Baru
        </button>
    </div>
</div>

<!-- Alert Informasi Alur Jadwal -->
<div class="alert alert-info border-info shadow-sm d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-info-circle-fill fs-4 text-info flex-shrink-0 mt-1"></i>
    <div class="small">
        <strong>Aturan Pembukaan Jadwal Pengusulan:</strong>
        <ul class="mb-0 ps-3 mt-1">
            <li>SKPD hanya dapat membuat dan mengirim usulan <?= $tipe ?> baru jika terdapat jadwal pengusulan yang berstatus <strong>Buka</strong> dan tanggal saat ini berada dalam rentang tanggal mulai s.d. selesai.</li>
            <li>Jika jadwal berstatus <strong>Tutup</strong>, tombol pengusulan bagi SKPD akan terkunci otomatis dan berstatus <em>"Menunggu Pembuatan Jadwal oleh BPKAD"</em>.</li>
            <li>Administrator dan Verifikator dapat membuka atau menutup jadwal sewaktu-waktu sesuai tahapan penganggaran daerah.</li>
        </ul>
    </div>
</div>

<!-- Tabel Jadwal -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold">
            <i class="bi bi-table me-2 text-primary"></i>
            Daftar Jadwal Pengusulan Standar Harga
        </h6>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-2 px-3">
            Total <?= count($list) ?> Jadwal
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th width="40" class="text-center">#</th>
                        <th>Tahun Anggaran</th>
                        <th>Modul</th>
                        <th>Nama Jadwal / Tahapan</th>
                        <th>Periode Pelaksanaan</th>
                        <th class="text-center">Status</th>
                        <th>Keterangan</th>
                        <th class="text-center" width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada jadwal pengusulan yang dibuat.<br>
                            <small>Klik tombol <strong>Buat Jadwal Baru</strong> untuk membuka tahapan pengusulan.</small>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $today = date('Y-m-d');
                    foreach ($list as $idx => $row): 
                        $isCurrent = ($row->status === 'buka' && $today >= $row->tanggal_mulai && $today <= $row->tanggal_selesai);
                    ?>
                    <tr class="<?= $isCurrent ? 'table-success bg-opacity-25' : '' ?>">
                        <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                        <td>
                            <span class="badge bg-dark fs-6 px-2.5 py-1">TA <?= (int)$row->tahun_anggaran ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $row->tipe === 'SSH' ? 'bg-primary' : ($row->tipe === 'SBU' ? 'bg-info' : 'bg-secondary') ?>">
                                <?= e($row->tipe) ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= e($row->nama_jadwal) ?></div>
                            <?php if ($isCurrent): ?>
                                <small class="text-success fw-semibold"><i class="bi bi-broadcast me-1"></i>Sedang Berlangsung (Aktif)</small>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <div><i class="bi bi-calendar-event me-1 text-muted"></i><?= date('d M Y', strtotime($row->tanggal_mulai)) ?></div>
                            <div class="text-muted"><i class="bi bi-arrow-right me-1"></i><?= date('d M Y', strtotime($row->tanggal_selesai)) ?></div>
                        </td>
                        <td class="text-center">
                            <?php if ($row->status === 'buka'): ?>
                                <span class="badge bg-success py-2 px-2.5"><i class="bi bi-unlock-fill me-1"></i>BUKA</span>
                            <?php else: ?>
                                <span class="badge bg-danger py-2 px-2.5"><i class="bi bi-lock-fill me-1"></i>TUTUP</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted" style="max-width: 250px;">
                            <?= !empty($row->keterangan) ? nl2br(e($row->keterangan)) : '-' ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <!-- Tombol Toggle Status Buka / Tutup -->
                                <a href="<?= site_url("{$prefixUrl}/jadwal/toggle/{$row->id}") ?>" 
                                   class="btn <?= $row->status === 'buka' ? 'btn-outline-danger' : 'btn-outline-success' ?>" 
                                   title="<?= $row->status === 'buka' ? 'Tutup Jadwal' : 'Buka Jadwal' ?>"
                                   onclick="return confirm('Apakah Anda yakin ingin <?= $row->status === 'buka' ? 'MENUTUP' : 'MEMBUKA' ?> jadwal pengusulan ini?')">
                                    <i class="bi <?= $row->status === 'buka' ? 'bi-lock' : 'bi-unlock' ?>"></i>
                                </a>
                                <!-- Tombol Edit -->
                                <button type="button" class="btn btn-outline-primary" 
                                        onclick='editJadwal(<?= json_encode($row) ?>)' title="Edit Jadwal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($this->currentUser->role === 'admin'): ?>
                                <!-- Tombol Hapus -->
                                <a href="<?= site_url("{$prefixUrl}/jadwal/hapus/{$row->id}") ?>" 
                                   class="btn btn-outline-secondary" 
                                   title="Hapus Jadwal"
                                   onclick="return confirm('Hapus jadwal pengusulan ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Jadwal -->
<div class="modal fade" id="modalJadwal" tabindex="-1" aria-labelledby="modalJadwalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= site_url("{$prefixUrl}/jadwal/simpan") ?>" method="post">
                <?= csrf_input() ?>
                <input type="hidden" name="id" id="jadwal_id" value="">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalJadwalLabel">
                        <i class="bi bi-calendar-plus text-primary me-2"></i>Buat Jadwal Pengusulan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="jadwal_tipe" class="form-label fw-semibold">Modul Pengusulan <span class="text-danger">*</span></label>
                        <select name="tipe" id="jadwal_tipe" class="form-select" required>
                            <option value="SEMUA">Semua Modul (SSH & SBU)</option>
                            <option value="SSH" <?= $tipe === 'SSH' ? 'selected' : '' ?>>Standar Satuan Harga (SSH) Fisik</option>
                            <option value="SBU" <?= $tipe === 'SBU' ? 'selected' : '' ?>>Standar Biaya Umum (SBU) Non-Fisik</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="jadwal_tahun" class="form-label fw-semibold">Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="number" name="tahun_anggaran" id="jadwal_tahun" class="form-control" 
                                   value="2027" min="2024" max="2035" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jadwal_status" class="form-label fw-semibold">Status Awal <span class="text-danger">*</span></label>
                            <select name="status" id="jadwal_status" class="form-select" required>
                                <option value="buka">Buka (Aktif)</option>
                                <option value="tutup">Tutup (Menunggu)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jadwal_nama" class="form-label fw-semibold">Nama Jadwal / Tahapan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_jadwal" id="jadwal_nama" class="form-control" 
                               placeholder="Contoh: Pengusulan SSH & SBU Murni TA 2027" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="jadwal_tgl_mulai" class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="jadwal_tgl_mulai" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="jadwal_tgl_selesai" class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" id="jadwal_tgl_selesai" class="form-control" required value="<?= date('Y-12-31') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jadwal_keterangan" class="form-label fw-semibold">Keterangan / Petunjuk Teknis</label>
                        <textarea name="keterangan" id="jadwal_keterangan" class="form-control" rows="3" 
                                  placeholder="Catatan tambahan untuk SKPD pengusul..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetFormJadwal() {
    document.getElementById('jadwal_id').value = '';
    document.getElementById('jadwal_tipe').value = '<?= $tipe ?>';
    document.getElementById('jadwal_tahun').value = '2027';
    document.getElementById('jadwal_nama').value = 'Pengusulan <?= $modTitle ?> TA 2027';
    document.getElementById('jadwal_status').value = 'buka';
    document.getElementById('jadwal_keterangan').value = '';
    document.getElementById('modalJadwalLabel').innerHTML = '<i class="bi bi-calendar-plus text-primary me-2"></i>Buat Jadwal Pengusulan';
}

function editJadwal(data) {
    document.getElementById('jadwal_id').value = data.id;
    document.getElementById('jadwal_tipe').value = data.tipe;
    document.getElementById('jadwal_tahun').value = data.tahun_anggaran;
    document.getElementById('jadwal_nama').value = data.nama_jadwal;
    document.getElementById('jadwal_tgl_mulai').value = data.tanggal_mulai;
    document.getElementById('jadwal_tgl_selesai').value = data.tanggal_selesai;
    document.getElementById('jadwal_status').value = data.status;
    document.getElementById('jadwal_keterangan').value = data.keterangan || '';
    document.getElementById('modalJadwalLabel').innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i>Edit Jadwal Pengusulan';
    
    var modal = new bootstrap.Modal(document.getElementById('modalJadwal'));
    modal.show();
}
</script>
