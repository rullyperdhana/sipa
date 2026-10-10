<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_model extends CI_Model
{
    // ===== SKPD =====
    public function getAllSkpd($activeOnly = TRUE, $tahun = NULL)
    {
        if ($activeOnly) $this->db->where('s.is_active', 1);

        if ($tahun === NULL && function_exists('get_tahun_anggaran')) {
            $tahun = get_tahun_anggaran();
        }

        if (!empty($tahun) && $this->db->table_exists('skpd_nomenklatur')) {
            $this->db->select("s.*, 
                COALESCE(n.nama_skpd, s.nama_skpd) AS nama_skpd,
                COALESCE(n.kode_skpd, s.kode_skpd) AS kode_skpd,
                COALESCE(n.kepala_skpd, s.kepala_skpd) AS kepala_skpd,
                COALESCE(n.nip_kepala, s.nip_kepala) AS nip_kepala,
                COALESCE(n.jabatan_kepala, s.jabatan_kepala) AS jabatan_kepala,
                COALESCE(n.nama_pengurus, s.nama_pengurus) AS nama_pengurus,
                COALESCE(n.nip_pengurus, s.nip_pengurus) AS nip_pengurus,
                n.tahun_anggaran AS nomenklatur_tahun,
                n.keterangan AS nomenklatur_keterangan");
            $this->db->from('skpd s');
            $this->db->join('skpd_nomenklatur n', 'n.skpd_id = s.id AND n.tahun_anggaran = ' . (int)$tahun, 'left');
            return $this->db->order_by('nama_skpd', 'ASC')->get()->result();
        }

        return $this->db->order_by('nama_skpd', 'ASC')->get('skpd s')->result();
    }

    public function findSkpd($id, $tahun = NULL)
    {
        $skpd = $this->db->get_where('skpd', ['id' => (int) $id])->row();
        if (!$skpd) return NULL;

        if ($tahun === NULL && function_exists('get_tahun_anggaran')) {
            $tahun = get_tahun_anggaran();
        }

        if (!empty($tahun) && $this->db->table_exists('skpd_nomenklatur')) {
            $nomenklatur = $this->db->get_where('skpd_nomenklatur', [
                'skpd_id'        => (int) $id,
                'tahun_anggaran' => (int) $tahun
            ])->row();

            if ($nomenklatur) {
                if (!empty($nomenklatur->nama_skpd))     $skpd->nama_skpd     = $nomenklatur->nama_skpd;
                if (!empty($nomenklatur->kode_skpd))     $skpd->kode_skpd     = $nomenklatur->kode_skpd;
                if (!empty($nomenklatur->kepala_skpd))   $skpd->kepala_skpd   = $nomenklatur->kepala_skpd;
                if (!empty($nomenklatur->nip_kepala))    $skpd->nip_kepala    = $nomenklatur->nip_kepala;
                if (!empty($nomenklatur->jabatan_kepala))$skpd->jabatan_kepala= $nomenklatur->jabatan_kepala;
                if (!empty($nomenklatur->nama_pengurus)) $skpd->nama_pengurus = $nomenklatur->nama_pengurus;
                if (!empty($nomenklatur->nip_pengurus))  $skpd->nip_pengurus  = $nomenklatur->nip_pengurus;
                $skpd->nomenklatur_tahun = (int) $tahun;
                $skpd->nomenklatur_keterangan = $nomenklatur->keterangan ?? '';
                $skpd->is_nomenklatur_custom = TRUE;
            }
        }

        return $skpd;
    }

    public function getSkpdById($id, $tahun = NULL)
    {
        return $this->findSkpd($id, $tahun);
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

    // ===== NOMENKLATUR SKPD PER TAHUN =====
    public function getNomenklaturListBySkpd($skpdId)
    {
        return $this->db->where('skpd_id', (int)$skpdId)
            ->order_by('tahun_anggaran', 'DESC')
            ->get('skpd_nomenklatur')->result();
    }

    public function findNomenklatur($id)
    {
        return $this->db->get_where('skpd_nomenklatur', ['id' => (int)$id])->row();
    }

    public function saveNomenklatur($data, $id = NULL)
    {
        if ($id) {
            $this->db->where('id', (int)$id)->update('skpd_nomenklatur', $data);
            return (int)$id;
        }

        // Cek apakah sudah ada untuk skpd_id dan tahun_anggaran
        $existing = $this->db->get_where('skpd_nomenklatur', [
            'skpd_id'        => (int)$data['skpd_id'],
            'tahun_anggaran' => (int)$data['tahun_anggaran']
        ])->row();

        if ($existing) {
            $this->db->where('id', (int)$existing->id)->update('skpd_nomenklatur', $data);
            return (int)$existing->id;
        }

        $this->db->insert('skpd_nomenklatur', $data);
        return (int)$this->db->insert_id();
    }

    public function deleteNomenklatur($id)
    {
        return $this->db->where('id', (int)$id)->delete('skpd_nomenklatur');
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

    public function getActivePeriode($tahun = NULL)
    {
        $today = date('Y-m-d');
        $this->db->where('status', 'open');
        $this->db->where('tanggal_mulai <=', $today);
        $this->db->where('tanggal_selesai >=', $today);
        if ($tahun) {
            $this->db->where('tahun', (int)$tahun);
        }
        return $this->db->order_by('tahun', 'DESC')->get('rkbmd_periode')->result();
    }

    public function getPeriodeAktif($tahun = NULL)
    {
        if ($tahun === NULL && function_exists('get_tahun_anggaran')) {
            $tahun = get_tahun_anggaran();
        }

        $today = date('Y-m-d');
        $this->db->from('rkbmd_periode');
        $this->db->where('status', 'open');
        $this->db->where('tanggal_mulai <=', $today);
        $this->db->where('tanggal_selesai >=', $today);
        if ($tahun) {
            $this->db->where('tahun', (int)$tahun);
        }
        return $this->db->order_by('tahun', 'DESC')->get()->row();
    }

    public function isPeriodeBuka($tahun = NULL)
    {
        return !empty($this->getPeriodeAktif($tahun));
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
