<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>RKBMD <?= e(label_jenis($jenis)) ?> - <?= e($usulan->nomor_usulan) ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Times New Roman',serif;font-size:11pt;color:#000;background:#fff;}
.kop{text-align:center;border-bottom:3px double #000;padding-bottom:8px;margin-bottom:16px;}
.kop h2{font-size:14pt;font-weight:bold;text-transform:uppercase;}
.kop h3{font-size:13pt;font-weight:bold;text-transform:uppercase;}
.kop p{font-size:10pt;}
h4{text-align:center;text-transform:uppercase;margin:12px 0 8px;font-size:12pt;text-decoration:underline;}
.info-table{width:100%;margin-bottom:12px;font-size:10pt;}
.info-table td{padding:2px 4px;vertical-align:top;}
.info-table td:first-child{width:35%;}
table.data{width:100%;border-collapse:collapse;font-size:9pt;margin-bottom:16px;}
table.data th,table.data td{border:1px solid #000;padding:4px 6px;vertical-align:top;}
table.data th{background:#e8e8e8;text-align:center;font-weight:bold;}
table.data .text-right{text-align:right;}
table.data .text-center{text-align:center;}
.ttd-section{display:flex;justify-content:space-between;margin-top:20px;}
.ttd-box{text-align:center;width:220px;}
.ttd-box .ttd-space{height:70px;}
.footer-note{font-size:9pt;color:#555;margin-top:8px;}
@media print{body{margin:0;}@page{margin:1.5cm;size:A4 landscape;}}
</style>
</head>
<body>
<div class="kop">
    <h2><?= e($app_owner) ?></h2>
    <h3><?= e($usulan->nama_skpd) ?></h3>
    <p><?= e($skpd_info->alamat) ?> - Telp. <?= e($skpd_info->telepon) ?></p>
</div>

<h4>Rencana Kebutuhan Barang Milik Daerah (RKBMD)<br>
    Jenis: <?= e(label_jenis($jenis)) ?></h4>

<table class="info-table">
    <tr><td>Nomor Usulan</td><td>: <strong><?= e($usulan->nomor_usulan) ?></strong></td><td>SKPD</td><td>: <?= e($usulan->nama_skpd) ?></td></tr>
    <tr><td>Tahun Anggaran</td><td>: <?= (int)$usulan->tahun_anggaran ?></td><td>Kode SKPD</td><td>: <?= e($usulan->kode_skpd) ?></td></tr>
    <tr><td>Tanggal Usulan</td><td>: <?= tanggal_id($usulan->tanggal_usulan) ?></td><td>Status</td><td>: <?= strtoupper($usulan->status) ?></td></tr>
    <?php if ($usulan->keterangan): ?><tr><td>Keterangan</td><td colspan="3">: <?= e($usulan->keterangan) ?></td></tr><?php endif; ?>
</table>

<table class="data">
    <thead>
        <tr>
            <th width="30">No</th>
            <th>Kode Barang</th>
            <th>Nama Barang / Jenis BMD</th>
            <?php if ($jenis === 'pengadaan'): ?>
            <th>Program / Kegiatan</th>
            <th width="70">Jml Usulan</th>
            <th width="70">Keb. Riil</th>
            <th width="100">Harga Satuan (Rp)</th>
            <th width="120">Total Harga (Rp)</th>
            <?php elseif ($jenis === 'pemeliharaan'): ?>
            <th>Nama Pemeliharaan</th>
            <th width="80">Jml Pemeliharaan</th>
            <th width="80">Kondisi (B/RR/RB)</th>
            <th width="100">Harga Satuan (Rp)</th>
            <th width="120">Total Harga (Rp)</th>
            <?php elseif ($jenis === 'pemanfaatan'): ?>
            <th>Bentuk Pemanfaatan</th>
            <th>No Register</th>
            <th width="60">Thn</th>
            <th width="120">Harga Perolehan (Rp)</th>
            <th width="80">Kondisi (B/RR/RB)</th>
            <?php elseif ($jenis === 'pemindahtanganan'): ?>
            <th>Bentuk Pemindahtanganan</th>
            <th>No Register</th>
            <th>Spesifikasi</th>
            <th width="50">Thn</th>
            <th width="120">Harga Perolehan (Rp)</th>
            <?php elseif ($jenis === 'penghapusan'): ?>
            <th>Kategori Penghapusan</th>
            <th>No Register</th>
            <th>Spesifikasi</th>
            <th width="50">Thn</th>
            <th width="120">Harga Perolehan (Rp)</th>
            <?php endif; ?>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($detail)): ?>
        <tr><td colspan="10" class="text-center" style="padding:10px;">Tidak ada data</td></tr>
        <?php else: $totalNilai=0; foreach ($detail as $i => $d): ?>
        <tr>
            <td class="text-center"><?= $i+1 ?></td>
            <td><?= e($d->kode_barang) ?></td>
            <td><?= e($d->barang_nama) ?></td>
            <?php if ($jenis === 'pengadaan'): $totalNilai += $d->total_harga; ?>
            <td><?= e($d->program_kegiatan) ?></td>
            <td class="text-center"><?= (int)$d->usulan_jumlah ?> <?= e($d->usulan_satuan) ?></td>
            <td class="text-center"><?= (int)$d->kebutuhan_riil_jumlah ?> <?= e($d->kebutuhan_riil_satuan) ?></td>
            <td class="text-right"><?= rupiah_excel($d->harga_satuan) ?></td>
            <td class="text-right"><?= rupiah_excel($d->total_harga) ?></td>
            <?php elseif ($jenis === 'pemeliharaan'): $totalNilai += $d->total_harga; ?>
            <td><?= e($d->nama_pemeliharaan) ?></td>
            <td class="text-center"><?= (int)$d->jumlah_pemeliharaan ?> <?= e($d->satuan_pemeliharaan) ?></td>
            <td class="text-center"><?= (int)$d->kondisi_b ?> / <?= (int)$d->kondisi_rr ?> / <?= (int)$d->kondisi_rb ?></td>
            <td class="text-right"><?= rupiah_excel($d->harga_satuan) ?></td>
            <td class="text-right"><?= rupiah_excel($d->total_harga) ?></td>
            <?php elseif ($jenis === 'pemanfaatan'): ?>
            <td><?= e(ucwords(str_replace('_',' ',$d->bentuk_pemanfaatan))) ?></td>
            <td><?= e($d->no_register) ?></td>
            <td class="text-center"><?= (int)$d->tahun_perolehan ?: '-' ?></td>
            <td class="text-right"><?= rupiah_excel($d->harga_perolehan) ?></td>
            <td class="text-center"><?= (int)$d->kondisi_b ?>/<?= (int)$d->kondisi_rr ?>/<?= (int)$d->kondisi_rb ?></td>
            <?php elseif ($jenis === 'pemindahtanganan'): ?>
            <td><?= e(ucwords(str_replace('_',' ',$d->bentuk_pemindahtanganan))) ?></td>
            <td><?= e($d->no_register) ?></td>
            <td><?= e($d->spesifikasi) ?></td>
            <td class="text-center"><?= (int)$d->tahun_perolehan ?></td>
            <td class="text-right"><?= rupiah_excel($d->harga_perolehan) ?></td>
            <?php elseif ($jenis === 'penghapusan'): ?>
            <td><?= e(ucwords(str_replace('_',' ',$d->kategori_penghapusan))) ?></td>
            <td><?= e($d->no_register) ?></td>
            <td><?= e($d->spesifikasi) ?></td>
            <td class="text-center"><?= (int)$d->tahun_perolehan ?></td>
            <td class="text-right"><?= rupiah_excel($d->harga_perolehan) ?></td>
            <?php endif; ?>
            <td><?= e($d->keterangan) ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </tbody>
    <?php if (in_array($jenis,['pengadaan','pemeliharaan']) && !empty($detail)): ?>
    <tfoot>
        <tr>
            <td colspan="<?= $jenis==='pengadaan' ? 7 : 7 ?>" class="text-right" style="font-weight:bold;">J U M L A H</td>
            <td class="text-right" style="font-weight:bold;"><?= rupiah_excel($usulan->total_nilai) ?></td>
            <td></td>
        </tr>
    </tfoot>
    <?php endif; ?>
</table>

<div class="ttd-section">
    <div class="ttd-box">
        <p>Mengetahui,</p>
        <p>Kepala SKPD</p>
        <div class="ttd-space"></div>
        <p><strong><?= e($skpd_info->kepala_skpd ?: '__________________') ?></strong></p>
        <p>NIP. <?= e($skpd_info->nip_kepala ?: '...........................') ?></p>
    </div>
    <div class="ttd-box">
        <p><?= e($app_kabupaten) ?>, <?= tanggal_id(date('Y-m-d')) ?></p>
        <p>Pengurus Barang Pengguna</p>
        <div class="ttd-space"></div>
        <p><?= e($skpd_info->nama_pengurus ?: '__________________') ?></strong></p>
        <p>NIP. <?= e($skpd_info->nip_pengurus ?: '...........................') ?></p>
    </div>
</div>

<div class="footer-note">
    Dicetak oleh sistem SIPA pada <?= date('d/m/Y H:i:s') ?>
</div>

<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
