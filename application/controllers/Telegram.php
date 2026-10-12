<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Telegram Notification Settings
 * Pengelolaan konfigurasi notifikasi bot Telegram ke Admin BKAD / Grup Verifikator.
 */
class Telegram extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['telegram_service', 'form_validation']);
        $this->load->helper(['app', 'form']);
    }

    /**
     * Index Router
     */
    public function index()
    {
        redirect('telegram/settings');
    }

    /**
     * Halaman Pengaturan Notifikasi Telegram
     */
    public function settings()
    {
        if ($this->input->method() === 'post') {
            $data = [
                'telegram_enabled'   => $this->input->post('telegram_enabled') ? '1' : '0',
                'telegram_bot_token' => trim($this->input->post('telegram_bot_token', TRUE) ?? ''),
                'telegram_chat_id'   => trim($this->input->post('telegram_chat_id', TRUE) ?? ''),
                'telegram_parse_mode'=> $this->input->post('telegram_parse_mode', TRUE) ?: 'HTML'
            ];

            $this->telegram_service->saveSettings($data);
            $this->session->set_flashdata('success', 'Konfigurasi Notifikasi Telegram berhasil disimpan.');
            redirect('telegram/settings');
        }

        $data = [
            'title'    => 'Pengaturan Notifikasi Telegram',
            'settings' => $this->telegram_service->getSettings()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('master/telegram_settings', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Endpoint AJAX Uji Coba Pengiriman Pesan Bot Telegram
     */
    public function ajax_test()
    {
        if ($this->input->method() !== 'post') {
            return $this->output->set_status_header(405)->set_output(json_encode([
                'success' => false, 
                'message' => 'Method tidak diizinkan.'
            ]));
        }

        $token  = trim($this->input->post('telegram_bot_token', TRUE) ?? '');
        $chatId = trim($this->input->post('telegram_chat_id', TRUE) ?? '');

        $res = $this->telegram_service->testConnection($token, $chatId);
        $res['csrfHash'] = $this->security->get_csrf_hash();

        return $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }
}
