<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Katalog Standar Harga') ?> - SIPA Kabupaten Tapin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            background: #fff;
            color: #000;
            margin: 0;
            padding: 15px;
        }

        /* Toolbar Layar (Tidak Dicetak) */
        .no-print-toolbar {
            background: #0f172a;
            color: #f8fafc;
            padding: 12px 20px;
            margin: -15px -15px 25px -15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        /* Kop Surat Resmi Kedinasan */
        .kop-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .kop-header h4 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .kop-header h3 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .kop-header p {
            font-size: 9pt;
            margin-bottom: 1px;
            line-height: 1.35;
        }
        .kop-divider {
            border-bottom: 3px double #000;
            margin-top: 8px;
            margin-bottom: 15px;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin-bottom: 18px;
        }
        .doc-title h5 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .doc-title .doc-subtitle {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Informasi Metadata */
        .table-meta {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
            border-collapse: collapse;
        }
        .table-meta td {
            padding: 2px 4px;
            vertical-align: top;
        }

        /* Tabel Data Katalog */
        .table-print {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }
        .table-print th, .table-print td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            color: #000;
        }
        .table-print th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .font-mono {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        }

        /* Blok Tanda Tangan */
        .ttd-box {
            margin-top: 30px;
            page-break-inside: avoid;
            font-size: 9.5pt;
        }
        .ttd-space {
            height: 65px;
        }

        /* CSS Mode Cetak */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                font-size: 9pt;
            }
            .table-print {
                font-size: 8pt;
            }
            .table-print th, .table-print td {
                padding: 4px 5px;
            }
            thead {
                display: table-header-group;
            }
            tr {
                page-break-inside: avoid;
            }
            .ttd-box {
                page-break-inside: avoid;
            }
            @page {
                size: A4 landscape;
                margin: 10mm 12mm 12mm 12mm;
            }
        }
    </style>
</head>
<body onload="if(!window.location.search.includes('autoprint=0')) { window.print(); }">

<!-- Toolbar Pratinjau (Hanya terlihat di layar) -->
<div class="no-print no-print-toolbar">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 fs-6">
                <i class="bi bi-printer me-1"></i> Cetak Katalog <?= e($tipe) ?> TA <?= (int)$filter['tahun'] ?>
            </span>
            <span class="text-light small opacity-75">
                Menampilkan <strong><?= number_format(count($list), 0, ',', '.') ?></strong> data 
                (Total Filter: <strong><?= number_format($totalFiltered, 0, ',', '.') ?></strong>)
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Pilihan Limit Data -->
            <div class="btn-group btn-group-sm" role="group" aria-label="Batas Data Cetak">
                <a href="<?= site_url("{$prefixUrl}/cetak_master?" . http_build_query(array_merge($filter, ['autoprint' => 0, 'limit' => 100]))) ?>" 
                   class="btn <?= ($printLimit == 100 && $mode !== 'current_page') ? 'btn-light fw-bold text-dark' : 'btn-outline-light' ?>">100</a>
                <a href="<?= site_url("{$prefixUrl}/cetak_master?" . http_build_query(array_merge($filter, ['autoprint' => 0, 'limit' => 250]))) ?>" 
                   class="btn <?= ($printLimit == 250 && $mode !== 'current_page') ? 'btn-light fw-bold text-dark' : 'btn-outline-light' ?>">250</a>
                <a href="<?= site_url("{$prefixUrl}/cetak_master?" . http_build_query(array_merge($filter, ['autoprint' => 0, 'limit' => 500]))) ?>" 
                   class="btn <?= ($printLimit == 500 && $mode !== 'current_page') ? 'btn-light fw-bold text-dark' : 'btn-outline-light' ?>">500</a>
                <a href="<?= site_url("{$prefixUrl}/cetak_master?" . http_build_query(array_merge($filter, ['autoprint' => 0, 'limit' => 1000]))) ?>" 
                   class="btn <?= ($printLimit == 1000 && $mode !== 'current_page') ? 'btn-light fw-bold text-dark' : 'btn-outline-light' ?>">1.000</a>
            </div>

            <!-- Tombol Cetak & Tutup -->
            <button type="button" class="btn btn-success btn-sm px-3 fw-bold shadow-sm" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Cetak Dokumen
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm text-light border-secondary" onclick="window.close()">
                <i class="bi bi-x-circle me-1"></i> Tutup Tab
            </button>
        </div>
    </div>
</div>

<div class="container-fluid px-0">

    <!-- Kop Surat Resmi -->
    <div class="kop-header">
        <h4>PEMERINTAH KABUPATEN TAPIN</h4>
        <h3>BADAN PENGELOLAAN KEUANGAN DAN ASET DAERAH</h3>
        <p>Kawasan Rantau Baru, Jl. Datu Nuraya, Rantau, Kabupaten Tapin, Kalimantan Selatan (71114)</p>
        <p>Laman Resmi: <em>https://sipa.bkadtapinkab.online</em> &bull; Pos-el: <em>bpkad@tapinkab.go.id</em></p>
        <div class="kop-divider"></div>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h5>KATALOG RESMI <?= e($moduleTitle) ?></h5>
        <div class="doc-subtitle">PEMERINTAH KABUPATEN TAPIN &bull; TAHUN ANGGARAN <?= (int)$filter['tahun'] ?></div>
    </div>

    <!-- Parameter Ringkasan Metadata -->
    <table class="table-meta">
        <tr>
            <td width="15%" class="fw-bold">Tahun Anggaran</td>
            <td width="2%">:</td>
            <td width="33%">Tahun Anggaran <?= (int)$filter['tahun'] ?></td>
            <td width="18%" class="fw-bold">Tanggal Cetak</td>
            <td width="2%">:</td>
            <td width="30%"><?= tanggal_id(date('Y-m-d')) ?> &bull; <?= date('H:i') ?> WITA</td>
        </tr>
        <tr>
            <td class="fw-bold">Filter Kategori</td>
            <td>:</td>
            <td><?= !empty($filter['kategori']) ? e($filter['kategori']) : 'Semua Kategori' ?></td>
            <td class="fw-bold">Total Master Terdata</td>
            <td>:</td>
            <td><?= number_format($totalFiltered, 0, ',', '.') ?> Item Terdaftar</td>
        </tr>
        <tr>
            <td class="fw-bold">Kata Kunci Pencarian</td>
            <td>:</td>
            <td><?= !empty($filter['q']) ? ('"' . e($filter['q']) . '"') : 'Semua Data (Tanpa Kata Kunci)' ?></td>
            <td class="fw-bold">Jumlah Item Tercetak</td>
            <td>:</td>
            <td>
                <strong><?= number_format(count($list), 0, ',', '.') ?> Item</strong>
                <?php if ($totalFiltered > count($list)): ?>
                    <span class="text-muted">(Dibatasi <?= number_format($printLimit, 0, ',', '.') ?> item untuk performa cetak)</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Katalog Resmi -->
    <table class="table-print">
        <thead>
            <tr>
                <th width="35" class="text-center">NO</th>
                <th width="130">KODE STANDAR / KELOMPOK</th>
                <th><?= ($tipe === 'SBU') ? 'URAIAN BIAYA / HONOR / JASA & SPESIFIKASI' : 'URAIAN BARANG FISIK & SPESIFIKASI TEKNIS' ?></th>
                <th width="120">KATEGORI</th>
                <th width="190">REKENING BELANJA SIPD RI</th>
                <th width="65" class="text-center">SATUAN</th>
                <th width="125" class="text-end">HARGA STANDAR (RP)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($list)): ?>
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    Tidak ada data katalog <?= e($tipe) ?> yang sesuai dengan kriteria pencarian / filter.
                </td>
            </tr>
            <?php else: foreach ($list as $idx => $row): ?>
            <tr>
                <td class="text-center"><?= $startNumber + $idx ?></td>
                <td>
                    <div class="fw-bold font-mono"><?= e($row->kode_standar) ?></div>
                    <div class="small text-muted font-mono"><?= e($row->kode_kelompok) ?></div>
                </td>
                <td>
                    <div class="fw-bold"><?= e($row->uraian) ?></div>
                    <?php if (!empty($row->spesifikasi)): ?>
                    <div class="small text-secondary mt-0.5"><?= nl2br(e($row->spesifikasi)) ?></div>
                    <?php endif; ?>
                </td>
                <td><?= e($row->kategori) ?></td>
                <td>
                    <?php if (!empty($row->kode_rekening)): ?>
                        <div class="font-mono fw-bold"><?= e($row->kode_rekening) ?></div>
                        <div class="small text-muted"><?= e($row->nama_rekening) ?></div>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= e($row->satuan) ?></td>
                <td class="text-end fw-bold font-mono">
                    <?= rupiah($row->harga_satuan) ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- Blok Tanda Tangan Resmi Kepala BPKAD -->
    <div class="row ttd-box">
        <div class="col-7">
            <div class="small text-muted fst-italic pt-4">
                <strong>Catatan Keabsahan Dokumen:</strong><br>
                1. Dokumen ini merupakan cetakan resmi katalog Standar Harga dari Sistem Informasi Pengelolaan Aset (SIPA) Pemerintah Kabupaten Tapin.<br>
                2. Menjadi pedoman resmi acuan belanja dan pengusulan RKBMD serta RKA SKPD Tahun Anggaran <?= (int)$filter['tahun'] ?>.<br>
                3. Terhubung secara terintegrasi dengan kodefikasi rekening belanja Sistem Informasi Pembangunan Daerah (SIPD RI).
            </div>
        </div>
        <div class="col-5 text-center">
            <p class="mb-1">Rantau, <?= tanggal_id(date('Y-m-d')) ?></p>
            <p class="mb-0"><strong>KEPALA BADAN PENGELOLAAN KEUANGAN</strong></p>
            <p class="mb-4"><strong>DAN ASET DAERAH KABUPATEN TAPIN</strong></p>
            <div class="ttd-space"></div>
            <p class="mb-0 text-decoration-underline fw-bold">HARVEY, S.E., M.M.</p>
            <p class="mb-0">NIP. 19740512 199803 1 005</p>
        </div>
    </div>

</div>

</body>
</html>
