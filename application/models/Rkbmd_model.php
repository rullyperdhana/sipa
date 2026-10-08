<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model utama RKBMD - menangani usulan untuk semua jenis.
 */
class Rkbmd_model extends CI_Model
{
    /** Detail tabel per jenis usulan */
    protected $detailTables = [
        'pengadaan'        => 'rkbmd_pengadaan',
        'pemeliharaan'     => 'rkbmd_pemeliharaan',
        'pemanfaatan'      => 'rkbmd_pemanfaatan',
        'pemindahtanganan' => 'rkbmd_pemindahtanganan',
        'penghapusan'      => 'rkbmd_penghapusan'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('app');
    }

    public function getDetailTable($jenis)
    {
        return $this->detailTables[$jenis] ?? NULL;
    }

    public function getDetailTotalColumn($jenis)
    {
        $map = [
            'pengadaan'        => 'total_harga',
            'pemeliharaan'     => 'total_harga',
            'pemanfaatan'      => 'harga_perolehan',
            'pemindahtanganan' => 'harga_perolehan',
            'penghapusan'      => 'harga_perolehan'
        ];

        return $map[$jenis] ?? NULL;
    }

    /**
     * Daftar usulan dengan filter.
     */
    public function getUsulan($filter = [])
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, p.tahun AS tahun_periode, c.nama_lengkap AS nama_creator, v.nama_lengkap AS nama_verifikator');
        $this->db->from('rkbmd_usulan u');
        $this->db->join('skpd s', 's.id = u.skpd_id', 'left');
        $this->db->join('rkbmd_periode p', 'p.id = u.periode_id', 'left');
        $this->db->join('users c', 'c.id = u.created_by', 'left');
        $this->db->join('users v', 'v.id = u.verified_by', 'left');

        if (!empty($filter['jenis']))      $this->db->where('u.jenis_usulan', $filter['jenis']);
        if (!empty($filter['skpd_id']))    $this->db->where('u.skpd_id', (int) $filter['skpd_id']);
        if (!empty($filter['status']))     $this->db->where('u.status', $filter['status']);
        if (!empty($filter['periode_id'])) $this->db->where('u.periode_id', (int) $filter['periode_id']);
        if (!empty($filter['tahun']))      $this->db->where('u.tahun_anggaran', (int) $filter['tahun']);
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.nomor_usulan', $q)
                ->or_like('u.keterangan', $q)
                ->or_like('s.nama_skpd', $q)
                ->group_end();
        }

        $this->db->order_by('u.created_at', 'DESC');

        if (!empty($filter['limit'])) {
            $this->db->limit((int) $filter['limit'], (int) ($filter['offset'] ?? 0));
        }

        return $this->db->get()->result();
    }

    public function findUsulan($id)
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, s.kepala_skpd, s.nip_kepala, s.jabatan_kepala, p.tahun AS tahun_periode');
        $this->db->from('rkbmd_usulan u');
        $this->db->join('skpd s', 's.id = u.skpd_id', 'left');
        $this->db->join('rkbmd_periode p', 'p.id = u.periode_id', 'left');
        $this->db->where('u.id', (int) $id);
        return $this->db->get()->row();
    }

    public function getDetail($jenis, $usulanId)
    {
        $table = $this->getDetailTable($jenis);
        if (!$table) return [];

        $this->db->select('d.*, b.kode_barang, b.nama_barang AS barang_nama, b.satuan AS barang_satuan, b.kategori');
        $this->db->from("{$table} d");
        $this->db->join('barang b', 'b.id = d.barang_id', 'left');
        $this->db->where('d.usulan_id', (int) $usulanId);
        $this->db->order_by('d.urutan', 'ASC');
        $this->db->order_by('d.id', 'ASC');
        return $this->db->get()->result();
    }

    public function findDetail($jenis, $detailId)
    {
        $table = $this->getDetailTable($jenis);
        if (!$table) return NULL;
        return $this->db->get_where($table, ['id' => (int) $detailId])->row();
    }

    /**
     * Buat usulan baru. Transaksi.
     */
    public function createUsulan($data)
    {
        $this->db->trans_start();

        $userId = $this->session->userdata('user_id');
        $skpdId = (int) $data['skpd_id'];
        $kodeSkpd = $this->db->select('kode_skpd')->get_where('skpd', ['id' => $skpdId])->row()->kode_skpd ?? 'XXX';

        $insert = [
            'nomor_usulan'    => generate_nomor_usulan($data['jenis_usulan'], $kodeSkpd, $data['tahun_anggaran']),
            'jenis_usulan'   => $data['jenis_usulan'],
            'periode_id'     => (int) $data['periode_id'],
            'skpd_id'        => $skpdId,
            'tahun_anggaran' => (int) $data['tahun_anggaran'],
            'tanggal_usulan' => $data['tanggal_usulan'] ?? date('Y-m-d'),
            'keterangan'     => $data['keterangan'] ?? NULL,
            'is_nihil'       => !empty($data['is_nihil']) ? 1 : 0,
            'status'         => 'draft',
            'created_by'     => (int) $userId,
            'created_at'     => date('Y-m-d H:i:s')
        ];

        $this->db->insert('rkbmd_usulan', $insert);
        $usulanId = (int) $this->db->insert_id();

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) return FALSE;

        $this->logger->record('create', 'rkbmd_' . $data['jenis_usulan'], "Membuat usulan #{$insert['nomor_usulan']}", NULL, $insert);
        return $usulanId;
    }

    public function updateUsulan($id, $data)
    {
        $allowed = ['tanggal_usulan', 'keterangan', 'tahun_anggaran', 'periode_id', 'is_nihil'];
        $update = array_intersect_key($data, array_flip($allowed));

        if (empty($update)) return FALSE;

        $old = $this->findUsulan($id);
        $this->db->where('id', (int) $id)->update('rkbmd_usulan', $update);
        $this->logger->record('update', 'rkbmd', "Update usulan #{$old->nomor_usulan}", (array) $old, $update);
        return TRUE;
    }

    public function deleteUsulan($id)
    {
        $usulan = $this->findUsulan($id);
        if (!$usulan) return FALSE;

        // Hanya boleh dihapus saat draft / ditolak
        if (!in_array($usulan->status, ['draft', 'ditolak'], TRUE)) return FALSE;

        $this->db->where('id', (int) $id)->delete('rkbmd_usulan');
        $this->logger->record('delete', 'rkbmd', "Hapus usulan #{$usulan->nomor_usulan}", (array) $usulan);
        return TRUE;
    }

    /**
     * Tambah detail item ke usulan.
     */
    public function addDetail($jenis, $usulanId, $data)
    {
        $table = $this->getDetailTable($jenis);
        if (!$table) return FALSE;

        $data['usulan_id']  = (int) $usulanId;
        $data['created_at'] = date('Y-m-d H:i:s');

        // Hitung total untuk pengadaan & pemeliharaan
        if (in_array($jenis, ['pengadaan', 'pemeliharaan'])) {
            $jumlahKey = $jenis === 'pengadaan' ? 'kebutuhan_riil_jumlah' : 'jumlah_pemeliharaan';
            $jumlah = (int) ($data[$jumlahKey] ?? 0);
            $harga  = (float) ($data['harga_satuan'] ?? 0);
            $data['total_harga'] = $jumlah * $harga;
        }

        $this->db->insert($table, $data);
        $detailId = (int) $this->db->insert_id();

        $this->recalculateTotals($usulanId);
        return $detailId;
    }

    public function updateDetail($jenis, $detailId, $data)
    {
        $table = $this->getDetailTable($jenis);
        if (!$table) return FALSE;

        $detail = $this->findDetail($jenis, $detailId);
        if (!$detail) return FALSE;

        unset($data['id'], $data['usulan_id'], $data['created_at']);

        if (in_array($jenis, ['pengadaan', 'pemeliharaan'])) {
            $jumlahKey = $jenis === 'pengadaan' ? 'kebutuhan_riil_jumlah' : 'jumlah_pemeliharaan';
            if (isset($data[$jumlahKey]) || isset($data['harga_satuan'])) {
                $jumlah = (int) ($data[$jumlahKey] ?? $detail->{$jumlahKey} ?? 0);
                $harga  = (float) ($data['harga_satuan'] ?? $detail->harga_satuan ?? 0);
                $data['total_harga'] = $jumlah * $harga;
            }
        }

        $this->db->where('id', (int) $detailId)->update($table, $data);
        $this->recalculateTotals($detail->usulan_id);
        return TRUE;
    }

    public function deleteDetail($jenis, $detailId)
    {
        $table = $this->getDetailTable($jenis);
        if (!$table) return FALSE;

        $detail = $this->findDetail($jenis, $detailId);
        if (!$detail) return FALSE;

        $this->db->where('id', (int) $detailId)->delete($table);
        $this->recalculateTotals($detail->usulan_id);
        return TRUE;
    }

    /**
     * Recalculate total nilai & total item untuk usulan.
     */
    protected function recalculateTotals($usulanId)
    {
        $usulan = $this->findUsulan($usulanId);
        if (!$usulan) return;

        $table = $this->getDetailTable($usulan->jenis_usulan);
        if (!$table) return;

        $sumColumn = $this->getDetailTotalColumn($usulan->jenis_usulan) ?: 'total_harga';

        $row = $this->db->select_sum($sumColumn, 'total')
            ->select('COUNT(*) AS jml', FALSE)
            ->where('usulan_id', (int) $usulanId)
            ->get($table)->row();

        $this->db->where('id', (int) $usulanId)->update('rkbmd_usulan', [
            'total_nilai' => $row->total ?: 0,
            'total_item'  => (int) $row->jml
        ]);
    }

    /**
     * Submit usulan (SKPD -> Verifikator).
     */
    public function submit($usulanId)
    {
        $usulan = $this->findUsulan($usulanId);
        if (!$usulan) return ['success' => FALSE, 'message' => 'Usulan tidak ditemukan.'];
        if ($usulan->status !== 'draft' && $usulan->status !== 'revisi') {
            return ['success' => FALSE, 'message' => 'Usulan sudah diajukan sebelumnya.'];
        }

        $table = $this->getDetailTable($usulan->jenis_usulan);
        $detailCount = 0;
        if ($table) {
            $detailCount = (int) $this->db->where('usulan_id', (int) $usulanId)->from($table)->count_all_results();
        }

        if ($detailCount === 0 && empty($usulan->is_nihil)) {
            return ['success' => FALSE, 'message' => 'Usulan tidak memiliki item. Tambahkan minimal 1 item terlebih dahulu.'];
        }

        if (!empty($usulan->is_nihil) && $detailCount > 0) {
            return ['success' => FALSE, 'message' => 'Laporan nihil tidak dapat digunakan ketika usulan memiliki item.'];
        }

        if ($usulan->total_item !== $detailCount) {
            $this->recalculateTotals($usulanId);
        }

        $this->db->where('id', (int) $usulanId)->update('rkbmd_usulan', [
            'status'       => 'diajukan',
            'submitted_at' => date('Y-m-d H:i:s')
        ]);
        $this->logger->record('submit', 'rkbmd', "Submit usulan #{$usulan->nomor_usulan}");

        // Notifikasi ke verifikator
        $verifikators = $this->db->where_in('role', ['verifikator', 'admin'])->where('is_active', 1)->get('users')->result();
        foreach ($verifikators as $v) {
            $this->db->insert('notifikasi', [
                'user_id' => $v->id,
                'judul'   => 'Usulan RKBMD Baru',
                'pesan'   => "Usulan RKBMD {$usulan->jenis_usulan} dari {$usulan->nomor_usulan} menunggu verifikasi.",
                'link'    => site_url("verifikasi/detail/{$usulanId}"),
                'tipe'    => 'info'
            ]);
        }

        return ['success' => TRUE, 'message' => 'Usulan berhasil diajukan.'];
    }

    /**
     * Verifikasi/approve/reject usulan oleh BPKAD.
     */
    public function verifikasi($usulanId, $aksi, $catatan = '')
    {
        $usulan = $this->findUsulan($usulanId);
        if (!$usulan) return ['success' => FALSE, 'message' => 'Usulan tidak ditemukan.'];

        $userId = $this->session->userdata('user_id');
        $now = date('Y-m-d H:i:s');

        $statusMap = [
            'verifikasi' => 'diverifikasi',
            'setuju'     => 'disetujui',
            'tolak'      => 'ditolak',
            'revisi'     => 'revisi'
        ];

        if (!isset($statusMap[$aksi])) {
            return ['success' => FALSE, 'message' => 'Aksi tidak dikenali.'];
        }

        $update = [
            'status'              => $statusMap[$aksi],
            'catatan_verifikator' => $catatan,
            'verified_by'         => (int) $userId,
            'verified_at'         => $now
        ];
        if ($aksi === 'setuju') {
            $update['approved_by'] = (int) $userId;
            $update['approved_at'] = $now;
        }

        $this->db->where('id', (int) $usulanId)->update('rkbmd_usulan', $update);
        $this->logger->record($aksi, 'verifikasi', "Verifikasi #{$usulan->nomor_usulan}: {$aksi}");

        // Notifikasi ke pembuat usulan
        $this->db->insert('notifikasi', [
            'user_id' => $usulan->created_by,
            'judul'   => 'Status Usulan Diperbarui',
            'pesan'   => "Usulan {$usulan->nomor_usulan} status: " . $statusMap[$aksi],
            'link'    => site_url("rkbmd/{$usulan->jenis_usulan}/detail/{$usulanId}"),
            'tipe'    => $aksi === 'tolak' ? 'danger' : ($aksi === 'setuju' ? 'success' : 'info')
        ]);

        return ['success' => TRUE, 'message' => 'Verifikasi berhasil disimpan.'];
    }

    /**
     * Statistik dashboard.
     */
    public function getStatistik($filter = [])
    {
        $this->db->select('jenis_usulan, status, COUNT(*) AS jumlah, SUM(total_nilai) AS total_nilai');
        $this->db->from('rkbmd_usulan');
        if (!empty($filter['skpd_id']))  $this->db->where('skpd_id', (int) $filter['skpd_id']);
        if (!empty($filter['tahun']))    $this->db->where('tahun_anggaran', (int) $filter['tahun']);
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

    public function getMonitoringBySkpd($filter = [])
    {
        $this->db->select('u.skpd_id, s.nama_skpd, u.status, COUNT(*) AS jumlah, MAX(u.created_at) AS last_update', FALSE);
        $this->db->from('rkbmd_usulan u');
        $this->db->join('skpd s', 's.id = u.skpd_id', 'left');

        if (!empty($filter['jenis']))      $this->db->where('u.jenis_usulan', $filter['jenis']);
        if (!empty($filter['periode_id'])) $this->db->where('u.periode_id', (int) $filter['periode_id']);
        if (!empty($filter['skpd_id']))    $this->db->where('u.skpd_id', (int) $filter['skpd_id']);
        if (!empty($filter['tahun']))      $this->db->where('u.tahun_anggaran', (int) $filter['tahun']);

        $this->db->group_by(['u.skpd_id', 'u.status']);
        $this->db->order_by('s.nama_skpd', 'ASC');
        return $this->db->get()->result();
    }
}
