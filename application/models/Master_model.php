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
    public function getAllBarang($filter = [])
    {
        if (!empty($filter['kategori'])) $this->db->where('kategori', $filter['kategori']);
        if (!empty($filter['q'])) {
            $this->db->group_start()
                ->like('nama_barang', $filter['q'])
                ->or_like('kode_barang', $filter['q'])
                ->group_end();
        }
        if (!empty($filter['active_only'])) $this->db->where('is_active', 1);
        if (!empty($filter['limit'])) $this->db->limit((int) $filter['limit']);

        return $this->db->order_by('nama_barang', 'ASC')->get('barang')->result();
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
