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
            <button type="button" class="btn btn-secondary shadow-sm" disabled title="Jadwal penyusunan RKBMD TA <?= (int)($thAktif ?? 2027) ?> sedang DITUTUP oleh Administrator BKAD">
                <i class="bi bi-lock-fill me-1"></i> Menunggu Jadwal Dibuka
            </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Banner Status Jadwal Pengusulan RKBMD dengan Live Countdown -->
<?php if (!empty($isPeriodeBuka) && !empty($periodeAktif)): ?>
<?php 
    $tglSelesaiRkbmdFormatted = date('d F Y', strtotime($periodeAktif->tanggal_selesai));
    $tglMulaiRkbmdFormatted   = !empty($periodeAktif->tanggal_mulai) ? date('d M Y', strtotime($periodeAktif->tanggal_mulai)) : '';
    $targetIsoRkbmd           = date('Y-m-d\TH:i:s', strtotime($periodeAktif->tanggal_selesai . ' 23:59:59'));
?>
<div class="card border-0 shadow-sm mb-4 overflow-hidden sipa-schedule-card">
    <div class="card-body p-3 p-md-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge sipa-badge-pulse text-white px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
                        <span class="pulse-dot"></span>
                        <i class="bi bi-broadcast"></i> JADWAL PENYUSUNAN RKBMD AKTIF
                    </span>
                    <span class="badge bg-light text-dark border fw-medium">
                        TA <?= (int)($thAktif ?? 2027) ?>
                    </span>
                </div>
                <h5 class="fw-bold mb-1 text-dark">
                    <?= e($periodeAktif->nama_periode ?? "Penyusunan RKBMD TA {$thAktif}") ?>
                </h5>
                <p class="text-muted small mb-0 d-flex flex-wrap align-items-center gap-2">
                    <span><i class="bi bi-calendar2-range me-1 text-success"></i> <?= $tglMulaiRkbmdFormatted ?> s.d <strong><?= $tglSelesaiRkbmdFormatted ?> (23:59 WITA)</strong></span>
                    <?php if (!empty($periodeAktif->keterangan)): ?>
                        <span class="text-secondary">&bull; <?= e($periodeAktif->keterangan) ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-inline-flex flex-column align-items-lg-end">
                    <div class="text-uppercase fw-bold text-muted mb-1.5" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="bi bi-hourglass-split text-warning me-1"></i> Sisa Waktu Pengusulan:
                    </div>
                    <div class="sipa-countdown-wrapper" id="rkbmdCountdown" data-target="<?= $targetIsoRkbmd ?>">
                        <div class="sipa-countdown-unit">
                            <div class="sipa-countdown-num" id="rkbmd-cd-days">00</div>
                            <div class="sipa-countdown-label">Hari</div>
                        </div>
                        <div class="sipa-countdown-sep">:</div>
                        <div class="sipa-countdown-unit">
                            <div class="sipa-countdown-num" id="rkbmd-cd-hours">00</div>
                            <div class="sipa-countdown-label">Jam</div>
                        </div>
                        <div class="sipa-countdown-sep">:</div>
                        <div class="sipa-countdown-unit">
                            <div class="sipa-countdown-num" id="rkbmd-cd-mins">00</div>
                            <div class="sipa-countdown-label">Menit</div>
                        </div>
                        <div class="sipa-countdown-sep">:</div>
                        <div class="sipa-countdown-unit">
                            <div class="sipa-countdown-num" id="rkbmd-cd-secs">00</div>
                            <div class="sipa-countdown-label">Detik</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cdEl = document.getElementById('rkbmdCountdown');
    if (cdEl) {
        const targetIso = cdEl.getAttribute('data-target');
        if (targetIso) {
            const targetTime = new Date(targetIso).getTime();
            const elDays = document.getElementById('rkbmd-cd-days');
            const elHours = document.getElementById('rkbmd-cd-hours');
            const elMins = document.getElementById('rkbmd-cd-mins');
            const elSecs = document.getElementById('rkbmd-cd-secs');
            function pad(n) { return (n < 10 ? '0' : '') + n; }
            function updateTimer() {
                const diff = targetTime - new Date().getTime();
                if (diff <= 0) {
                    cdEl.innerHTML = '<span class="badge bg-danger px-3 py-2 fw-bold fs-6 shadow-sm"><i class="bi bi-alarm-fill me-1"></i> Batas Waktu Telah Berakhir</span>';
                    return;
                }
                if (elDays) elDays.textContent = pad(Math.floor(diff / (1000 * 60 * 60 * 24)));
                if (elHours) elHours.textContent = pad(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)));
                if (elMins) elMins.textContent = pad(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)));
                if (elSecs) elSecs.textContent = pad(Math.floor((diff % (1000 * 60)) / 1000));
                if (diff < 24 * 60 * 60 * 1000) cdEl.classList.add('urgent');
            }
            updateTimer();
            setInterval(updateTimer, 1000);
        }
    }
});
</script>
<?php else: ?>
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning bg-white">
    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-dark">Jadwal Penyusunan RKBMD Ditutup</h6>
                <div class="text-muted small">Pengusulan RKBMD untuk <strong>Tahun Anggaran <?= (int)($thAktif ?? 2027) ?></strong> saat ini belum dibuka atau telah berakhir. Menunggu pembukaan jadwal oleh BKAD.</div>
            </div>
        </div>
        <span class="badge bg-danger px-3 py-2 fw-semibold">
            <i class="bi bi-lock-fill me-1"></i> JADWAL DITUTUP
        </span>
    </div>
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
                                    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Jadwal penyusunan RKBMD TA <?= (int)$u->tahun_anggaran ?> telah DITUTUP oleh BKAD">
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
