<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_model extends CI_Model
{
    // ===== SKPD =====
    public function getAllSkpd($activeOnly = TRUE)
    {
        if ($activeOnly) $this->db->where('is_active', 1);
        return $this->db->order_by('nama_skpd', 'ASC')->get('skpd')->result();
    }

    public function findSkpd($id)
    {
        return $this->db->get_where('skpd', ['id' => (int) $id])->row();
    }

    public function getSkpdById($id)
    {
        return $this->findSkpd($id);
    }

    public function saveSkpd($data, $id = NULL)
    {
        if ($id) {
            return $this->db->where('id', (int) $id)->update('skpd', $data);
        }
        $this->db->insert('skpd', $data);
        return (int) $this->db->insert_id();
    }

    public function deleteSkpd($id)
    {
        return $this->db->where('id', (int) $id)->delete('skpd');
    }

    // ===== BARANG =====
    private function _applyFilterBarang($filter = [])
    {
        if (!empty($filter['kategori'])) {
            $this->db->where('kategori', $filter['kategori']);
        }
        if (!empty($filter['q'])) {
            $q = trim($filter['q']);
            $this->db->group_start()
                ->like('nama_barang', $q)
                ->or_like('kode_barang', $q)
                ->group_end();
        }
        if (isset($filter['is_active']) && $filter['is_active'] !== '') {
            $this->db->where('is_active', (int) $filter['is_active']);
        } elseif (!empty($filter['active_only'])) {
            $this->db->where('is_active', 1);
        }
        if (isset($filter['has_harga']) && $filter['has_harga'] !== '') {
            if ($filter['has_harga'] == '1') {
                $this->db->where('harga_standar >', 0);
            } elseif ($filter['has_harga'] == '0') {
                $this->db->where('harga_standar <=', 0);
            }
        }
    }

    public function getAllBarang($filter = [], $limit = 0, $offset = 0)
    {
        $this->_applyFilterBarang($filter);

        $orderCol = $filter['order_by'] ?? 'kode_barang';
        $orderDir = (isset($filter['order_dir']) && strtoupper($filter['order_dir']) === 'DESC') ? 'DESC' : 'ASC';
        $this->db->order_by($orderCol, $orderDir);

        if ($limit > 0) {
            $this->db->limit((int) $limit, (int) $offset);
        } elseif (!empty($filter['limit'])) {
            $this->db->limit((int) $filter['limit'], (int) ($filter['offset'] ?? 0));
        }

        return $this->db->get('barang')->result();
    }

    public function countBarang($filter = [])
    {
        $this->_applyFilterBarang($filter);
        return $this->db->count_all_results('barang');
    }

    public function getStatistikBarang()
    {
        $total   = $this->db->count_all('barang');
        $mesin   = $this->db->where('kategori', 'Peralatan dan Mesin')->count_all_results('barang');
        $gedung  = $this->db->where('kategori', 'Gedung dan Bangunan')->count_all_results('barang');
        $tanah   = $this->db->where('kategori', 'Tanah')->count_all_results('barang');
        $lainnya = $this->db->where_not_in('kategori', ['Peralatan dan Mesin', 'Gedung dan Bangunan', 'Tanah'])->count_all_results('barang');
        $aktif   = $this->db->where('is_active', 1)->count_all_results('barang');

        return (object) [
            'total'   => $total,
            'mesin'   => $mesin,
            'gedung'  => $gedung,
            'tanah'   => $tanah,
            'lainnya' => $lainnya,
            'aktif'   => $aktif
        ];
    }

    public function findBarang($id)
    {
        return $this->db->get_where('barang', ['id' => (int) $id])->row();
    }

    public function saveBarang($data, $id = NULL)
    {
        if ($id) return $this->db->where('id', (int) $id)->update('barang', $data);
        $this->db->insert('barang', $data);
        return (int) $this->db->insert_id();
    }

    public function deleteBarang($id)
    {
        return $this->db->where('id', (int) $id)->delete('barang');
    }

    // ===== PERIODE =====
    public function getAllPeriode()
    {
        return $this->db->order_by('tahun', 'DESC')->get('rkbmd_periode')->result();
    }

    public function getActivePeriode()
    {
        return $this->db->where('status', 'open')
            ->order_by('tahun', 'DESC')
            ->get('rkbmd_periode')->result();
    }

    public function findPeriode($id)
    {
        return $this->db->get_where('rkbmd_periode', ['id' => (int) $id])->row();
    }

    public function savePeriode($data, $id = NULL)
    {
        if ($id) return $this->db->where('id', (int) $id)->update('rkbmd_periode', $data);
        $this->db->insert('rkbmd_periode', $data);
        return (int) $this->db->insert_id();
    }
}
