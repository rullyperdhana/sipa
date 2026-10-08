<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        $this->masuk();
    }

    public function masuk()
    {
        if ($this->auth->check()) redirect('dashboard');

        if ($this->input->method() === 'post') {
            // Validasi server-side
            $this->form_validation->set_rules('username', 'Username', 'required|trim|max_length[50]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[3]|max_length[200]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
            } else {
                $username = $this->input->post('username', TRUE);
                $password = $this->input->post('password'); // jangan trim/escape password
                $remember = (bool) $this->input->post('remember');

                try {
                    $result = $this->auth->attempt($username, $password, $remember);
                    if ($result['success']) {
                        redirect('dashboard');
                    }
                    $this->session->set_flashdata('danger', $result['message']);
                } catch (\Throwable $e) {
                    log_message('error', 'Login attempt exception: ' . $e->getMessage());
                    $this->session->set_flashdata('danger', 'Error saat login: ' . $e->getMessage());
                }
            }
            redirect('login');
        }

        $data = ['title' => 'Login - SIRKBMD'];
        $this->load->view('auth/login', $data);
        return;
    }

    public function logout()
    {
        $this->auth->logout();
        $this->session->set_flashdata('success', 'Anda telah berhasil logout.');
        redirect('login');
    }

    // Helper to get SKPD list for registration (if needed directly in login context)
    // This is just an example, typically SKPD list would be fetched in Register controller
    protected function _get_skpd_list() {
        return $this->db->get_where('skpd', ['is_active' => 1])->result();
    }
}
