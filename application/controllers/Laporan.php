<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Laporan
 * Pusat Laporan & Dashboard Eksekutif SIPA Pemerintah Kabupaten Tapin.
 * Mengintegrasikan pelaporan RKBMD, Standar Satuan Harga (SSH), Standar Biaya Umum (SBU),
 * serta matriks kepatuhan pengusulan SKPD se-Kabupaten Tapin.
 */
class Laporan extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['laporan_model', 'rkbmd_model', 'ssh_model', 'master_model']);
        $this->load->helper(['app', 'form', 'download']);
    }

    public function index()
    {
        $tahun  = (int) ($this->input->get('tahun') ?: date('Y'));
        $skpdId = (int) $this->input->get('skpd_id');
        $status = $this->input->get('status', TRUE);
        $q      = $this->input->get('q', TRUE);
        $tab    = $this->input->get('tab', TRUE) ?: 'rkbmd';

        // Pembatasan Role SKPD
        if ($this->currentUser->role === 'skpd') {
            $skpdId = (int) $this->currentUser->skpd_id;
        }

        $filter = ['tahun' => $tahun];
        if (!empty($skpdId)) $filter['skpd_id'] = $skpdId;
        if (!empty($status)) $filter['status'] = $status;
        if (!empty($q))      $filter['q'] = $q;

        // 1. Eksekutif KPI Ringkasan Terpadu
        $kpi = $this->laporan_model->getExecutiveKpi($tahun, $skpdId);

        // 2. Rekapitulasi RKBMD
        $rkbmdStat = $this->laporan_model->getRkbmdStatistik($filter);
        $rekapRkbmd = [];
        foreach (['pengadaan', 'pemeliharaan', 'pemanfaatan', 'pemindahtanganan', 'penghapusan'] as $j) {
            $rekapRkbmd[$j] = [
                'total'     => 0,
                'draft'     => 0,
                'diajukan'  => 0,
                'disetujui' => 0,
                'ditolak'   => 0,
                'nilai'     => 0
            ];
        }
        foreach ($rkbmdStat as $row) {
            if (!isset($rekapRkbmd[$row->jenis_usulan])) continue;
            $rekapRkbmd[$row->jenis_usulan]['total'] += (int) $row->jumlah;
            $rekapRkbmd[$row->jenis_usulan]['nilai'] += (float) $row->total_nilai;
            if (isset($rekapRkbmd[$row->jenis_usulan][$row->status])) {
                $rekapRkbmd[$row->jenis_usulan][$row->status] = (int) $row->jumlah;
            }
        }
        $usulanRkbmd = $this->rkbmd_model->getUsulan($filter);

        // 3. Rekapitulasi Standar Harga (SSH & SBU)
        $sshStat = $this->laporan_model->getSshSbuStatistik($filter);
        $rekapStandar = [
            'SSH' => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'diverifikasi' => 0, 'direvisi' => 0, 'ditetapkan' => 0, 'nilai_usulan' => 0, 'nilai_ditetapkan' => 0],
            'SBU' => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'diverifikasi' => 0, 'direvisi' => 0, 'ditetapkan' => 0, 'nilai_usulan' => 0, 'nilai_ditetapkan' => 0]
        ];
        foreach ($sshStat as $s) {
            $t = $s->tipe;
            if (!isset($rekapStandar[$t])) continue;
            $rekapStandar[$t]['total']            += (int) $s->jumlah;
            $rekapStandar[$t]['nilai_usulan']     += (float) $s->total_usulan;
            $rekapStandar[$t]['nilai_ditetapkan'] += (float) $s->total_ditetapkan;
            $statusKey = strtolower($s->status_proses);
            if (isset($rekapStandar[$t][$statusKey])) {
                $rekapStandar[$t][$statusKey] += (int) $s->jumlah;
            }
        }
        $usulanStandar = $this->laporan_model->getSshSbuList($filter, 150);

        // 4. Matriks Kepatuhan SKPD (Hanya jika admin/pimpinan atau melihat semua SKPD)
        $kepatuhanList = $this->laporan_model->getKepatuhanSkpdList($tahun, $q);

        // 5. Data Grafik Visual (Chart.js Payload)
        $chartData = [
            // Donut Chart: Komposisi Status Usulan Gabungan
            'statusDonut' => [
                'labels' => ['Disetujui / Ditetapkan', 'Menunggu Verifikasi', 'Draft Usulan', 'Direvisi / Ditolak'],
                'data'   => [
                    array_sum(array_column($rekapRkbmd, 'disetujui')) + ($rekapStandar['SSH']['ditetapkan'] + $rekapStandar['SBU']['ditetapkan']),
                    array_sum(array_column($rekapRkbmd, 'diajukan')) + ($rekapStandar['SSH']['diajukan'] + $rekapStandar['SBU']['diajukan'] + $rekapStandar['SSH']['diverifikasi'] + $rekapStandar['SBU']['diverifikasi']),
                    array_sum(array_column($rekapRkbmd, 'draft')) + ($rekapStandar['SSH']['draft'] + $rekapStandar['SBU']['draft']),
                    array_sum(array_column($rekapRkbmd, 'ditolak')) + ($rekapStandar['SSH']['direvisi'] + $rekapStandar['SBU']['direvisi'])
                ],
                'colors' => ['#198754', '#ffc107', '#6c757d', '#dc3545']
            ],
            // Bar Chart: Alokasi Pagu Nilai Usulan per Jenis RKBMD
            'rkbmdBar' => [
                'labels' => ['Pengadaan', 'Pemeliharaan', 'Pemanfaatan', 'Pemindahtanganan', 'Penghapusan'],
                'data'   => [
                    $rekapRkbmd['pengadaan']['nilai'],
                    $rekapRkbmd['pemeliharaan']['nilai'],
                    $rekapRkbmd['pemanfaatan']['nilai'],
                    $rekapRkbmd['pemindahtanganan']['nilai'],
                    $rekapRkbmd['penghapusan']['nilai']
                ]
            ],
            // Top 5 SKPD
            'topSkpd' => $this->laporan_model->getTopSkpdAnggaran($tahun, 5)
        ];

        $data = [
            'title'          => 'Pusat Laporan & Dashboard Eksekutif SIPA',
            'tahun'          => $tahun,
            'skpd_id'        => $skpdId,
            'status'         => $status,
            'q'              => $q,
            'activeTab'      => $tab,
            'skpd_list'      => $this->master_model->getAllSkpd(),
            'kpi'            => $kpi,
            'rekapRkbmd'     => $rekapRkbmd,
            'usulanRkbmd'    => $usulanRkbmd,
            'rekapStandar'   => $rekapStandar,
            'usulanStandar'  => $usulanStandar,
            'kepatuhanList'  => $kepatuhanList,
            'chartData'      => $chartData
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('laporan/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Ekspor Rekapitulasi Data ke Format Spreadsheet (CSV / Excel)
     */
    public function export($modul = 'rkbmd', $format = 'excel')
    {
        $tahun  = (int) ($this->input->get('tahun') ?: date('Y'));
        $skpdId = (int) $this->input->get('skpd_id');
        $status = $this->input->get('status', TRUE);

        if ($this->currentUser->role === 'skpd') {
            $skpdId = (int) $this->currentUser->skpd_id;
        }

        $filter = ['tahun' => $tahun];
        if (!empty($skpdId)) $filter['skpd_id'] = $skpdId;
        if (!empty($status)) $filter['status'] = $status;

        $filename = "Rekap_SIPA_" . strtoupper($modul) . "_{$tahun}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Add BOM UTF-8 for Excel Indonesian character compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($modul === 'ssh_sbu') {
            // Header SSH & SBU
            fputcsv($output, ['REKAPITULASI STANDAR SATUAN HARGA (SSH) & STANDAR BIAYA UMUM (SBU) KABUPATEN TAPIN']);
            fputcsv($output, ["Tahun Anggaran: {$tahun} | Tanggal Unduh: " . date('d-m-Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['No', 'Kode Usulan', 'Tipe', 'SKPD Pengusul', 'Kategori', 'Nama Barang / Jasa', 'Satuan', 'Harga Usulan (Rp)', 'Harga Ditetapkan (Rp)', 'Status Proses', 'Tanggal Pengajuan']);

            $list = $this->laporan_model->getSshSbuList($filter, 0);
            $no = 1;
            foreach ($list as $row) {
                fputcsv($output, [
                    $no++,
                    $row->kode_usulan,
                    $row->tipe,
                    $row->nama_skpd ?: '-',
                    $row->kategori,
                    $row->uraian,
                    $row->satuan,
                    (float) $row->harga_usulan,
                    (float) ($row->harga_ditetapkan ?: 0),
                    $row->status_proses,
                    date('d-m-Y', strtotime($row->created_at))
                ]);
            }
        } elseif ($modul === 'kepatuhan') {
            // Header Matriks Kepatuhan
            fputcsv($output, ['MATRIKS KEPATUHAN PENGUSULAN SKPD SE-KABUPATEN TAPIN']);
            fputcsv($output, ["Tahun Anggaran: {$tahun} | Tanggal Unduh: " . date('d-m-Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['No', 'Kode SKPD', 'Nama SKPD', 'Usulan RKBMD', 'RKBMD Disetujui', 'Total Nilai RKBMD (Rp)', 'Usulan SSH', 'Usulan SBU', 'Standar Ditetapkan', 'Total Nilai Standar (Rp)', 'Grand Total Usulan', 'Status Kepatuhan']);

            $list = $this->laporan_model->getKepatuhanSkpdList($tahun);
            $no = 1;
            foreach ($list as $row) {
                $statusKepatuhan = ($row->total_rkbmd > 0 && ($row->total_ssh > 0 || $row->total_sbu > 0)) ? 'Lengkap' : (($row->grand_total_usulan > 0) ? 'Sebagian' : 'Belum Ada Usulan');
                fputcsv($output, [
                    $no++,
                    $row->kode_skpd,
                    $row->nama_skpd,
                    (int) $row->total_rkbmd,
                    (int) $row->rkbmd_disetujui,
                    (float) $row->nilai_rkbmd,
                    (int) $row->total_ssh,
                    (int) $row->total_sbu,
                    (int) $row->standar_ditetapkan,
                    (float) $row->nilai_standar,
                    (int) $row->grand_total_usulan,
                    $statusKepatuhan
                ]);
            }
        } else {
            // Default: RKBMD
            fputcsv($output, ['REKAPITULASI RENCANA KEBUTUHAN BARANG MILIK DAERAH (RKBMD) KABUPATEN TAPIN']);
            fputcsv($output, ["Tahun Anggaran: {$tahun} | Tanggal Unduh: " . date('d-m-Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['No', 'Nomor Usulan', 'Jenis Perencanaan', 'SKPD Pengusul', 'Tanggal Usulan', 'Jumlah Item', 'Total Nilai Usulan (Rp)', 'Status Usulan', 'Keterangan']);

            $list = $this->rkbmd_model->getUsulan($filter);
            $no = 1;
            foreach ($list as $row) {
                fputcsv($output, [
                    $no++,
                    $row->nomor_usulan,
                    strtoupper($row->jenis_usulan),
                    $row->nama_skpd ?: '-',
                    date('d-m-Y', strtotime($row->tanggal_usulan)),
                    (int) $row->total_item,
                    (float) $row->total_nilai,
                    strtoupper($row->status),
                    $row->keterangan ?: '-'
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Lembar Cetak Rekapitulasi Resmi Pemerintah Kabupaten Tapin & BPKAD
     */
    public function cetak()
    {
        $tahun  = (int) ($this->input->get('tahun') ?: date('Y'));
        $skpdId = (int) $this->input->get('skpd_id');
        $modul  = $this->input->get('modul', TRUE) ?: 'rkbmd';

        if ($this->currentUser->role === 'skpd') {
            $skpdId = (int) $this->currentUser->skpd_id;
        }

        $filter = ['tahun' => $tahun];
        if (!empty($skpdId)) $filter['skpd_id'] = $skpdId;

        $kpi = $this->laporan_model->getExecutiveKpi($tahun, $skpdId);
        $skpdInfo = NULL;
        if (!empty($skpdId)) {
            $skpdInfo = $this->master_model->getSkpdById($skpdId);
        }

        $data = [
            'tahun'       => $tahun,
            'modul'       => $modul,
            'skpdInfo'    => $skpdInfo,
            'kpi'         => $kpi,
            'rkbmdList'   => ($modul === 'rkbmd') ? $this->rkbmd_model->getUsulan($filter) : [],
            'standarList' => ($modul === 'ssh_sbu') ? $this->laporan_model->getSshSbuList($filter, 0) : [],
            'kepatuhan'   => ($modul === 'kepatuhan') ? $this->laporan_model->getKepatuhanSkpdList($tahun) : []
        ];

        $this->load->view('laporan/cetak_rekap', $data);
    }
}
