<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Akun_model extends CI_Model
{
    protected $table = 'ref_akun_belanja';
    protected $tableAll = 'ref_akun';

    /**
     * Ambil data referensi akun belanja dengan filter, pencarian, dan pagination
     */
    public function getAkunBelanja($filter = [], $limit = 50, $offset = 0)
    {
        $this->_applyFilter($filter);
        if ($limit > 0) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        $this->db->order_by('kode_akun', 'ASC');
        return $this->db->get($this->table)->result();
    }

    /**
     * Hitung total akun belanja berdasarkan filter
     */
    public function countAkunBelanja($filter = [])
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
                ->like('kode_akun', $q)
                ->or_like('nama_akun', $q)
                ->group_end();
        }

        if (!empty($filter['kelompok'])) {
            $this->db->where('kelompok', $filter['kelompok']);
        }

        if (isset($filter['level']) && $filter['level'] !== '') {
            $this->db->where('level', (int) $filter['level']);
        }

        if (isset($filter['is_leaf']) && $filter['is_leaf'] !== '') {
            $this->db->where('is_leaf', (int) $filter['is_leaf']);
        }

        if (isset($filter['prefix']) && $filter['prefix'] !== '') {
            $this->db->like('kode_akun', $filter['prefix'], 'after');
        }

        if (isset($filter['is_active']) && $filter['is_active'] !== '') {
            $this->db->where('is_active', (int) $filter['is_active']);
        }
    }

    /**
     * Ambil statistik ringkasan akun belanja
     */
    public function getStatistikBelanja()
    {
        $total = $this->db->count_all($this->table);

        $operasi = $this->db->like('kode_akun', '5.1.', 'after')->count_all_results($this->table);
        $modal   = $this->db->like('kode_akun', '5.2.', 'after')->count_all_results($this->table);
        $btt     = $this->db->like('kode_akun', '5.3.', 'after')->count_all_results($this->table);
        $transfer= $this->db->like('kode_akun', '5.4.', 'after')->count_all_results($this->table);

        $leaf    = $this->db->where('is_leaf', 1)->count_all_results($this->table);
        $header  = $this->db->where('is_leaf', 0)->count_all_results($this->table);

        return (object) [
            'total'    => $total,
            'operasi'  => $operasi,
            'modal'    => $modal,
            'btt'      => $btt,
            'transfer' => $transfer,
            'leaf'     => $leaf,
            'header'   => $header
        ];
    }

    /**
     * Cari akun berdasarkan ID
     */
    public function findAkunBelanja($id)
    {
        return $this->db->get_where($this->table, ['id' => (int) $id])->row();
    }

    /**
     * Cari akun berdasarkan kode_akun
     */
    public function findByKode($kode_akun)
    {
        return $this->db->get_where($this->table, ['kode_akun' => trim($kode_akun)])->row();
    }

    /**
     * Live search untuk Select2 / autocomplete dropdown
     */
    public function searchSelect2($term, $leafOnly = true, $limit = 30)
    {
        $term = trim($term);
        if ($leafOnly) {
            $this->db->where('is_leaf', 1);
        }
        $this->db->where('is_active', 1);

        if (!empty($term)) {
            $this->db->group_start()
                ->like('kode_akun', $term)
                ->or_like('nama_akun', $term)
                ->group_end();
        }

        $this->db->order_by('kode_akun', 'ASC');
        $this->db->limit((int) $limit);
        $rows = $this->db->get($this->table)->result();

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id'       => $r->kode_akun,
                'text'     => "{$r->kode_akun} - {$r->nama_akun}",
                'kode'     => $r->kode_akun,
                'nama'     => $r->nama_akun,
                'kelompok' => $r->kelompok,
                'level'    => (int) $r->level
            ];
        }

        return $results;
    }

    /**
     * Simpan / update akun belanja
     */
    public function saveAkunBelanja($data, $id = NULL)
    {
        if ($id) {
            $this->db->where('id', (int) $id)->update($this->table, $data);
            return (int) $id;
        }

        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    /**
     * Hapus akun belanja
     */
    public function deleteAkunBelanja($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }
}
