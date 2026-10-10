<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Rekapitulasi - SIPA Kabupaten Tapin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; background: #fff; color: #000; }
        .kop-header { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .kop-header h4 { font-size: 16pt; font-weight: bold; margin-bottom: 2px; }
        .kop-header h3 { font-size: 18pt; font-weight: bold; margin-bottom: 2px; }
        .kop-header p { font-size: 10pt; margin-bottom: 0; }
        .table-print { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-print th, .table-print td { border: 1px solid #000; padding: 6px 8px; font-size: 10pt; }
        .table-print th { background-color: #f2f2f2; text-align: center; }
        .ttd-box { margin-top: 40px; page-break-inside: avoid; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container-fluid">
    <div class="no-print my-3 text-end">
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Dokumen</button>
        <button class="btn btn-secondary btn-sm" onclick="window.close()">Tutup</button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-header">
        <h4>PEMERINTAH KABUPATEN TAPIN</h4>
        <h3>BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH</h3>
        <p>Jl. Datu Nuraya Kawasan Rantau Baru, Rantau, Kabupaten Tapin, Kalimantan Selatan</p>
        <p>Laman: <em>sipa.bkadtapinkab.online</em> &bull; Email: <em>bpkad@tapinkab.go.id</em></p>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase mb-1">
            <?php if ($modul === 'ssh_sbu'): ?>
                LEMBAR REKAPITULASI STANDAR SATUAN HARGA (SSH) & STANDAR BIAYA UMUM (SBU)
            <?php elseif ($modul === 'kepatuhan'): ?>
                MATRIKS MONITORING KEPATUHAN PENGUSULAN ASET SKPD
            <?php else: ?>
                LEMBAR REKAPITULASI RENCANA KEBUTUHAN BARANG MILIK DAERAH (RKBMD)
            <?php endif; ?>
        </h5>
        <div class="small">
            Tahun Anggaran: <strong><?= (int)$tahun ?></strong>
            <?php if (!empty($skpdInfo)): ?>
                &bull; SKPD: <strong><?= e($skpdInfo->nama_skpd) ?></strong>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ringkasan Singkat -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="border p-2">
                <small class="text-muted d-block">Total Usulan Terdata</small>
                <strong><?= number_format($kpi['total_usulan']) ?> Usulan</strong>
            </div>
        </div>
        <div class="col-4">
            <div class="border p-2">
                <small class="text-muted d-block">Total Nilai Usulan (Pagu)</small>
                <strong><?= rupiah($kpi['total_nilai']) ?></strong>
            </div>
        </div>
        <div class="col-4">
            <div class="border p-2">
                <small class="text-muted d-block">Total Nilai Disetujui / Ditetapkan</small>
                <strong><?= rupiah($kpi['nilai_disetujui']) ?></strong>
            </div>
        </div>
    </div>

    <!-- Tabel Data Rekap -->
    <?php if ($modul === 'ssh_sbu'): ?>
        <table class="table-print">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>Kode Usulan</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Nama Barang / Jasa</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Usulan</th>
                    <th class="text-end">Harga Tetap</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($standarList)): ?>
                <tr><td colspan="9" class="text-center py-3">Tidak ada data usulan standar harga.</td></tr>
                <?php else: foreach ($standarList as $i => $row): ?>
                <tr>
                    <td class="text-center"><?= $i+1 ?></td>
                    <td><?= e($row->kode_usulan) ?></td>
                    <td class="text-center"><?= e($row->tipe) ?></td>
                    <td><?= e($row->kategori) ?></td>
                    <td><?= e($row->uraian) ?></td>
                    <td class="text-center"><?= e($row->satuan) ?></td>
                    <td class="text-end"><?= rupiah($row->harga_usulan) ?></td>
                    <td class="text-end"><?= $row->harga_ditetapkan ? rupiah($row->harga_ditetapkan) : '-' ?></td>
                    <td class="text-center"><?= e($row->status_proses) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    <?php elseif ($modul === 'kepatuhan'): ?>
        <table class="table-print">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>Kode</th>
                    <th>Nama Satuan Kerja (SKPD)</th>
                    <th class="text-center">RKBMD</th>
                    <th class="text-center">SSH/SBU</th>
                    <th class="text-center">Total Usulan</th>
                    <th class="text-end">Total Nilai Usulan</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kepatuhan)): ?>
                <tr><td colspan="8" class="text-center py-3">Tidak ada data SKPD.</td></tr>
                <?php else: foreach ($kepatuhan as $i => $row): 
                    $stat = ($row->total_rkbmd > 0 && ($row->total_ssh > 0 || $row->total_sbu > 0)) ? 'Lengkap' : (($row->grand_total_usulan > 0) ? 'Sebagian' : 'Belum Ada');
                ?>
                <tr>
                    <td class="text-center"><?= $i+1 ?></td>
                    <td><?= e($row->kode_skpd) ?></td>
                    <td><?= e($row->nama_skpd) ?></td>
                    <td class="text-center"><?= (int)$row->total_rkbmd ?></td>
                    <td class="text-center"><?= (int)($row->total_ssh + $row->total_sbu) ?></td>
                    <td class="text-center fw-bold"><?= (int)$row->grand_total_usulan ?></td>
                    <td class="text-end"><?= rupiah($row->grand_total_nilai) ?></td>
                    <td class="text-center"><?= $stat ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    <?php else: ?>
        <table class="table-print">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>Nomor Usulan</th>
                    <th>Jenis Perencanaan</th>
                    <th>SKPD Pengusul</th>
                    <th>Tanggal</th>
                    <th class="text-center">Item</th>
                    <th class="text-end">Total Nilai Usulan</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rkbmdList)): ?>
                <tr><td colspan="8" class="text-center py-3">Tidak ada data usulan RKBMD.</td></tr>
                <?php else: foreach ($rkbmdList as $i => $row): ?>
                <tr>
                    <td class="text-center"><?= $i+1 ?></td>
                    <td><strong><?= e($row->nomor_usulan) ?></strong></td>
                    <td><?= strtoupper(label_jenis($row->jenis_usulan)) ?></td>
                    <td><?= e($row->nama_skpd) ?></td>
                    <td class="text-center"><?= tanggal_id($row->tanggal_usulan) ?></td>
                    <td class="text-center"><?= (int)$row->total_item ?></td>
                    <td class="text-end"><?= rupiah($row->total_nilai) ?></td>
                    <td class="text-center"><?= strtoupper($row->status) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Tanda Tangan -->
    <div class="row ttd-box">
        <div class="col-7"></div>
        <div class="col-5 text-center">
            <p class="mb-1">Rantau, <?= tanggal_id(date('Y-m-d')) ?></p>
            <p class="mb-5"><strong>KEPALA BADAN PENGELOLAAN KEUANGAN<br>DAN ASET DAERAH KABUPATEN TAPIN</strong></p>
            <br><br>
            <p class="mb-0 text-decoration-underline fw-bold">HARVEY, S.E., M.M.</p>
            <p class="mb-0">NIP. 19740512 199803 1 005</p>
        </div>
    </div>
</div>

</body>
</html>
