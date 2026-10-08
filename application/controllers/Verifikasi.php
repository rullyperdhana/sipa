<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Verifikasi extends Verifikator_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['rkbmd_model', 'master_model']);
    }

    public function index()
    {
        $filter = ['status' => 'diajukan'];
        if ($s = $this->input->get('status', TRUE))    $filter['status'] = $s;
        if ($j = $this->input->get('jenis', TRUE))     $filter['jenis'] = $j;
        if ($q = $this->input->get('q', TRUE))         $filter['q'] = $q;
        if ($t = (int) $this->input->get('tahun'))     $filter['tahun'] = $t;

        $data = [
            'title'  => 'Verifikasi RKBMD',
            'usulan' => $this->rkbmd_model->getUsulan($filter),
            'filter' => $filter,
            'skpd'   => $this->master_model->getAllSkpd()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('verifikasi/index', $data);
        $this->load->view('templates/footer');
    }

    public function detail($id)
    {
        $usulan = $this->rkbmd_model->findUsulan($id);
        if (!$usulan) show_404();

        $data = [
            'title'  => 'Verifikasi - ' . $usulan->nomor_usulan,
            'usulan' => $usulan,
            'detail' => $this->rkbmd_model->getDetail($usulan->jenis_usulan, $id)
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('verifikasi/detail', $data);
        $this->load->view('templates/footer');
    }

    public function proses($id)
    {
        if ($this->input->method() !== 'post') show_404();

        $usulan = $this->rkbmd_model->findUsulan($id);
        if (!$usulan) {
            $this->session->set_flashdata('danger', 'Usulan tidak ditemukan.');
            redirect('verifikasi');
        }

        $aksi = $this->input->post('aksi', TRUE);
        $allowed = ['verifikasi', 'setuju', 'tolak', 'revisi'];
        if (!in_array($aksi, $allowed, TRUE)) {
            $this->session->set_flashdata('danger', 'Aksi tidak valid.');
            redirect("verifikasi/detail/{$id}");
        }

        $catatan = $this->input->post('catatan', TRUE);
        if (in_array($aksi, ['tolak', 'revisi'], TRUE) && empty($catatan)) {
            $this->session->set_flashdata('danger', 'Catatan wajib diisi untuk aksi tolak/revisi.');
            redirect("verifikasi/detail/{$id}");
        }

        $result = $this->rkbmd_model->verifikasi($id, $aksi, $catatan);
        $this->session->set_flashdata($result['success'] ? 'success' : 'danger', $result['message']);
        redirect("verifikasi/detail/{$id}");
    }
}
