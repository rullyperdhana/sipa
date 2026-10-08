<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Forgot_password extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('form');
        // Load email library if you plan to send actual emails
        // $this->load->library('email');
    }

    public function index()
    {
        if ($this->auth->check()) {
            redirect('dashboard');
        }

        $data['title'] = 'Lupa Password - SIRKBMD';
        $this->load->view('auth/forgot_password', $data);
    }

    public function send_reset_link()
    {
        if ($this->auth->check()) {
            redirect('dashboard');
        }

        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[100]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', validation_errors());
            redirect('forgot-password');
        } else {
            $email = $this->input->post('email', TRUE);
            $user = $this->db->get_where('users', ['email' => $email])->row();

            if (!$user) {
                $this->session->set_flashdata('danger', 'Email tidak terdaftar.');
                redirect('forgot-password');
            }

            $token = $this->auth->generatePasswordResetToken($email);

            if ($token) {
                // --- START: Email Sending Logic (Placeholder) ---
                // In a real application, you would send an email here.
                // Example using CodeIgniter's Email Library:
                /*
                $this->email->from('no-reply@yourdomain.com', 'SIRKBMD');
                $this->email->to($email);
                $this->email->subject('Reset Password SIRKBMD');
                $reset_link = site_url('reset-password/' . $token);
                $message = "Halo " . $user->nama_lengkap . ",\n\n";
                $message .= "Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.\n";
                $message .= "Silakan klik link berikut untuk mereset password Anda: " . $reset_link . "\n\n";
                $message .= "Link ini akan kadaluarsa dalam 1 jam.\n";
                $message .= "Jika Anda tidak meminta reset password, abaikan email ini.\n\n";
                $message .= "Terima kasih,\nTim SIRKBMD";
                $this->email->message($message);

                if ($this->email->send()) {
                    $this->session->set_flashdata('success', 'Link reset password telah dikirim ke email Anda.');
                } else {
                    $this->session->set_flashdata('danger', 'Gagal mengirim email reset password. Silakan coba lagi.');
                    log_message('error', 'Email sending failed: ' . $this->email->print_debugger());
                }
                */
                // For demonstration, we'll just show a success message and log the link
                log_message('info', 'Password Reset Link for ' . $email . ': ' . site_url('reset-password/' . $token));
                $this->session->set_flashdata('success', 'Link reset password telah dikirim ke email Anda. (Cek log aplikasi untuk link demo)');
                // --- END: Email Sending Logic ---
            } else {
                $this->session->set_flashdata('danger', 'Gagal membuat token reset password. Silakan coba lagi.');
            }
            redirect('forgot-password');
        }
    }

    public function reset($token = NULL)
    {
        if ($this->auth->check()) {
            redirect('dashboard');
        }

        if ($token === NULL) {
            $this->session->set_flashdata('danger', 'Token reset password tidak valid.');
            redirect('forgot-password');
        }

        $user = $this->auth->validatePasswordResetToken($token);

        if (!$user) {
            $this->session->set_flashdata('danger', 'Token reset password tidak valid atau sudah kadaluarsa.');
            redirect('forgot-password');
        }

        $this->form_validation->set_rules('password', 'Password Baru', 'required|min_length[6]|max_length[200]');
        $this->form_validation->set_rules('password_confirm', 'Konfirmasi Password Baru', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $data['title'] = 'Reset Password - SIRKBMD';
            $data['token'] = $token;
            $this->load->view('auth/reset_password', $data);
        } else {
            if ($this->auth->resetPassword($user->id, $this->input->post('password'), $token)) {
                $this->session->set_flashdata('success', 'Password Anda berhasil direset. Silakan login.');
                redirect('login');
            } else {
                $this->session->set_flashdata('danger', 'Gagal mereset password. Silakan coba lagi.');
                redirect('reset-password/' . $token);
            }
        }
    }
}