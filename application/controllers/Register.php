<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('skpd_model'); // Assuming you have an SKPD model
    }

    public function index()
    {
        if ($this->auth->check()) {
            redirect('dashboard');
        }

        $data['title'] = 'Registrasi Akun - SIRKBMD';
        $data['skpd_list'] = $this->skpd_model->get_all_skpd_active(); // Fetch active SKPDs

        $this->load->view('auth/register', $data);
    }

    public function process()
    {
        if ($this->auth->check()) {
            redirect('dashboard');
        }

        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('username', 'Username', 'required|trim|alpha_numeric|min_length[5]|max_length[50]|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[100]|is_unique[users.email]');
        $this->form_validation->set_rules('skpd_id', 'SKPD', 'required|integer');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[200]');
        $this->form_validation->set_rules('password_confirm', 'Konfirmasi Password', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', validation_errors());
            $data['title'] = 'Registrasi Akun - SIRKBMD';
            $data['skpd_list'] = $this->skpd_model->get_all_skpd_active();
            $this->load->view('auth/register', $data);
        } else {
            $userData = [
                'nama_lengkap' => $this->input->post('nama_lengkap', TRUE),
                'username'     => $this->input->post('username', TRUE),
                'email'        => $this->input->post('email', TRUE),
                'skpd_id'      => $this->input->post('skpd_id', TRUE),
                'password'     => $this->input->post('password'),
                'role'         => 'skpd', // Self-registration for SKPD users
            ];

            $result = $this->auth->registerUser($userData);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
                redirect('login');
            } else {
                $this->session->set_flashdata('danger', $result['message']);
                $data['title'] = 'Registrasi Akun - SIRKBMD';
                $data['skpd_list'] = $this->skpd_model->get_all_skpd_active();
                $this->load->view('auth/register', $data);
            }
        }
    }
}