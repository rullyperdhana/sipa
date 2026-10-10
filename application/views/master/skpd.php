<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="bi bi-bank me-2"></i>Master Data SKPD</h1>
            <p class="page-subtitle">Kelola data Satuan Kerja Perangkat Daerah.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSkpd" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i>Tambah SKPD
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt-skpd">
                <thead class="table-light">
                    <tr><th>#</th><th>Kode SKPD</th><th>Nama SKPD</th><th>Kepala SKPD</th><th>Pengurus</th><th>Telp</th><th class="text-center">Status</th><th class="text-center" width="120">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data SKPD.</td></tr>
                    <?php else: foreach ($list as $i => $s): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><strong><?= e($s->kode_skpd) ?></strong></td>
                        <td><?= e($s->nama_skpd) ?></td>
                        <td><?= e($s->kepala_skpd ?: '-') ?><?php if ($s->nip_kepala): ?><br><small class="text-muted">NIP: <?= e($s->nip_kepala) ?></small><?php endif; ?></td>
                        <td><?= e($s->nama_pengurus ?: '-') ?><?php if ($s->nip_pengurus): ?><br><small class="text-muted">NIP: <?= e($s->nip_pengurus) ?></small><?php endif; ?></td>
                        <td><?= e($s->telepon ?: '-') ?></td>
                        <td class="text-center"><span class="badge bg-<?= $s->is_active ? 'success' : 'secondary' ?>"><?= $s->is_active ? 'Aktif' : 'Nonaktif' ?></span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info btn-nomenklatur-skpd me-1"
                                data-id="<?= (int)$s->id ?>"
                                data-nama="<?= e($s->nama_skpd) ?>"
                                data-kode="<?= e($s->kode_skpd) ?>"
                                data-kepala="<?= e($s->kepala_skpd) ?>"
                                data-nip="<?= e($s->nip_kepala) ?>"
                                data-jabatan="<?= e($s->jabatan_kepala) ?>"
                                data-peng="<?= e($s->nama_pengurus) ?>"
                                data-npeng="<?= e($s->nip_pengurus) ?>"
                                title="Riwayat & Nomenklatur Per Tahun">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary btn-edit-skpd"
                                data-id="<?= (int)$s->id ?>"
                                data-kode="<?= e($s->kode_skpd) ?>"
                                data-nama="<?= e($s->nama_skpd) ?>"
                                data-kepala="<?= e($s->kepala_skpd) ?>"
                                data-nip="<?= e($s->nip_kepala) ?>"
                                data-jabatan="<?= e($s->jabatan_kepala) ?>"
                                data-peng="<?= e($s->nama_pengurus) ?>"
                                data-npeng="<?= e($s->nip_pengurus) ?>"
                                data-alamat="<?= e($s->alamat) ?>"
                                data-telp="<?= e($s->telepon) ?>"
                                data-active="<?= (int)$s->is_active ?>"
                                title="Edit Master SKPD">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="post" action="<?= site_url('master/skpd') ?>" class="d-inline" onsubmit="return confirm('Hapus SKPD ini?')">
                                <?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$s->id ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus SKPD"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal SKPD Pokok -->
<div class="modal fade" id="modalSkpd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= site_url('master/skpd') ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="skpd_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Tambah SKPD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kode SKPD <span class="text-danger">*</span></label>
                            <input type="text" name="kode_skpd" id="kode_skpd" class="form-control" required maxlength="20" placeholder="1.05.01">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nama SKPD <span class="text-danger">*</span></label>
                            <input type="text" name="nama_skpd" id="nama_skpd" class="form-control" required maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Kepala SKPD</label>
                            <input type="text" name="kepala_skpd" id="kepala_skpd" class="form-control" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP Kepala</label>
                            <input type="text" name="nip_kepala" id="nip_kepala" class="form-control" maxlength="25">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Jabatan Kepala</label>
                            <input type="text" name="jabatan_kepala" id="jabatan_kepala" class="form-control" maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Pengurus Barang</label>
                            <input type="text" name="nama_pengurus" id="nama_pengurus" class="form-control" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP Pengurus Barang</label>
                            <input type="text" name="nip_pengurus" id="nip_pengurus" class="form-control" maxlength="25">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" id="telepon_skpd" class="form-control" maxlength="20">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat_skpd" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="is_active_skpd" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Nomenklatur SKPD Per Tahun -->
<div class="modal fade" id="modalNomenklatur" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>Riwayat & Nomenklatur Per Tahun: <strong id="nom-skpd-title" class="text-primary"></strong></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                    <div>
                        <strong>Petunjuk Nomenklatur:</strong> Jika nama dinas, kode unit, atau pejabat kepala SKPD berubah pada tahun anggaran tertentu (misal Perda baru), tentukan data khusus tahun tersebut di bawah. Laporan cetak dan dokumen lampau akan tetap mengunci nama resmi pada tahun bersangkutan.
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Form Input / Edit Nomenklatur -->
                    <div class="col-lg-5">
                        <div class="card border shadow-none bg-light-subtle">
                            <div class="card-header bg-white py-2 fw-bold text-dark">
                                <i class="bi bi-pencil-square me-1 text-primary"></i> <span id="nom-form-title">Tambah Nomenklatur Tahun</span>
                            </div>
                            <div class="card-body">
                                <form method="post" action="<?= site_url('master/skpd') ?>" id="form-nom">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="action" value="save_nomenklatur">
                                    <input type="hidden" name="skpd_id" id="nom_skpd_id" value="">
                                    <input type="hidden" name="nomenklatur_id" id="nom_id" value="">

                                    <div class="mb-2.5">
                                        <label class="form-label small fw-semibold">Tahun Anggaran <span class="text-danger">*</span></label>
                                        <input type="number" name="tahun_anggaran" id="nom_tahun" class="form-control form-control-sm" required min="2020" max="2099" value="<?= (int) (function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027) ?>">
                                    </div>
                                    <div class="mb-2.5">
                                        <label class="form-label small fw-semibold">Nama Resmi SKPD di Tahun Tersebut <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_skpd" id="nom_nama" class="form-control form-control-sm" required maxlength="255">
                                    </div>
                                    <div class="mb-2.5">
                                        <label class="form-label small fw-semibold">Kode SKPD / Sub Unit</label>
                                        <input type="text" name="kode_skpd" id="nom_kode" class="form-control form-control-sm" maxlength="50">
                                    </div>
                                    <div class="row g-2 mb-2.5">
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">Kepala SKPD</label>
                                            <input type="text" name="kepala_skpd" id="nom_kepala" class="form-control form-control-sm" maxlength="150">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">NIP Kepala</label>
                                            <input type="text" name="nip_kepala" id="nom_nip" class="form-control form-control-sm" maxlength="30">
                                        </div>
                                    </div>
                                    <div class="mb-2.5">
                                        <label class="form-label small fw-semibold">Jabatan Kepala</label>
                                        <input type="text" name="jabatan_kepala" id="nom_jabatan" class="form-control form-control-sm" maxlength="200" placeholder="Kepala Dinas ...">
                                    </div>
                                    <div class="mb-2.5">
                                        <label class="form-label small fw-semibold">Keterangan / Dasar Regulasi</label>
                                        <input type="text" name="keterangan" id="nom_ket" class="form-control form-control-sm" maxlength="255" placeholder="Contoh: Perda No. 4/2026 ttg Perubahan OPD">
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetNomForm()">Batal Edit</button>
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save me-1"></i>Simpan Nomenklatur</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Riwayat Nomenklatur Terdaftar -->
                    <div class="col-lg-7">
                        <div class="card border shadow-none">
                            <div class="card-header bg-white py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-list-check me-1 text-primary"></i> Daftar Nomenklatur Khusus Tahun</span>
                                <span class="badge bg-secondary" id="nom-count">0 Data</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light small">
                                            <tr>
                                                <th>TA</th>
                                                <th>Nama SKPD di Tahun Tersebut</th>
                                                <th>Kepala SKPD</th>
                                                <th>Regulasi / Ket</th>
                                                <th class="text-center" width="70">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="nom-table-body">
                                            <tr><td colspan="5" class="text-center py-4 text-muted small">Memuat data...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    window.openAddModal = function() {
        $('#modal-title').text('Tambah SKPD');
        $('#skpd_id').val('');
        $('#kode_skpd,#nama_skpd,#kepala_skpd,#nip_kepala,#jabatan_kepala,#telepon_skpd,#alamat_skpd,#nama_pengurus,#nip_pengurus').val('');
        $('#is_active_skpd').val('1');
    };

    if($.fn.DataTable) $('#dt-skpd').DataTable({paging:true,ordering:true,language:{url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'}});

    $(document).on('click','.btn-edit-skpd',function(){
        const d=$(this).data();
        $('#modal-title').text('Edit SKPD');
        $('#skpd_id').val(d.id);
        $('#kode_skpd').val(d.kode);
        $('#nama_skpd').val(d.nama);
        $('#kepala_skpd').val(d.kepala);
        $('#nip_kepala').val(d.nip);
        $('#jabatan_kepala').val(d.jabatan);
        $('#telepon_skpd').val(d.telp);
        $('#nama_pengurus').val(d.peng);
        $('#nip_pengurus').val(d.npeng);
        $('#alamat_skpd').val(d.alamat);
        $('#is_active_skpd').val(d.active);
        new bootstrap.Modal(document.getElementById('modalSkpd')).show();
    });

    let currentSkpdData = null;
    let currentNomList = [];

    window.resetNomForm = function() {
        $('#nom-form-title').text('Tambah Nomenklatur Tahun');
        $('#nom_id').val('');
        if (currentSkpdData) {
            $('#nom_skpd_id').val(currentSkpdData.id);
            $('#nom_nama').val(currentSkpdData.nama_skpd || '');
            $('#nom_kode').val(currentSkpdData.kode_skpd || '');
            $('#nom_kepala').val(currentSkpdData.kepala_skpd || '');
            $('#nom_nip').val(currentSkpdData.nip_kepala || '');
            $('#nom_jabatan').val(currentSkpdData.jabatan_kepala || '');
        }
        $('#nom_ket').val('');
    };

    $(document).on('click', '.btn-nomenklatur-skpd', function() {
        const d = $(this).data();
        currentSkpdData = {
            id: d.id,
            nama_skpd: d.nama,
            kode_skpd: d.kode,
            kepala_skpd: d.kepala,
            nip_kepala: d.nip,
            jabatan_kepala: d.jabatan
        };
        $('#nom-skpd-title').text(d.nama);
        resetNomForm();

        const modalEl = document.getElementById('modalNomenklatur');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        loadNomenklaturList(d.id);
    });

    function loadNomenklaturList(skpdId) {
        $('#nom-table-body').html('<tr><td colspan="5" class="text-center py-3 text-muted small"><span class="spinner-border spinner-border-sm me-1"></span>Memuat riwayat...</td></tr>');
        
        $.getJSON('<?= site_url('master/skpd/nomenklatur') ?>/' + skpdId, function(res) {
            if (!res.status) {
                $('#nom-table-body').html('<tr><td colspan="5" class="text-center py-3 text-danger small">' + (res.message || 'Gagal memuat') + '</td></tr>');
                return;
            }
            currentNomList = res.list || [];
            $('#nom-count').text(currentNomList.length + ' Data');

            if (currentNomList.length === 0) {
                $('#nom-table-body').html('<tr><td colspan="5" class="text-center py-4 text-muted small">Belum ada aturan nomenklatur khusus per tahun. Sistem akan menggunakan data master pokok SKPD.</td></tr>');
                return;
            }

            let html = '';
            currentNomList.forEach(function(row) {
                html += '<tr>' +
                    '<td><span class="badge bg-primary">TA ' + row.tahun_anggaran + '</span></td>' +
                    '<td><strong>' + (row.nama_skpd || '-') + '</strong><br><small class="text-muted">Kode: ' + (row.kode_skpd || '-') + '</small></td>' +
                    '<td class="small">' + (row.kepala_skpd || '-') + (row.nip_kepala ? '<br><span class="text-muted">NIP: ' + row.nip_kepala + '</span>' : '') + '</td>' +
                    '<td class="small text-muted">' + (row.keterangan || '-') + '</td>' +
                    '<td class="text-center">' +
                        '<div class="btn-group btn-group-sm">' +
                            '<button type="button" class="btn btn-outline-primary btn-xs btn-edit-nom" data-id="' + row.id + '" title="Edit"><i class="bi bi-pencil"></i></button>' +
                            '<form method="post" action="<?= site_url('master/skpd') ?>" class="d-inline" onsubmit="return confirm(\'Hapus aturan nomenklatur tahun ' + row.tahun_anggaran + '?\')">' +
                                '<?= csrf_input() ?>' +
                                '<input type="hidden" name="action" value="delete_nomenklatur">' +
                                '<input type="hidden" name="nomenklatur_id" value="' + row.id + '">' +
                                '<button type="submit" class="btn btn-outline-danger btn-xs" title="Hapus"><i class="bi bi-trash"></i></button>' +
                            '</form>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
            });
            $('#nom-table-body').html(html);
        }).fail(function() {
            $('#nom-table-body').html('<tr><td colspan="5" class="text-center py-3 text-danger small">Gagal memuat data dari server.</td></tr>');
        });
    }

    $(document).on('click', '.btn-edit-nom', function() {
        const nomId = $(this).data('id');
        const item = currentNomList.find(function(x) { return x.id == nomId; });
        if (!item) return;

        $('#nom-form-title').text('Edit Nomenklatur TA ' + item.tahun_anggaran);
        $('#nom_id').val(item.id);
        $('#nom_skpd_id').val(item.skpd_id);
        $('#nom_tahun').val(item.tahun_anggaran);
        $('#nom_nama').val(item.nama_skpd);
        $('#nom_kode').val(item.kode_skpd || '');
        $('#nom_kepala').val(item.kepala_skpd || '');
        $('#nom_nip').val(item.nip_kepala || '');
        $('#nom_jabatan').val(item.jabatan_kepala || '');
        $('#nom_ket').val(item.keterangan || '');
    });
});
</script>

