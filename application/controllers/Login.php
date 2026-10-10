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
                $tahunAnggaran = (int) $this->input->post('tahun_anggaran');

                try {
                    $result = $this->auth->attempt($username, $password, $remember);
                    if ($result['success']) {
                        // Set tahun anggaran aktif
                        if ($tahunAnggaran >= 2020 && $tahunAnggaran <= 2099) {
                            set_tahun_anggaran($tahunAnggaran);
                        } else {
                            get_tahun_anggaran();
                        }

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
        $availableYears = get_daftar_tahun_anggaran();
        $selectedYear   = get_tahun_anggaran();

        $data = [
            'title'                => 'Login - SIPA Kabupaten Tapin',
            'show_captcha'         => $showCaptcha,
            'captcha_question'     => $captchaQuestion,
            'registration_enabled' => ($regEnabled === '1'),
            'available_years'      => $availableYears,
            'selected_year'        => $selectedYear,
        ];
        $this->load->view('auth/login', $data);
        return;
    }

    /**
     * Beralih tahun anggaran aktif tanpa logout.
     */
    public function switch_year($tahun = null)
    {
        if (!$this->auth->check()) {
            redirect('login');
        }

        $tahun = (int) $tahun;
        if ($tahun >= 2020 && $tahun <= 2099) {
            set_tahun_anggaran($tahun);
            $this->session->set_flashdata('info', "Tahun Anggaran aktif berhasil dialihkan ke <strong>TA {$tahun}</strong>.");
        }

        $ref = $this->input->server('HTTP_REFERER');
        if (!empty($ref) && strpos($ref, site_url()) === 0 && strpos($ref, 'switch-year') === FALSE) {
            redirect($ref);
        } else {
            redirect('dashboard');
        }
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
