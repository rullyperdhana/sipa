<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['user_model', 'skpd_model']);
    }

    public function profile()
    {
        $userId = $this->currentUser->id;
        $data = [
            'title' => 'Profil Saya',
            'user'  => $this->user_model->find($userId)
        ];
        $this->load->view('templates/header', $data);
        $this->load->view('user/profile', $data);
        $this->load->view('templates/footer');
    }

    public function update_profile()
    {
        // Validasi data user
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|max_length[150]');
        $this->form_validation->set_rules('email', 'Email', 'valid_email|max_length[100]');
        $this->form_validation->set_rules('nip', 'NIP', 'max_length[25]');
        $this->form_validation->set_rules('jabatan', 'Jabatan', 'max_length[150]');

        // Validasi data SKPD (hanya jika user SKPD)
        if ($this->currentUser->role === 'skpd') {
            $this->form_validation->set_rules('kepala_skpd', 'Nama Kepala SKPD', 'max_length[150]');
            $this->form_validation->set_rules('nip_kepala', 'NIP Kepala SKPD', 'max_length[25]');
            $this->form_validation->set_rules('jabatan_kepala', 'Jabatan Kepala', 'max_length[150]');
            $this->form_validation->set_rules('nama_pengurus', 'Nama Pengurus Barang', 'max_length[150]');
            $this->form_validation->set_rules('nip_pengurus', 'NIP Pengurus Barang', 'max_length[25]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', validation_errors());
            redirect('profile');
        }

        $userId = $this->currentUser->id;

        // Update data user
        $userData = [
            'nama_lengkap' => $this->input->post('nama_lengkap', TRUE),
            'email'        => $this->input->post('email', TRUE),
            'nip'          => $this->input->post('nip', TRUE),
            'jabatan'      => $this->input->post('jabatan', TRUE)
        ];

        $this->user_model->update($userId, $userData);
        $this->session->set_userdata('nama_lengkap', $userData['nama_lengkap']);

        // Update data SKPD (hanya untuk user SKPD)
        if ($this->currentUser->role === 'skpd' && $this->currentUser->skpd_id) {
            $skpdData = [
                'kepala_skpd'    => $this->input->post('kepala_skpd', TRUE),
                'nip_kepala'     => $this->input->post('nip_kepala', TRUE),
                'jabatan_kepala' => $this->input->post('jabatan_kepala', TRUE),
                'nama_pengurus'  => $this->input->post('nama_pengurus', TRUE),
                'nip_pengurus'   => $this->input->post('nip_pengurus', TRUE)
            ];

            $this->skpd_model->update($this->currentUser->skpd_id, $skpdData);
        }

        $this->logger->record('update', 'profile', 'Update profil');
        $this->session->set_flashdata('success', 'Profil berhasil diperbarui.');
        redirect('profile');
    }

    public function change_password()
    {
        $this->form_validation->set_rules('current_password', 'Password Saat Ini', 'required');
        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|min_length[8]|max_length[200]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|matches[new_password]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', validation_errors());
            redirect('profile');
        }

        $userId = $this->currentUser->id;

        if (!$this->user_model->verifyPassword($userId, $this->input->post('current_password'))) {
            $this->session->set_flashdata('danger', 'Password saat ini salah.');
            redirect('profile');
        }

        $this->user_model->changePassword($userId, $this->input->post('new_password'));
        $this->logger->record('change_password', 'profile', 'Ganti password');
        $this->session->set_flashdata('success', 'Password berhasil diubah.');
        redirect('profile');
    }
}
