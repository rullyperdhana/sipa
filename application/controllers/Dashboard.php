<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['rkbmd_model', 'master_model']);
    }

    public function index()
    {
        $user = $this->currentUser;
        $tahun = (int) ($this->input->get('tahun') ?: date('Y'));
        $periodeId = (int) $this->input->get('periode_id');
        $jenis = $this->input->get('jenis') ?: 'pengadaan';
        $skpdId = $this->input->get('skpd_id');

        $filter = ['tahun' => $tahun];
        if ($user->role === 'skpd') {
            $filter['skpd_id'] = $user->skpd_id;
            $skpdId = $user->skpd_id;
        }

        // Statistik per jenis & status
        $statistik = $this->rkbmd_model->getStatistik($filter);

        $rekap = [
            'pengadaan'        => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'pemeliharaan'     => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'pemanfaatan'      => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'pemindahtanganan' => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0],
            'penghapusan'      => ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'ditolak' => 0, 'nilai' => 0]
        ];

        foreach ($statistik as $row) {
            if (!isset($rekap[$row->jenis_usulan])) continue;
            $rekap[$row->jenis_usulan]['total'] += (int) $row->jumlah;
            $rekap[$row->jenis_usulan]['nilai'] += (float) $row->total_nilai;
            if (isset($rekap[$row->jenis_usulan][$row->status])) {
                $rekap[$row->jenis_usulan][$row->status] = (int) $row->jumlah;
            }
        }

        $periodeList = $this->master_model->getAllPeriode();
        if (!$periodeId && !empty($periodeList)) {
            $periodeId = (int) $periodeList[0]->id;
        }

        $skpdList = $this->master_model->getAllSkpd(FALSE);

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

        // Usulan terbaru
        $filter['limit'] = 10;
        $usulanTerbaru = $this->rkbmd_model->getUsulan($filter);

        $data = [
            'title'         => 'Dashboard - SIRKBMD',
            'rekap'         => $rekap,
            'tahun'         => $tahun,
            'periodeList'   => $periodeList,
            'periodeId'     => $periodeId,
            'jenis'         => $jenis,
            'skpdList'      => $skpdList,
            'skpdId'        => $skpdId,
            'monitoring'    => $monitoring,
            'usulanTerbaru' => $usulanTerbaru,
            'totalNilai'    => array_sum(array_column($rekap, 'nilai'))
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
}
