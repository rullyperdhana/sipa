<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['master_model']);
        $this->output->set_content_type('application/json');
    }

    public function search_barang()
    {
        $q = $this->input->get('q', TRUE);
        $list = $this->master_model->getAllBarang(['q' => $q, 'active_only' => TRUE, 'limit' => 20]);

        $results = array_map(function($b) {
            return [
                'id'   => (int) $b->id,
                'text' => $b->kode_barang . ' - ' . $b->nama_barang,
                'kode' => $b->kode_barang,
                'nama' => $b->nama_barang,
                'satuan' => $b->satuan,
                'harga'  => (float) $b->harga_standar
            ];
        }, $list);

        $this->output->set_output(json_encode(['results' => $results]));
    }

    public function search_akun_belanja()
    {
        $this->load->model('akun_model');
        $q = $this->input->get('q', TRUE);
        $leafOnly = $this->input->get('all') ? false : true;
        $results = $this->akun_model->searchSelect2($q, $leafOnly, 30);

        $this->output->set_output(json_encode(['results' => $results]));
    }

    public function search_standar_harga()
    {
        $this->load->model('ssh_model');
        $tipe = $this->input->get('tipe', TRUE) ?: 'SSH';
        $q = $this->input->get('q', TRUE);
        $tahun = $this->input->get('tahun', TRUE) ?: 2027;
        $limit = (int) ($this->input->get('limit') ?: 30);

        $results = $this->ssh_model->searchMasterAjax($tipe, $q, $tahun, $limit);

        $formatted = array_map(function($m) {
            $rekText = $m->kode_rekening ? " [Rek: {$m->kode_rekening}]" : '';
            return [
                'id'               => (int) $m->id,
                'text'             => "{$m->kode_kelompok} - {$m->uraian}" . ($m->spesifikasi ? " ({$m->spesifikasi})" : "") . " - Rp " . number_format($m->harga_satuan, 0, ',', '.') . "/{$m->satuan}{$rekText}",
                'kode_standar'     => $m->kode_standar,
                'kode_kelompok'    => $m->kode_kelompok,
                'uraian'           => $m->uraian,
                'spesifikasi'      => $m->spesifikasi,
                'satuan'           => $m->satuan,
                'harga_satuan'     => (float) $m->harga_satuan,
                'harga_satuan_fmt' => number_format($m->harga_satuan, 0, ',', '.'),
                'kategori'         => $m->kategori,
                'kode_rekening'    => $m->kode_rekening,
                'nama_rekening'    => $m->nama_rekening,
                'tahun_anggaran'   => (int) $m->tahun_anggaran
            ];
        }, $results);

        $this->output->set_output(json_encode(['results' => $formatted]));
    }

    public function detail_standar_harga($id)
    {
        $this->load->model('ssh_model');
        $item = $this->ssh_model->getMasterById($id);
        if (!$item) {
            return $this->output->set_status_header(404)->set_output(json_encode(['error' => 'Not found']));
        }
        return $this->output->set_output(json_encode(['item' => $item]));
    }

    public function notifikasi()
    {
        $userId = $this->currentUser->id;
        $list = $this->notifikasi_model->getForUser($userId, FALSE, 10);
        $unread = $this->notifikasi_model->countUnread($userId);

        $this->output->set_output(json_encode([
            'unread' => $unread,
            'list'   => $list
        ], JSON_UNESCAPED_UNICODE));
    }

    public function baca_notif($id)
    {
        $this->notifikasi_model->markAsRead($id, $this->currentUser->id);
        $this->output->set_output(json_encode(['success' => TRUE]));
    }
}
