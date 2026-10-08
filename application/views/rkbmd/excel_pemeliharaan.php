<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<html>
<head><meta charset="UTF-8"><style>td,th{font-family:Arial;font-size:10pt;}</style></head>
<body>
<table>
<tr><td colspan="10" style="font-weight:bold;font-size:13pt;text-align:center;">RENCANA KEBUTUHAN BARANG MILIK DAERAH (RKBMD)</td></tr>
<tr><td colspan="10" style="font-weight:bold;font-size:12pt;text-align:center;">JENIS: <?= strtoupper(label_jenis($jenis)) ?></td></tr>
<tr><td colspan="10">&#xA0;</td></tr>
<tr><td colspan="2">Nomor Usulan</td><td colspan="8"><?= e($usulan->nomor_usulan) ?></td></tr>
<tr><td colspan="2">SKPD</td><td colspan="8"><?= e($usulan->nama_skpd) ?></td></tr>
<tr><td colspan="2">Tahun Anggaran</td><td colspan="8"><?= (int)$usulan->tahun_anggaran ?></td></tr>
<tr><td colspan="2">Tanggal Usulan</td><td colspan="8"><?= tanggal_id($usulan->tanggal_usulan) ?></td></tr>
<tr><td colspan="2">Status</td><td colspan="8"><?= strtoupper($usulan->status) ?></td></tr>
<tr><td colspan="10">&#xA0;</td></tr>
<tr style="background:#dce6f1;font-weight:bold;text-align:center;">
    <td>No</td>
    <td>Kode Barang</td>
    <td>Nama Barang</td>
    <?php if ($jenis==='pengadaan'): ?>
    <td>Program Kegiatan</td><td>Sub Kegiatan</td>
    <td>Jml Usulan</td><td>Keb. Riil</td>
    <td>Harga Satuan (Rp)</td><td>Total Harga (Rp)</td>
    <?php elseif ($jenis==='pemeliharaan'): ?>
    <td>Nama Pemeliharaan</td><td>Jml Pemeliharaan</td>
    <td>Kondisi B</td><td>Kondisi RR</td><td>Kondisi RB</td>
    <td>Harga Satuan (Rp)</td><td>Total Harga (Rp)</td>
    <?php elseif ($jenis==='pemanfaatan'): ?>
    <td>Bentuk Pemanfaatan</td><td>No Register</td><td>Thn Perolehan</td><td>Harga Perolehan (Rp)</td>
    <?php elseif ($jenis==='pemindahtanganan'): ?>
    <td>Bentuk Pemindahtanganan</td><td>No Register</td><td>Spesifikasi</td><td>Thn Perolehan</td><td>Harga Perolehan (Rp)</td>
    <?php elseif ($jenis==='penghapusan'): ?>
    <td>Kategori Penghapusan</td><td>No Register</td><td>Spesifikasi</td><td>Thn Perolehan</td><td>Harga Perolehan (Rp)</td>
    <?php endif; ?>
    <td>Keterangan</td>
</tr>
<?php if (empty($detail)): ?>
<tr><td colspan="10" style="text-align:center;">Tidak ada data</td></tr>
<?php else: foreach ($detail as $i => $d): ?>
<tr>
    <td style="text-align:center;"><?= $i+1 ?></td>
    <td><?= e($d->kode_barang) ?></td>
    <td><?= e($d->barang_nama) ?></td>
    <?php if ($jenis==='pengadaan'): ?>
    <td><?= e($d->program_kegiatan) ?></td>
    <td><?= e($d->sub_kegiatan) ?></td>
    <td style="text-align:center;"><?= (int)$d->usulan_jumlah ?> <?= e($d->usulan_satuan) ?></td>
    <td style="text-align:center;"><?= (int)$d->kebutuhan_riil_jumlah ?> <?= e($d->kebutuhan_riil_satuan) ?></td>
    <td style="text-align:right;"><?= rupiah_excel($d->harga_satuan) ?></td>
    <td style="text-align:right;"><?= rupiah_excel($d->total_harga) ?></td>
    <?php elseif ($jenis==='pemeliharaan'): ?>
    <td><?= e($d->nama_pemeliharaan) ?></td>
    <td style="text-align:center;"><?= (int)$d->jumlah_pemeliharaan ?> <?= e($d->satuan_pemeliharaan) ?></td>
    <td style="text-align:center;"><?= (int)$d->kondisi_b ?></td>
    <td style="text-align:center;"><?= (int)$d->kondisi_rr ?></td>
    <td style="text-align:center;"><?= (int)$d->kondisi_rb ?></td>
    <td style="text-align:right;"><?= rupiah_excel($d->harga_satuan) ?></td>
    <td style="text-align:right;"><?= rupiah_excel($d->total_harga) ?></td>
    <?php elseif ($jenis==='pemanfaatan'): ?>
    <td><?= e(ucwords(str_replace('_',' ',$d->bentuk_pemanfaatan))) ?></td>
    <td><?= e($d->no_register) ?></td>
    <td style="text-align:center;"><?= (int)$d->tahun_perolehan ?: '-' ?></td>
    <td style="text-align:right;"><?= rupiah_excel($d->harga_perolehan) ?></td>
    <?php elseif ($jenis==='pemindahtanganan'): ?>
    <td><?= e(ucwords(str_replace('_',' ',$d->bentuk_pemindahtanganan))) ?></td>
    <td><?= e($d->no_register) ?></td>
    <td><?= e($d->spesifikasi) ?></td>
    <td style="text-align:center;"><?= (int)$d->tahun_perolehan ?></td>
    <td style="text-align:right;"><?= rupiah_excel($d->harga_perolehan) ?></td>
    <?php elseif ($jenis==='penghapusan'): ?>
    <td><?= e(ucwords(str_replace('_',' ',$d->kategori_penghapusan))) ?></td>
    <td><?= e($d->no_register) ?></td>
    <td><?= e($d->spesifikasi) ?></td>
    <td style="text-align:center;"><?= (int)$d->tahun_perolehan ?></td>
    <td style="text-align:right;"><?= rupiah_excel($d->harga_perolehan) ?></td>
    <?php endif; ?>
    <td><?= e($d->keterangan) ?></td>
</tr>
<?php endforeach; ?>
<?php if (in_array($jenis,['pengadaan','pemeliharaan'])): ?>
<tr style="font-weight:bold;background:#f2f2f2;">
    <td colspan="<?= $jenis==='pengadaan' ? 8 : 9 ?>" style="text-align:right;">JUMLAH TOTAL</td>
    <td style="text-align:right;"><?= rupiah_excel($usulan->total_nilai) ?></td>
    <td></td>
</tr>
<?php endif; ?>
<?php endif; ?>
</table>
</body>
</html>
