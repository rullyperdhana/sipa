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
