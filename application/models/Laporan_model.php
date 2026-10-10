<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Laporan_model
 * Menyediakan agregasi data eksekutif, rekapitulasi RKBMD, SSH, SBU,
 * dan matriks kepatuhan pengusulan SKPD se-Kabupaten Tapin.
 */
class Laporan_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil 4 KPI Ringkasan Eksekutif Terpadu
     */
    public function getExecutiveKpi($tahun, $skpdId = NULL)
    {
        // 1. Agregasi RKBMD
        $this->db->select("
            COUNT(*) as total_usulan,
            COALESCE(SUM(total_nilai), 0) as total_nilai,
            COALESCE(SUM(CASE WHEN status = 'disetujui' THEN total_nilai ELSE 0 END), 0) as nilai_disetujui,
            COUNT(CASE WHEN status = 'disetujui' THEN 1 END) as usulan_disetujui,
            COUNT(DISTINCT skpd_id) as skpd_aktif
        ");
        $this->db->from('rkbmd_usulan');
        $this->db->where('tahun_anggaran', (int) $tahun);
        if (!empty($skpdId)) {
            $this->db->where('skpd_id', (int) $skpdId);
        }
        $rkbmd = $this->db->get()->row();

        // 2. Agregasi SSH & SBU
        $this->db->select("
            COUNT(*) as total_usulan,
            COALESCE(SUM(harga_usulan), 0) as total_nilai,
            COALESCE(SUM(CASE WHEN status_proses = 'Ditetapkan' THEN COALESCE(harga_ditetapkan, harga_usulan) ELSE 0 END), 0) as nilai_ditetapkan,
            COUNT(CASE WHEN status_proses = 'Ditetapkan' THEN 1 END) as usulan_ditetapkan,
            COUNT(DISTINCT id_skpd) as skpd_aktif
        ");
        $this->db->from('standar_harga_usulan');
        $this->db->where('tahun_anggaran', (int) $tahun);
        if (!empty($skpdId)) {
            $this->db->where('id_skpd', (int) $skpdId);
        }
        $ssh = $this->db->get()->row();

        // 3. Total SKPD di sistem
        $totalSkpd = $this->db->from('skpd')->where('is_active', 1)->count_all_results();
        if ($totalSkpd === 0) $totalSkpd = $this->db->from('skpd')->count_all_results();

        // Gabungan SKPD unik yang mengusulkan
        $this->db->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        $qSkpdAktif = $this->db->query("
            SELECT COUNT(DISTINCT skpd_id) as total FROM (
                SELECT skpd_id FROM rkbmd_usulan WHERE tahun_anggaran = " . (int)$tahun . (!empty($skpdId) ? " AND skpd_id = " . (int)$skpdId : "") . "
                UNION
                SELECT id_skpd as skpd_id FROM standar_harga_usulan WHERE tahun_anggaran = " . (int)$tahun . (!empty($skpdId) ? " AND id_skpd = " . (int)$skpdId : "") . "
            ) as t
        ")->row();
        $skpdUnikAktif = (int) ($qSkpdAktif->total ?? 0);

        $totalUsulanAll = (int)($rkbmd->total_usulan ?? 0) + (int)($ssh->total_usulan ?? 0);
        $totalNilaiAll  = (float)($rkbmd->total_nilai ?? 0) + (float)($ssh->total_nilai ?? 0);
        $totalDisetujui = (float)($rkbmd->nilai_disetujui ?? 0) + (float)($ssh->nilai_ditetapkan ?? 0);
        $approvalRate   = ($totalNilaiAll > 0) ? round(($totalDisetujui / $totalNilaiAll) * 100, 1) : 0;
        $kepatuhanRate  = ($totalSkpd > 0) ? round(($skpdUnikAktif / $totalSkpd) * 100, 1) : 0;

        return [
            'total_usulan'     => $totalUsulanAll,
            'total_nilai'      => $totalNilaiAll,
            'nilai_disetujui'  => $totalDisetujui,
            'efisiensi'        => max(0, $totalNilaiAll - $totalDisetujui),
            'approval_rate'    => $approvalRate,
            'skpd_aktif'       => $skpdUnikAktif,
            'total_skpd'       => $totalSkpd,
            'kepatuhan_rate'   => $kepatuhanRate,
            'rkbmd_total'      => (int)($rkbmd->total_usulan ?? 0),
            'ssh_sbu_total'    => (int)($ssh->total_usulan ?? 0)
        ];
    }

    /**
     * Rekap Usulan RKBMD per Jenis dan Status
     */
    public function getRkbmdStatistik($filter = [])
    {
        $this->db->select('jenis_usulan, status, COUNT(*) AS jumlah, SUM(total_nilai) AS total_nilai');
        $this->db->from('rkbmd_usulan');
        if (!empty($filter['skpd_id']))  $this->db->where('skpd_id', (int) $filter['skpd_id']);
        if (!empty($filter['tahun']))    $this->db->where('tahun_anggaran', (int) $filter['tahun']);
        if (!empty($filter['status']))   $this->db->where('status', $filter['status']);
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('nomor_usulan', $q)
                ->or_like('keterangan', $q)
                ->group_end();
        }
        $this->db->group_by(['jenis_usulan', 'status']);
        return $this->db->get()->result();
    }

    /**
     * Rekap Usulan Standar Harga (SSH & SBU)
     */
    public function getSshSbuStatistik($filter = [])
    {
        $this->db->select("
            tipe,
            status_proses,
            COUNT(*) as jumlah,
            COALESCE(SUM(harga_usulan), 0) as total_usulan,
            COALESCE(SUM(CASE WHEN status_proses = 'Ditetapkan' THEN COALESCE(harga_ditetapkan, harga_usulan) ELSE 0 END), 0) as total_ditetapkan
        ");
        $this->db->from('standar_harga_usulan');
        if (!empty($filter['skpd_id'])) $this->db->where('id_skpd', (int) $filter['skpd_id']);
        if (!empty($filter['tahun']))   $this->db->where('tahun_anggaran', (int) $filter['tahun']);
        if (!empty($filter['status']))  $this->db->where('status_proses', $filter['status']);
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('kode_usulan', $q)
                ->or_like('uraian', $q)
                ->or_like('spesifikasi', $q)
                ->group_end();
        }
        $this->db->group_by(['tipe', 'status_proses']);
        return $this->db->get()->result();
    }

    /**
     * Daftar Usulan Standar Harga untuk Tab Laporan SSH & SBU
     */
    public function getSshSbuList($filter = [], $limit = 100)
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd');
        $this->db->from('standar_harga_usulan u');
        $this->db->join('skpd s', 's.id = u.id_skpd', 'left');

        if (!empty($filter['tipe']))    $this->db->where('u.tipe', $filter['tipe']);
        if (!empty($filter['skpd_id'])) $this->db->where('u.id_skpd', (int) $filter['skpd_id']);
        if (!empty($filter['tahun']))   $this->db->where('u.tahun_anggaran', (int) $filter['tahun']);
        if (!empty($filter['status']))  $this->db->where('u.status_proses', $filter['status']);
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.kode_usulan', $q)
                ->or_like('u.uraian', $q)
                ->or_like('u.spesifikasi', $q)
                ->or_like('s.nama_skpd', $q)
                ->group_end();
        }

        $this->db->order_by('u.created_at', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result();
    }

    /**
     * Matriks Kepatuhan Pengusulan Seluruh SKPD Kabupaten Tapin
     */
    public function getKepatuhanSkpdList($tahun, $q = NULL)
    {
        $tahun = (int) $tahun;
        $sql = "
            SELECT 
                s.id as skpd_id,
                s.kode_skpd,
                s.nama_skpd,
                COALESCE(r.total_rkbmd, 0) as total_rkbmd,
                COALESCE(r.rkbmd_disetujui, 0) as rkbmd_disetujui,
                COALESCE(r.nilai_rkbmd, 0) as nilai_rkbmd,
                COALESCE(h.total_ssh, 0) as total_ssh,
                COALESCE(h.total_sbu, 0) as total_sbu,
                COALESCE(h.standar_ditetapkan, 0) as standar_ditetapkan,
                COALESCE(h.nilai_standar, 0) as nilai_standar,
                (COALESCE(r.total_rkbmd, 0) + COALESCE(h.total_ssh, 0) + COALESCE(h.total_sbu, 0)) as grand_total_usulan,
                (COALESCE(r.nilai_rkbmd, 0) + COALESCE(h.nilai_standar, 0)) as grand_total_nilai
            FROM skpd s
            LEFT JOIN (
                SELECT 
                    skpd_id,
                    COUNT(*) as total_rkbmd,
                    COUNT(CASE WHEN status = 'disetujui' THEN 1 END) as rkbmd_disetujui,
                    SUM(total_nilai) as nilai_rkbmd
                FROM rkbmd_usulan
                WHERE tahun_anggaran = {$tahun}
                GROUP BY skpd_id
            ) r ON r.skpd_id = s.id
            LEFT JOIN (
                SELECT 
                    id_skpd,
                    COUNT(CASE WHEN tipe = 'SSH' THEN 1 END) as total_ssh,
                    COUNT(CASE WHEN tipe = 'SBU' THEN 1 END) as total_sbu,
                    COUNT(CASE WHEN status_proses = 'Ditetapkan' THEN 1 END) as standar_ditetapkan,
                    SUM(harga_usulan) as nilai_standar
                FROM standar_harga_usulan
                WHERE tahun_anggaran = {$tahun}
                GROUP BY id_skpd
            ) h ON h.id_skpd = s.id
        ";

        if (!empty($q)) {
            $qEsc = $this->db->escape_like_str($q);
            $sql .= " WHERE s.nama_skpd LIKE '%{$qEsc}%' OR s.kode_skpd LIKE '%{$qEsc}%'";
        }

        $sql .= " ORDER BY grand_total_usulan DESC, s.nama_skpd ASC";

        return $this->db->query($sql)->result();
    }

    /**
     * Top 5 SKPD Berdasarkan Pagu Nilai Usulan
     */
    public function getTopSkpdAnggaran($tahun, $limit = 5)
    {
        $tahun = (int) $tahun;
        $limit = (int) $limit;
        $sql = "
            SELECT 
                s.nama_skpd,
                COALESCE(SUM(u.total_nilai), 0) as total_nilai
            FROM rkbmd_usulan u
            JOIN skpd s ON s.id = u.skpd_id
            WHERE u.tahun_anggaran = {$tahun}
            GROUP BY u.skpd_id, s.nama_skpd
            ORDER BY total_nilai DESC
            LIMIT {$limit}
        ";
        return $this->db->query($sql)->result();
    }
}
