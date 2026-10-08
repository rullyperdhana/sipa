<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logger - mencatat audit trail aktivitas user.
 */
class Logger
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function record($aksi, $modul, $keterangan = '', $dataLama = NULL, $dataBaru = NULL)
    {
        $userId   = $this->CI->session->userdata('user_id');
        $username = $this->CI->session->userdata('username');

        $this->CI->db->insert('log_aktivitas', [
            'user_id'     => $userId ?: NULL,
            'username'    => $username ?: NULL,
            'aksi'        => $aksi,
            'modul'       => $modul,
            'keterangan'  => $keterangan,
            'data_lama'   => $dataLama ? json_encode($dataLama, JSON_UNESCAPED_UNICODE) : NULL,
            'data_baru'   => $dataBaru ? json_encode($dataBaru, JSON_UNESCAPED_UNICODE) : NULL,
            'ip_address'  => $this->CI->input->ip_address(),
            'user_agent'  => substr($this->CI->input->user_agent() ?: '', 0, 255),
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }
}
