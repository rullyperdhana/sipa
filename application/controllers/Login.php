<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('form');
    }
    
    public function index()
    {
        $this->masuk();
    }

    public function masuk()
    {
        if ($this->auth->check()) redirect('dashboard');

        $fails = (int) $this->session->userdata('login_fails');
        $showCaptcha = ($fails >= 3);

        if ($this->input->method() === 'post') {
            // Validasi server-side
            $this->form_validation->set_rules('username', 'Username', 'required|trim|max_length[50]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[3]|max_length[200]');

            if ($showCaptcha) {
                $this->form_validation->set_rules('captcha', 'Kode Keamanan (Captcha)', 'required|integer');
            }

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
            } else {
                // Verifikasi captcha jika muncul
                if ($showCaptcha) {
                    $userAns = (int) $this->input->post('captcha');
                    $correctAns = (int) $this->session->userdata('login_captcha_ans');
                    if ($userAns !== $correctAns) {
                        $this->session->set_flashdata('danger', 'Jawaban kode keamanan (Captcha) salah. Silakan coba lagi.');
                        redirect('login');
                        return;
                    }
                }

                $username = $this->input->post('username', TRUE);
                $password = $this->input->post('password'); // jangan trim/escape password
                $remember = (bool) $this->input->post('remember');

                try {
                    $result = $this->auth->attempt($username, $password, $remember);
                    if ($result['success']) {
                        // Reset fails
                        $this->session->unset_userdata(['login_fails', 'login_captcha_ans']);
                        redirect('dashboard');
                    }

                    // Tambah hitungan gagal
                    $this->session->set_userdata('login_fails', $fails + 1);
                    $this->session->set_flashdata('danger', $result['message']);
                } catch (\Throwable $e) {
                    log_message('error', 'Login attempt exception: ' . $e->getMessage());
                    $this->session->set_flashdata('danger', 'Error saat login: ' . $e->getMessage());
                }
            }
            redirect('login');
            return;
        }

        // Generate captcha baru jika dibutuhkan
        $captchaQuestion = '';
        if ($showCaptcha) {
            $n1 = rand(2, 9);
            $n2 = rand(1, 9);
            $this->session->set_userdata('login_captcha_ans', $n1 + $n2);
            $captchaQuestion = "Berapa hasil {$n1} + {$n2} ?";
        }

        $regEnabled = function_exists('get_setting') ? get_setting('registration_enabled', '1') : '1';

        $data = [
            'title'                => 'Login - SIPA Kabupaten Tapin',
            'show_captcha'         => $showCaptcha,
            'captcha_question'     => $captchaQuestion,
            'registration_enabled' => ($regEnabled === '1')
        ];
        $this->load->view('auth/login', $data);
        return;
    }

    public function logout()
    {
        $this->auth->logout();
        $this->session->set_flashdata('success', 'Anda telah berhasil logout.');
        redirect('login');
    }

    protected function _get_skpd_list() {
        return $this->db->get_where('skpd', ['is_active' => 1])->result();
    }
}
