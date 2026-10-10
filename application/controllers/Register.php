<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('skpd_model');
    }

    public function index()
    {
        if ($this->auth->check()) {
            redirect('dashboard');
        }

        $regEnabled = function_exists('get_setting') ? get_setting('registration_enabled', '1') : '1';
        $isClosed   = ($regEnabled === '0');

        // Generate Math Captcha Anti-Bot
        $n1 = rand(2, 9);
        $n2 = rand(1, 9);
        $this->session->set_userdata('reg_captcha_ans', $n1 + $n2);

        $data = [
            'title'               => 'Pendaftaran Akun Baru - SIPA Kabupaten Tapin',
            'skpd_list'           => $this->skpd_model->get_all_skpd_active(),
            'is_closed'           => $isClosed,
            'captcha_question'    => "Berapa hasil {$n1} + {$n2} ?",
            'require_approval'    => (function_exists('get_setting') ? get_setting('registration_require_approval', '1') : '1') === '1'
        ];

        $this->load->view('auth/register', $data);
    }

    public function process()
    {
        if ($this->auth->check()) {
            redirect('dashboard');
        }

        $regEnabled = function_exists('get_setting') ? get_setting('registration_enabled', '1') : '1';
        if ($regEnabled === '0') {
            $this->session->set_flashdata('danger', 'Pendaftaran mandiri sedang ditutup oleh Administrator BPKAD.');
            redirect('register');
            return;
        }

        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('nip', 'NIP', 'trim|max_length[25]');
        $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[4]|max_length[50]|alpha_dash|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[100]|is_unique[users.email]');
        $this->form_validation->set_rules('no_wa', 'Nomor WhatsApp / HP', 'required|trim|min_length[10]|max_length[20]');
        $this->form_validation->set_rules('skpd_id', 'SKPD', 'required|integer');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[200]');
        $this->form_validation->set_rules('password_confirm', 'Konfirmasi Password', 'required|matches[password]');
        $this->form_validation->set_rules('captcha', 'Kode Keamanan (Captcha)', 'required|integer');

        $captchaUser = (int) $this->input->post('captcha');
        $captchaValid = ($captchaUser === (int) $this->session->userdata('reg_captcha_ans'));

        if ($this->form_validation->run() === FALSE || !$captchaValid) {
            if (!$captchaValid && $this->form_validation->run() !== FALSE) {
                $this->session->set_flashdata('danger', 'Jawaban kode keamanan (Captcha) salah. Silakan coba kembali.');
            } else {
                $this->session->set_flashdata('danger', validation_errors());
            }

            // Regenerate captcha baru
            $n1 = rand(2, 9);
            $n2 = rand(1, 9);
            $this->session->set_userdata('reg_captcha_ans', $n1 + $n2);

            $data = [
                'title'            => 'Pendaftaran Akun Baru - SIPA Kabupaten Tapin',
                'skpd_list'        => $this->skpd_model->get_all_skpd_active(),
                'is_closed'        => FALSE,
                'captcha_question' => "Berapa hasil {$n1} + {$n2} ?",
                'require_approval' => (function_exists('get_setting') ? get_setting('registration_require_approval', '1') : '1') === '1'
            ];
            $this->load->view('auth/register', $data);
            return;
        }

        $userData = [
            'nama_lengkap' => $this->input->post('nama_lengkap', TRUE),
            'nip'          => $this->input->post('nip', TRUE),
            'username'     => strtolower($this->input->post('username', TRUE)),
            'email'        => strtolower($this->input->post('email', TRUE)),
            'no_wa'        => $this->input->post('no_wa', TRUE),
            'skpd_id'      => (int) $this->input->post('skpd_id', TRUE),
            'password'     => $this->input->post('password'),
        ];

        $result = $this->auth->registerUser($userData);

        if ($result['success']) {
            $this->session->unset_userdata('reg_captcha_ans');
            $this->session->set_flashdata('success', $result['message']);
            redirect('login');
        } else {
            $this->session->set_flashdata('danger', $result['message']);
            redirect('register');
        }
    }
}