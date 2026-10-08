<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>RKBMD <?= e(label_jenis($jenis)) ?> - <?= e($usulan->nomor_usulan) ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Times New Roman',serif;font-size:10pt;color:#000;background:#fff;}
.page-title{text-align:center;font-weight:bold;font-size:11pt;margin-bottom:3px;}
.subtitle{text-align:center;font-weight:bold;font-size:10pt;text-transform:uppercase;margin-bottom:8px;}
.kop{text-align:center;padding-bottom:3px;margin-bottom:8px;}
.kop h2{font-size:12pt;font-weight:bold;text-transform:uppercase;}
.kop h3{font-size:11pt;font-weight:bold;text-transform:uppercase;}
.kop p{font-size:9pt;}
.header-info{width:100%;margin-bottom:8px;font-size:9pt;}
.header-info td{padding:2px 3px;vertical-align:top;border:none;}
.header-info td.label{font-weight:bold;width:18%;}
.header-info td.colon{width:2%;text-align:center;}
.header-info td.value{width:30%;}
table.data{width:100%;border-collapse:collapse;font-size:8.5pt;margin-bottom:8px;}
table.data th,table.data td{border:1px solid #000;padding:2px 3px;vertical-align:top;text-align:center;}
table.data th{background:#d3d3d3;text-align:center;font-weight:bold;font-size:8pt;}
table.data .text-right{text-align:right;}
table.data .text-left{text-align:left;}
table.data .text-center{text-align:center;}
table.data .col-no{width:25px;}
table.data .col-kode{width:50px;}
table.data .col-nama{width:130px;}
table.data .col-jml{width:40px;}
table.data .col-satuan{width:35px;}
table.data .col-nominal{width:70px;}
.sub-header{background:#f0f0f0;font-weight:bold;text-align:center;font-size:8pt;}
.ttd-section{display:flex;justify-content:space-between;margin-top:12px;}
.ttd-box{text-align:center;width:210px;font-size:9pt;}
.ttd-box .ttd-space{height:60px;}
.footer-note{font-size:8pt;color:#555;margin-top:6px;text-align:center;}
@media print{body{margin:0;}@page{margin:1cm;size:A4 landscape;}}
</style>
</head>
<body>
<div class="page-title">Lampiran 2. Format Usulan RKBMD Pengadaan</div>
<div class="subtitle">Usulan Rencana Kebutuhan Barang Milik Daerah Pengadaan</div>

<table class="header-info">
<tr>
    <td class="label">PEMERINTAH/PROVINSI</td><td class="colon">:</td><td class="value">KALIMANTAN SELATAN</td>
    <td class="label">KABUPATEN/KOTA</td><td class="colon">:</td><td class="value"><?= e(strtoupper($usulan->nama_skpd)) ?></td>
</tr>
<tr>
    <td class="label">SEKSI</td><td class="colon">:</td><td class="value">-</td>
    <td class="label">TAHUN</td><td class="colon">:</td><td class="value"> <?= (int)$usulan->tahun_anggaran ?></td>
</tr>
</table>


<table class="data">
    <thead>
        <tr>
            <th rowspan="2" class="col-no">NO</th>
            <th rowspan="2"class="sub-header">KUASA PENGGUNA BARANG/ <br>PROGRAM/KEGIATAN/OUTPUT</th>
            <th colspan="4" class="sub-header">USULAN BARANG MILIK DAERAH</th>
            <th colspan="2" class="sub-header">KEBUTUHAN MAKSIMUM</th>
            <th colspan="4" class="sub-header">DATA BARANG YANG DAPAT DIOPTIMALISASIKAN</th>
            <th colspan="2" class="sub-header">KEBUTUHAN RIIL BARANG MILIK</th>
            <th rowspan="2" class="sub-header" width="50">KETERANGAN</th>
        </tr>
        <tr>
            <th class="col-kode">KODE BARANG</th>
            <th class="col-nama">NAMA</th>
            <th class="col-jml">JUMLAH</th>
            <th class="col-satuan">SATUAN</th>
            <th class="col-jml">JUMLAH</th>
            <th class="col-satuan">SATUAN</th>
            <th class="col-kode">KODE BARANG</th>
            <th class="col-nama">NAMA</th>
            <th class="col-jml">JUMLAH</th>
            <th class="col-satuan">SATUAN</th>
            <th class="col-jml">JUMLAH</th>
            <th class="col-satuan">SATUAN</th>
        </tr>
        <tr>
            <th width="35">1</th>
            <th width="35">2</th>
            <th width="35">3</th>
            <th width="35">4</th>
            <th width="35">5</th>
            <th width="35">6</th>
            <th width="35">7</th>
            <th width="35">8</th>
            <th width="35">9</th>
            <th width="35">10</th>
            <th width="35">11</th>
            <th width="35">12</th>
            <th width="35">13</th>
            <th width="35">14</th>
            <th width="35">15</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($detail)): ?>
        <tr>
            <td colspan="20" class="text-center" style="padding:8px;">Tidak ada data</td>
        </tr>
        <?php else: foreach ($detail as $i => $d): ?>
        <tr>
            <td class="text-center"><?= $i+1 ?></td>
            <!-- KUASA PENGGUNA BARANG / PROGRAM / KEGIATAN / OUTPUT -->
            <td class="text-left">
                <?= e(trim(implode(' / ', array_filter([ $d->program_kegiatan, $d->sub_kegiatan, $d->output_kegiatan ])))) ?: '-' ?>
            </td>
            <!-- USULAN BARANG MILIK DAERAH -->
            <td class="text-left"><?= e($d->kode_barang) ?></td>
            <td class="text-left"><?= e($d->barang_nama) ?></td>
            <td class="text-center"><?= (int)$d->usulan_jumlah ?: '-' ?></td>
            <td class="text-center"><?= e($d->usulan_satuan) ?: '-' ?></td>
            <!-- KEBUTUHAN MAKSIMUM -->
            <td class="text-center"><?= (int)$d->kebutuhan_maks_jumlah ?: '-' ?></td>
            <td class="text-center"><?= e($d->kebutuhan_maks_satuan) ?: '-' ?></td>
            <!-- DATA BARANG YANG DAPAT DIOPTIMALISASIKAN -->
            <td class="text-left"><?= e($d->kode_barang) ?></td>
            <td class="text-left"><?= e($d->barang_nama) ?></td>
            <td class="text-center"><?= (int)$d->optimalisasi_jumlah ?: '-' ?></td>
            <td class="text-center"><?= e($d->optimalisasi_satuan) ?: '-' ?></td>
            <!-- KEBUTUHAN RIIL BARANG MILIK -->
            <td class="text-center"><?= (int)$d->kebutuhan_riil_jumlah ?: '-' ?></td>
            <td class="text-center"><?= e($d->kebutuhan_riil_satuan) ?: '-' ?></td>
            <td class="text-center"><?= e($d->keterangan) ?: '-' ?></td>
        </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>


<div style="font-size:8pt;margin-top:6px;margin-bottom:8px;line-height:1.4;">
    
</div>

<table style="width:100%;margin-top:12px;font-size:9pt;border-collapse:collapse;">
<tr>
    <td style="width:50%;text-align:center;padding:4px;">
        Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh Balai Besar Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara (BSSN).
    </td>
    <td style="width:50%;text-align:center;padding:4px;">
        <?= e($app_kabupaten) ?>, <?= tanggal_id(date('Y-m-d')) ?><br>
        Pengguna Barang<br><br><br>
        <?= e($skpd_info->kepala_skpd) ?><br>
        NIP. <?= e($skpd_info->nip_kepala) ?>
    </td>
</tr>
</table>

<div class="footer-note">
    Dicetak oleh sistem SIPA pada <?= date('d/m/Y H:i:s') ?>
</div>

<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
