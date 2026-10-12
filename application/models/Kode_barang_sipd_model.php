<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Referensi Kode Barang SIPD RI
 * Tabel: ref_kode_barang_sipd
 * Pemerintah Kabupaten Tapin - BKAD
 */
class Kode_barang_sipd_model extends CI_Model
{
    protected $table = 'ref_kode_barang_sipd';

    /**
     * Mengambil daftar referensi kode barang SIPD dengan filter, pagination, dan pencarian
     */
    public function getKodeBarang($filter = [], $limit = 50, $offset = 0)
    {
        $this->_applyFilter($filter);
        if ($limit > 0) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        $this->db->order_by('kode_barang', 'ASC');
        return $this->db->get($this->table)->result();
    }

    /**
     * Menghitung total data kode barang SIPD berdasarkan filter
     */
    public function countKodeBarang($filter = [])
    {
        $this->_applyFilter($filter);
        return $this->db->count_all_results($this->table);
    }

    /**
     * Query filter helper
     */
    private function _applyFilter($filter = [])
    {
        if (!empty($filter['q'])) {
            $q = trim($filter['q']);
            $this->db->group_start()
                ->like('kode_barang', $q)
                ->or_like('uraian', $q)
                ->group_end();
        }

        if (!empty($filter['kelompok']) && in_array(strtoupper($filter['kelompok']), ['SSH', 'SBU'])) {
            $this->db->where('kelompok', strtoupper($filter['kelompok']));
        }

        if (isset($filter['is_active']) && $filter['is_active'] !== '') {
            $this->db->where('is_active', (int) $filter['is_active']);
        }
    }

    /**
     * Mengambil statistik jumlah total, SSH, dan SBU
     */
    public function getStatistik()
    {
        $total = $this->db->count_all($this->table);
        $ssh   = $this->db->where('kelompok', 'SSH')->count_all_results($this->table);
        $sbu   = $this->db->where('kelompok', 'SBU')->count_all_results($this->table);

        return (object) [
            'total' => (int) $total,
            'ssh'   => (int) $ssh,
            'sbu'   => (int) $sbu
        ];
    }

    /**
     * Mencari data berdasarkan ID
     */
    public function findById($id)
    {
        return $this->db->get_where($this->table, ['id' => (int) $id])->row();
    }

    /**
     * Mencari data berdasarkan kode_barang
     */
    public function findByKode($kode_barang)
    {
        return $this->db->get_where($this->table, ['kode_barang' => trim($kode_barang)])->row();
    }

    /**
     * Live search untuk Select2 / autocomplete dropdown
     */
    public function searchSelect2($term = '', $kelompok = NULL, $limit = 30)
    {
        $term = trim($term);
        $this->db->where('is_active', 1);

        if (!empty($kelompok) && in_array(strtoupper($kelompok), ['SSH', 'SBU'])) {
            $this->db->where('kelompok', strtoupper($kelompok));
        }

        if (!empty($term)) {
            $words = array_filter(explode(' ', $term));
            $this->db->group_start();
            $this->db->like('kode_barang', $term);
            if (!empty($words)) {
                $this->db->or_group_start();
                foreach ($words as $w) {
                    if (strlen($w) > 0) {
                        $this->db->like('uraian', $w);
                    }
                }
                $this->db->group_end();
            }
            $this->db->group_end();
        }

        $this->db->order_by('kode_barang', 'ASC');
        $this->db->limit((int) $limit);
        $rows = $this->db->get($this->table)->result();

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id'       => $r->kode_barang,
                'text'     => "{$r->kode_barang} - {$r->uraian} [{$r->kelompok}]",
                'kode'     => $r->kode_barang,
                'uraian'   => $r->uraian,
                'kelompok' => $r->kelompok
            ];
        }

        return $results;
    }

    /**
     * Simpan / perbarui data
     */
    public function save($data, $id = NULL)
    {
        if ($id) {
            $this->db->where('id', (int) $id)->update($this->table, $data);
            return (int) $id;
        }

        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    /**
     * Hapus data
     */
    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }
}
