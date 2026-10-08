<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['rkbmd_model', 'master_model']);
    }

    public function index()
    {
        $tahun = (int) ($this->input->get('tahun') ?: date('Y'));
        $skpdId = (int) $this->input->get('skpd_id');
        $status = $this->input->get('status', TRUE);
        $q = $this->input->get('q', TRUE);

        $filter = ['tahun' => $tahun];

        if ($this->currentUser->role === 'skpd') {
            $filter['skpd_id'] = $this->currentUser->skpd_id;
        } elseif ($skpdId) {
            $filter['skpd_id'] = $skpdId;
        }

        if ($status) $filter['status'] = $status;
        if ($q) $filter['q'] = $q;

        $statistik = $this->rkbmd_model->getStatistik($filter);

        $rekap = [];
        foreach (['pengadaan','pemeliharaan','pemanfaatan','pemindahtanganan','penghapusan'] as $j) {
            $rekap[$j] = ['total' => 0, 'nilai' => 0, 'disetujui' => 0];
        }
        foreach ($statistik as $row) {
            if (!isset($rekap[$row->jenis_usulan])) continue;
            $rekap[$row->jenis_usulan]['total'] += (int) $row->jumlah;
            $rekap[$row->jenis_usulan]['nilai'] += (float) $row->total_nilai;
            if ($row->status === 'disetujui') $rekap[$row->jenis_usulan]['disetujui'] = (int) $row->jumlah;
        }

        $data = [
            'title'     => 'Laporan RKBMD',
            'rekap'     => $rekap,
            'tahun'     => $tahun,
            'skpd_id'   => $skpdId,
            'skpd_list' => $this->master_model->getAllSkpd(),
            'usulan'    => $this->rkbmd_model->getUsulan($filter)
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('laporan/index', $data);
        $this->load->view('templates/footer');
    }
}
