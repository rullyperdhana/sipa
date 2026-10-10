<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title"><i class="bi bi-clipboard-data me-2"></i>Daftar Usulan <?= e(label_jenis($jenis)) ?></h1>
            <p class="page-subtitle">Kelola usulan RKBMD <?= e(label_jenis($jenis)) ?>.</p>
        </div>
        <?php if (in_array($this->auth->user()->role, ['admin','skpd'])): ?>
            <?php if (!empty($isPeriodeBuka) || in_array($this->auth->user()->role, ['admin', 'pimpinan'], TRUE)): ?>
            <a href="<?= site_url("rkbmd/{$jenis}/create") ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Buat Usulan Baru
            </a>
            <?php else: ?>
            <button type="button" class="btn btn-secondary shadow-sm" disabled title="Jadwal penyusunan RKBMD TA <?= (int)($thAktif ?? 2027) ?> sedang DITUTUP oleh Administrator BPKAD">
                <i class="bi bi-lock-fill me-1"></i> Menunggu Jadwal Dibuka
            </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Banner Status Jadwal Pengusulan RKBMD -->
<?php if (!empty($isPeriodeBuka) && !empty($periodeAktif)): ?>
<div class="alert alert-success border-success shadow-sm d-flex justify-content-between align-items-center mb-3 py-2 px-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-broadcast fs-5 text-success"></i>
        <div>
            <strong>Jadwal Penyusunan RKBMD Aktif:</strong> <?= e($periodeAktif->nama_periode ?? "RKBMD TA {$thAktif}") ?> (TA <?= (int)($thAktif ?? 2027) ?>)
            <span class="text-muted small ms-2">&bull; Periode: <strong><?= date('d M Y', strtotime($periodeAktif->tanggal_mulai)) ?> s/d <?= date('d M Y', strtotime($periodeAktif->tanggal_selesai)) ?></strong></span>
        </div>
    </div>
    <span class="badge bg-success py-1.5 px-2.5"><i class="bi bi-unlock-fill me-1"></i>JADWAL DIBUKA</span>
</div>
<?php else: ?>
<div class="alert alert-warning border-warning shadow-sm d-flex justify-content-between align-items-center mb-3 py-2 px-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-clock-history fs-5 text-warning"></i>
        <div>
            <strong>Jadwal Penyusunan RKBMD Ditutup:</strong> Pengusulan RKBMD untuk <strong>Tahun Anggaran <?= (int)($thAktif ?? 2027) ?></strong> saat ini belum dibuka atau telah berakhir.
            <div class="text-muted small">Operator SKPD tidak dapat membuat usulan baru atau mengubah usulan pada tahun anggaran ini hingga jadwal resmi dibuka oleh BPKAD.</div>
        </div>
    </div>
    <span class="badge bg-danger py-1.5 px-2.5"><i class="bi bi-lock-fill me-1"></i>JADWAL DITUTUP</span>
</div>
<?php endif; ?>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" class="form-control" name="q" value="<?= e($filter['q'] ?? '') ?>" placeholder="Nomor usulan, SKPD...">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <?php foreach (['draft'=>'Draft','diajukan'=>'Diajukan','diverifikasi'=>'Diverifikasi','disetujui'=>'Disetujui','ditolak'=>'Ditolak','revisi'=>'Perlu Revisi'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= ($filter['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Tahun</label>
                <select class="form-select" name="tahun">
                    <option value="">Semua</option>
                    <?php foreach ($periode as $p): ?>
                    <option value="<?= (int)$p->tahun ?>" <?= (int)($filter['tahun']??0) === (int)$p->tahun ? 'selected' : '' ?>><?= (int)$p->tahun ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i> Filter</button>
                <a href="<?= site_url("rkbmd/{$jenis}") ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt-usulan">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Nomor Usulan</th>
                        <?php if ($this->auth->user()->role !== 'skpd'): ?><th>SKPD</th><?php endif; ?>
                        <th>Tahun</th>
                        <th>Tanggal</th>
                        <th class="text-end">Item</th>
                        <th class="text-end">Total Nilai</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usulan as $i => $u): ?>
                    <tr>
                        <td class="text-muted"><?= $i+1 ?></td>
                        <td>
                            <strong><?= e($u->nomor_usulan) ?></strong>
                            <?php if ($u->keterangan): ?><br><small class="text-muted"><?= e(substr($u->keterangan,0,50)) ?></small><?php endif; ?>
                        </td>
                        <?php if ($this->auth->user()->role !== 'skpd'): ?>
                        <td><small><?= e($u->kode_skpd) ?></small><br><?= e($u->nama_skpd) ?></td>
                        <?php endif; ?>
                        <td><?= (int)$u->tahun_anggaran ?></td>
                        <td><?= tanggal_id($u->tanggal_usulan) ?></td>
                        <td class="text-end"><?= number_format((int)$u->total_item) ?></td>
                        <td class="text-end"><?= rupiah($u->total_nilai) ?></td>
                        <td class="text-center"><?= badge_status($u->status) ?></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= site_url("rkbmd/{$jenis}/detail/{$u->id}") ?>" class="btn btn-outline-secondary" title="Lihat Detail"><i class="bi bi-eye"></i></a>
                                <?php if (in_array($u->status,['draft','revisi']) && in_array($this->auth->user()->role,['admin','skpd'])): ?>
                                    <?php $isItemJadwalBuka = in_array($this->auth->user()->role, ['admin', 'pimpinan'], TRUE) || $this->master_model->isPeriodeBuka($u->tahun_anggaran); ?>
                                    <?php if ($isItemJadwalBuka): ?>
                                    <a href="<?= site_url("rkbmd/{$jenis}/edit/{$u->id}") ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <?php else: ?>
                                    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Jadwal penyusunan RKBMD TA <?= (int)$u->tahun_anggaran ?> telah DITUTUP oleh BPKAD">
                                        <button class="btn btn-outline-secondary" disabled><i class="bi bi-lock-fill"></i></button>
                                    </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?= site_url("rkbmd/{$jenis}/cetak/{$u->id}") ?>" target="_blank" class="btn btn-outline-info" title="Cetak PDF"><i class="bi bi-printer"></i></a>
                                <a href="<?= site_url("rkbmd/{$jenis}/excel/{$u->id}") ?>" class="btn btn-outline-success" title="Download Excel"><i class="bi bi-file-earmark-excel"></i></a>
                                <?php if (in_array($u->status,['draft','ditolak']) && in_array($this->auth->user()->role,['admin','skpd'])): ?>
                                <button class="btn btn-outline-danger btn-delete" data-url="<?= site_url("rkbmd/{$jenis}/delete/{$u->id}") ?>" data-nomor="<?= e($u->nomor_usulan) ?>" title="Hapus"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    if($.fn.DataTable) {
        $('#dt-usulan').DataTable({
            paging:true, searching:false, ordering:true, info:true,
            order:[],
            language:{
                url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                emptyTable: 'Belum ada usulan <?= e(label_jenis($jenis)) ?>.'
            }
        });
    }
    $(document).on('click','.btn-delete',function(){
        const url=$(this).data('url'), nomor=$(this).data('nomor');
        Swal.fire({
            icon:'warning', title:'Hapus Usulan?',
            html:'Usulan <strong>'+nomor+'</strong> akan dihapus permanen.',
            showCancelButton:true, confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', confirmButtonColor:'#dc3545'
        }).then(r=>{ if(r.isConfirmed) window.location.href=url; });
    });
});
</script>
