<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Satuan_model extends CI_Model
{
    protected $table = 'ref_satuan';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil daftar satuan dengan filter, paginasi, dan pengurutan
     */
    public function getSatuan($filter = [], $limit = 50, $offset = 0)
    {
        $this->_applyFilter($filter);
        if ($limit > 0) {
            $this->db->limit((int) $limit, (int) $offset);
        }
        $this->db->order_by('nama_satuan', 'ASC');
        return $this->db->get($this->table)->result();
    }

    /**
     * Menghitung total data satuan berdasarkan filter
     */
    public function countSatuan($filter = [])
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
            $this->db->like('nama_satuan', $q);
        }

        if (isset($filter['is_active']) && $filter['is_active'] !== '') {
            $this->db->where('is_active', (int) $filter['is_active']);
        }
    }

    /**
     * Statistik ringkasan data satuan
     */
    public function getStatistik()
    {
        $total  = $this->db->count_all($this->table);
        $active = $this->db->where('is_active', 1)->count_all_results($this->table);
        $non    = $this->db->where('is_active', 0)->count_all_results($this->table);

        return (object) [
            'total'  => (int) $total,
            'active' => (int) $active,
            'non'    => (int) $non
        ];
    }

    /**
     * Mengambil semua nama satuan aktif (untuk datalist / dropdown form usulan)
     */
    public function getAllActiveNames()
    {
        $this->db->select('nama_satuan')
            ->where('is_active', 1)
            ->order_by('nama_satuan', 'ASC');
        $rows = $this->db->get($this->table)->result();
        return array_column($rows, 'nama_satuan');
    }

    /**
     * Mencari data satuan berdasarkan ID
     */
    public function findById($id)
    {
        return $this->db->get_where($this->table, ['id' => (int) $id])->row();
    }

    /**
     * Mencari data satuan berdasarkan Nama
     */
    public function findByNama($nama)
    {
        return $this->db->get_where($this->table, ['nama_satuan' => trim($nama)])->row();
    }

    /**
     * Autocomplete Select2 untuk live search
     */
    public function searchSelect2($term = '', $limit = 30)
    {
        $term = trim($term);
        $this->db->where('is_active', 1);

        if (!empty($term)) {
            $this->db->like('nama_satuan', $term);
        }

        $this->db->order_by('nama_satuan', 'ASC');
        $this->db->limit((int) $limit);
        $rows = $this->db->get($this->table)->result();

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id'   => $r->nama_satuan,
                'text' => $r->nama_satuan
            ];
        }

        return $results;
    }

    /**
     * Simpan (Create / Update) data satuan
     */
    public function save($data, $id = NULL)
    {
        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int) $id)->update($this->table, $data);
            return (int) $id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            return (int) $this->db->insert_id();
        }
    }

    /**
     * Hapus data satuan
     */
    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }
}
