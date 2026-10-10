<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['rkbmd_model', 'master_model', 'laporan_model', 'ssh_model']);
    }

    public function index()
    {
        $user = $this->currentUser;
        $tahun = (int) ($this->input->get('tahun') ?: get_tahun_anggaran());
        $periodeId = (int) $this->input->get('periode_id');
        $jenis = $this->input->get('jenis') ?: 'pengadaan';
        $skpdId = $this->input->get('skpd_id');

        $filter = ['tahun' => $tahun];
        if ($user->role === 'skpd') {
            $filter['skpd_id'] = $user->skpd_id;
            $skpdId = $user->skpd_id;
        }

        // 1. Ambil KPI Eksekutif Terpadu (RKBMD + SSH + SBU)
        $executiveKpi = $this->laporan_model->getExecutiveKpi($tahun, $skpdId);

        // 2. Statistik RKBMD per jenis & status
        $statistikRkbmd = $this->rkbmd_model->getStatistik($filter);

        $rekap = [
            'pengadaan'        => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'pemeliharaan'     => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'pemanfaatan'      => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'pemindahtanganan' => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'penghapusan'      => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0]
        ];

        foreach ($statistikRkbmd as $row) {
            if (!isset($rekap[$row->jenis_usulan])) continue;
            $rekap[$row->jenis_usulan]['total'] += (int) $row->jumlah;
            $rekap[$row->jenis_usulan]['nilai'] += (float) $row->total_nilai;
            if (isset($rekap[$row->jenis_usulan][$row->status])) {
                $rekap[$row->jenis_usulan][$row->status] = (int) $row->jumlah;
            }
        }

        // 3. Rekap Standar Harga (SSH & SBU)
        $filterSsh = ['tahun' => $tahun];
        if (!empty($skpdId)) $filterSsh['skpd_id'] = $skpdId;
        $statistikStandar = $this->laporan_model->getSshSbuStatistik($filterSsh);

        $rekapStandar = [
            'SSH' => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'diverifikasi' => 0, 'ditetapkan' => 0, 'direvisi' => 0, 'nilai' => 0],
            'SBU' => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'diverifikasi' => 0, 'ditetapkan' => 0, 'direvisi' => 0, 'nilai' => 0]
        ];

        foreach ($statistikStandar as $row) {
            $t = strtoupper($row->tipe);
            if (!isset($rekapStandar[$t])) continue;
            $rekapStandar[$t]['total'] += (int) $row->jumlah;
            $rekapStandar[$t]['nilai'] += (float) $row->total_usulan;
            $stLower = strtolower($row->status_proses);
            if (isset($rekapStandar[$t][$stLower])) {
                $rekapStandar[$t][$stLower] = (int) $row->jumlah;
            }
        }

        // 4. Status Jadwal Pengusulan Aktif (RKBMD, SSH, SBU)
        $jadwalRkbmd = $this->master_model->getPeriodeAktif($tahun);
        $isJadwalBukaRkbmd = !empty($jadwalRkbmd);
        $jadwalSsh = $this->ssh_model->getJadwalAktif('SSH', $tahun);
        $isJadwalBukaSsh = !empty($jadwalSsh);
        $jadwalSbu = $this->ssh_model->getJadwalAktif('SBU', $tahun);
        $isJadwalBukaSbu = !empty($jadwalSbu);

        // 5. Total Antrean Verifikasi (RKBMD diajukan + SSH diajukan + SBU diajukan)
        $totalDiajukanRkbmd = array_sum(array_column($rekap, 'diajukan'));
        $totalDiajukanSsh   = $rekapStandar['SSH']['diajukan'];
        $totalDiajukanSbu   = $rekapStandar['SBU']['diajukan'];
        $totalAntreanVerif  = $totalDiajukanRkbmd + $totalDiajukanSsh + $totalDiajukanSbu;

        // 6. Data JSON untuk Chart.js
        $chartAnggaran = [
            'labels' => ['Pengadaan', 'Pemeliharaan', 'Pemanfaatan', 'Pemindahtanganan', 'Penghapusan', 'SSH', 'SBU'],
            'data'   => [
                (float) $rekap['pengadaan']['nilai'],
                (float) $rekap['pemeliharaan']['nilai'],
                (float) $rekap['pemanfaatan']['nilai'],
                (float) $rekap['pemindahtanganan']['nilai'],
                (float) $rekap['penghapusan']['nilai'],
                (float) $rekapStandar['SSH']['nilai'],
                (float) $rekapStandar['SBU']['nilai'],
            ]
        ];

        $totalDraft = array_sum(array_column($rekap, 'draft')) + $rekapStandar['SSH']['draft'] + $rekapStandar['SBU']['draft'];
        $totalVerif = $totalAntreanVerif + $rekapStandar['SSH']['diverifikasi'] + $rekapStandar['SBU']['diverifikasi'];
        $totalDisetujui = array_sum(array_column($rekap, 'disetujui')) + $rekapStandar['SSH']['ditetapkan'] + $rekapStandar['SBU']['ditetapkan'];
        $totalDitolak = array_sum(array_column($rekap, 'ditolak')) + $rekapStandar['SSH']['direvisi'] + $rekapStandar['SBU']['direvisi'];

        $chartStatus = [
            'labels' => ['Draft', 'Diajukan / Verifikasi', 'Disetujui / Ditetapkan', 'Ditolak / Revisi'],
            'data'   => [$totalDraft, $totalVerif, $totalDisetujui, $totalDitolak]
        ];

        // 7. Periode List & SKPD List
        $periodeList = $this->master_model->getAllPeriode();
        if (!$periodeId && !empty($periodeList)) {
            $periodeId = (int) $periodeList[0]->id;
        }

        $skpdList = $this->master_model->getAllSkpd(FALSE);

        // 8. Monitoring RKBMD per SKPD
        $monitoringFilter = [
            'jenis'      => $jenis,
            'periode_id' => $periodeId,
            'tahun'      => $tahun
        ];
        if (!empty($skpdId)) {
            $monitoringFilter['skpd_id'] = (int) $skpdId;
        }
        if ($user->role === 'skpd') {
            $monitoringFilter['skpd_id'] = $user->skpd_id;
        }

        $monitoringRows = $this->rkbmd_model->getMonitoringBySkpd($monitoringFilter);

        $monitoring = [];
        $statuses = ['draft', 'diajukan', 'diverifikasi', 'disetujui', 'ditolak', 'revisi'];
        foreach ($skpdList as $skpd) {
            if ($user->role === 'skpd' && (int)$skpd->id !== (int)$user->skpd_id) continue;
            $monitoring[$skpd->id] = [
                'skpd_id' => $skpd->id,
                'nama_skpd' => $skpd->nama_skpd,
                'kode_skpd' => $skpd->kode_skpd,
                'total' => 0,
                'last_update' => null,
                'counts' => array_fill_keys($statuses, 0)
            ];
        }

        foreach ($monitoringRows as $row) {
            if (!isset($monitoring[$row->skpd_id])) continue;
            $status = $row->status ?: 'draft';
            if (!isset($monitoring[$row->skpd_id]['counts'][$status])) {
                $monitoring[$row->skpd_id]['counts'][$status] = 0;
            }
            $monitoring[$row->skpd_id]['counts'][$status] = (int) $row->jumlah;
            $monitoring[$row->skpd_id]['total'] += (int) $row->jumlah;
            if ($row->last_update && ($monitoring[$row->skpd_id]['last_update'] === null || $row->last_update > $monitoring[$row->skpd_id]['last_update'])) {
                $monitoring[$row->skpd_id]['last_update'] = $row->last_update;
            }
        }

        // 9. Usulan terbaru RKBMD & Standar Harga
        $filter['limit'] = 6;
        $usulanTerbaru = $this->rkbmd_model->getUsulan($filter);
        $usulanStandarTerbaru = $this->laporan_model->getSshSbuList(['tahun' => $tahun, 'skpd_id' => $skpdId], 6);

        $data = [
            'title'                => 'Dashboard Utama - SIPA Kab. Tapin',
            'rekap'                => $rekap,
            'rekapStandar'         => $rekapStandar,
            'executiveKpi'         => $executiveKpi,
            'jadwalRkbmd'          => $jadwalRkbmd,
            'isJadwalBukaRkbmd'    => $isJadwalBukaRkbmd,
            'jadwalSsh'            => $jadwalSsh,
            'isJadwalBukaSsh'      => $isJadwalBukaSsh,
            'jadwalSbu'            => $jadwalSbu,
            'isJadwalBukaSbu'      => $isJadwalBukaSbu,
            'totalAntreanVerif'    => $totalAntreanVerif,
            'chartAnggaran'        => $chartAnggaran,
            'chartStatus'          => $chartStatus,
            'tahun'                => $tahun,
            'periodeList'          => $periodeList,
            'periodeId'            => $periodeId,
            'jenis'                => $jenis,
            'skpdList'             => $skpdList,
            'skpdId'               => $skpdId,
            'monitoring'           => $monitoring,
            'usulanTerbaru'        => $usulanTerbaru,
            'usulanStandarTerbaru' => $usulanStandarTerbaru,
            'totalNilai'           => array_sum(array_column($rekap, 'nilai'))
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
}
