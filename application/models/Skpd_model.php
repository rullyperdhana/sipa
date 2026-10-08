<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpd_model extends CI_Model
{
    protected $table = 'skpd';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil semua data SKPD yang berstatus aktif.
     * Digunakan untuk menampilkan pilihan SKPD pada form registrasi mandiri.
     */
    public function get_all_skpd_active()
    {
        $this->db->order_by('nama_skpd', 'ASC');
        return $this->db->get_where($this->table, ['is_active' => 1])->result();
    }

    /**
     * Mencari satu data SKPD berdasarkan ID.
     */
    public function find($id)
    {
        return $this->db->get_where($this->table, ['id' => (int) $id])->row();
    }

    /**
     * Update data SKPD
     */
    public function update($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }
}