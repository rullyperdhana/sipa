<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Ssh_model
 * Mengelola data Standar Satuan Harga (SSH) dan Standar Biaya Umum (SBU)
 * dengan penegakan ketat Row Level Security (RLS) pada lapisan aplikasi.
 */
class Ssh_model extends CI_Model
{
    protected $table = 'standar_harga_usulan';
    protected $table_log = 'standar_harga_log';
    protected $table_master = 'ref_standar_harga';
    protected $table_jadwal = 'standar_harga_jadwal';

    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // 1. QUERY BUILDER DENGAN PENEGAKAN RLS (SELECT)
    // =========================================================================

    /**
     * RLS Policy untuk Operator SKPD:
     * Hanya dapat melihat data yang id_skpd-nya cocok dengan SKPD mereka sendiri,
     * ATAU data yang sudah berstatus 'Ditetapkan' (katalog umum).
     */
    public function getUsulanBySkpd($skpd_id, $filter = [])
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, usr.nama_lengkap as nama_pengusul, v.nama_lengkap as nama_verifikator, p.nama_lengkap as nama_penetap');
        $this->db->from($this->table . ' u');
        $this->db->join('skpd s', 's.id = u.id_skpd', 'left');
        $this->db->join('users usr', 'usr.id = u.user_id', 'left');
        $this->db->join('users v', 'v.id = u.verifikator_id', 'left');
        $this->db->join('users p', 'p.id = u.penetap_id', 'left');

        // PENEGAKAN RLS: Wajib filter id_skpd
        $this->db->where('u.id_skpd', (int) $skpd_id);

        if (!empty($filter['tipe'])) {
            $this->db->where('u.tipe', $filter['tipe']);
        }
        if (!empty($filter['status_proses'])) {
            $this->db->where('u.status_proses', $filter['status_proses']);
        }
        if (!empty($filter['kategori'])) {
            $this->db->where('u.kategori', $filter['kategori']);
        }
        if (!empty($filter['tahun'])) {
            $this->db->where('u.tahun_anggaran', (int) $filter['tahun']);
        }
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.kode_usulan', $q)
                ->or_like('u.uraian', $q)
                ->or_like('u.spesifikasi', $q)
                ->or_like('u.kategori', $q)
                ->group_end();
        }

        return $this->db->order_by('u.id', 'DESC')->get()->result();
    }

    /**
     * RLS Policy untuk Verifikator:
     * Dapat melihat data dari semua SKPD yang berstatus 'Diajukan'
     * (atau filter status lainnya untuk melihat riwayat verifikasi).
     */
    public function getUsulanVerifikasi($filter = [])
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, usr.nama_lengkap as nama_pengusul, v.nama_lengkap as nama_verifikator');
        $this->db->from($this->table . ' u');
        $this->db->join('skpd s', 's.id = u.id_skpd', 'left');
        $this->db->join('users usr', 'usr.id = u.user_id', 'left');
        $this->db->join('users v', 'v.id = u.verifikator_id', 'left');

        // Default hanya usulan yang 'Diajukan' untuk antrean kerja verifikator
        if (isset($filter['status_proses']) && $filter['status_proses'] !== '') {
            $this->db->where('u.status_proses', $filter['status_proses']);
        } else {
            $this->db->where('u.status_proses', 'Diajukan');
        }

        if (!empty($filter['tipe'])) {
            $this->db->where('u.tipe', $filter['tipe']);
        }
        if (!empty($filter['id_skpd'])) {
            $this->db->where('u.id_skpd', (int) $filter['id_skpd']);
        }
        if (!empty($filter['kategori'])) {
            $this->db->where('u.kategori', $filter['kategori']);
        }
        if (!empty($filter['tahun'])) {
            $this->db->where('u.tahun_anggaran', (int) $filter['tahun']);
        }
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.kode_usulan', $q)
                ->or_like('u.uraian', $q)
                ->or_like('u.spesifikasi', $q)
                ->or_like('s.nama_skpd', $q)
                ->group_end();
        }

        return $this->db->order_by('u.updated_at', 'ASC')->get()->result();
    }

    /**
     * RLS Policy untuk Penetap Harga:
     * Menampilkan data dari semua SKPD dengan status 'Diverifikasi'
     * untuk disahkan menjadi 'Ditetapkan'.
     */
    public function getUsulanPenetapan($filter = [])
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, usr.nama_lengkap as nama_pengusul, v.nama_lengkap as nama_verifikator');
        $this->db->from($this->table . ' u');
        $this->db->join('skpd s', 's.id = u.id_skpd', 'left');
        $this->db->join('users usr', 'usr.id = u.user_id', 'left');
        $this->db->join('users v', 'v.id = u.verifikator_id', 'left');

        // Penetap hanya memproses data 'Diverifikasi'
        if (isset($filter['status_proses']) && $filter['status_proses'] !== '') {
            $this->db->where('u.status_proses', $filter['status_proses']);
        } else {
            $this->db->where('u.status_proses', 'Diverifikasi');
        }

        if (!empty($filter['tipe'])) {
            $this->db->where('u.tipe', $filter['tipe']);
        }
        if (!empty($filter['id_skpd'])) {
            $this->db->where('u.id_skpd', (int) $filter['id_skpd']);
        }
        if (!empty($filter['kategori'])) {
            $this->db->where('u.kategori', $filter['kategori']);
        }
        if (!empty($filter['tahun'])) {
            $this->db->where('u.tahun_anggaran', (int) $filter['tahun']);
        }
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.kode_usulan', $q)
                ->or_like('u.uraian', $q)
                ->or_like('u.spesifikasi', $q)
                ->or_like('s.nama_skpd', $q)
                ->group_end();
        }

        return $this->db->order_by('u.tgl_verifikasi', 'ASC')->get()->result();
    }

    /**
     * Master Data Katalog Resmi Standar Satuan Harga (SSH) & SBU
     * Mengambil dari tabel ref_standar_harga (memuat 11.519 data resmi 2027 dan usulan yang telah ditetapkan).
     */
    public function countMasterData($filter = [])
    {
        $this->db->from($this->table_master);
        if (!empty($filter['tipe'])) {
            $this->db->where('tipe', $filter['tipe']);
        }
        if (!empty($filter['tahun'])) {
            $this->db->where('tahun_anggaran', (int)$filter['tahun']);
        }
        if (!empty($filter['kategori'])) {
            $this->db->where('kategori', $filter['kategori']);
        }
        if (!empty($filter['q'])) {
            $q = trim($filter['q']);
            $this->db->group_start()
                ->like('uraian', $q)
                ->or_like('spesifikasi', $q)
                ->or_like('kode_standar', $q)
                ->or_like('kode_kelompok', $q)
                ->or_like('kode_rekening', $q)
                ->or_like('nama_rekening', $q)
                ->group_end();
        }
        return (int) $this->db->count_all_results();
    }

    public function getMasterDataPaginated($filter = [], $limit = 25, $offset = 0)
    {
        $this->db->from($this->table_master);
        if (!empty($filter['tipe'])) {
            $this->db->where('tipe', $filter['tipe']);
        }
        if (!empty($filter['tahun'])) {
            $this->db->where('tahun_anggaran', (int)$filter['tahun']);
        }
        if (!empty($filter['kategori'])) {
            $this->db->where('kategori', $filter['kategori']);
        }
        if (!empty($filter['q'])) {
            $q = trim($filter['q']);
            $this->db->group_start()
                ->like('uraian', $q)
                ->or_like('spesifikasi', $q)
                ->or_like('kode_standar', $q)
                ->or_like('kode_kelompok', $q)
                ->or_like('kode_rekening', $q)
                ->or_like('nama_rekening', $q)
                ->group_end();
        }
        return $this->db->order_by('id', 'ASC')
            ->limit((int)$limit, (int)$offset)
            ->get()->result();
    }

    public function getMasterData($filter = [])
    {
        return $this->getMasterDataPaginated($filter, 100, 0);
    }

    public function getMasterById($id)
    {
        return $this->db->get_where($this->table_master, ['id' => (int)$id])->row();
    }

    public function searchMasterAjax($tipe, $q, $tahun = 2027, $limit = 30)
    {
        $this->db->from($this->table_master);
        if ($tipe) {
            $this->db->where('tipe', $tipe);
        }
        if ($tahun) {
            $this->db->where('tahun_anggaran', (int)$tahun);
        }
        if (!empty($q)) {
            $this->db->group_start()
                ->like('uraian', $q)
                ->or_like('spesifikasi', $q)
                ->or_like('kode_standar', $q)
                ->or_like('kode_kelompok', $q)
                ->or_like('kode_rekening', $q)
                ->group_end();
        }
        return $this->db->order_by('uraian', 'ASC')
            ->limit((int)$limit)
            ->get()->result();
    }

    public function getDistinctKategoriMaster($tipe, $tahun = NULL)
    {
        $this->db->distinct()->select('kategori');
        $this->db->from($this->table_master);
        if ($tipe) $this->db->where('tipe', $tipe);
        if ($tahun) $this->db->where('tahun_anggaran', (int)$tahun);
        $this->db->where('kategori IS NOT NULL', NULL, FALSE);
        $res = $this->db->order_by('kategori', 'ASC')->get()->result();
        return array_column($res, 'kategori');
    }

    public function getDistinctTahunMaster($tipe = NULL)
    {
        $this->db->distinct()->select('tahun_anggaran');
        $this->db->from($this->table_master);
        if ($tipe) $this->db->where('tipe', $tipe);
        $res = $this->db->order_by('tahun_anggaran', 'DESC')->get()->result();
        return array_column($res, 'tahun_anggaran');
    }

    /**
     * Ambil detail usulan dengan validasi RLS individual.
     */
    public function findWithRls($id, $user)
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, usr.nama_lengkap as nama_pengusul, v.nama_lengkap as nama_verifikator, p.nama_lengkap as nama_penetap');
        $this->db->from($this->table . ' u');
        $this->db->join('skpd s', 's.id = u.id_skpd', 'left');
        $this->db->join('users usr', 'usr.id = u.user_id', 'left');
        $this->db->join('users v', 'v.id = u.verifikator_id', 'left');
        $this->db->join('users p', 'p.id = u.penetap_id', 'left');
        $this->db->where('u.id', (int) $id);

        $row = $this->db->get()->row();
        if (!$row) return NULL;

        // Pengecekan RLS untuk Operator SKPD:
        // Jika bukan admin/verifikator/penetap, operator HANYA boleh mengakses data miliknya sendiri
        // KECUALI jika data tersebut sudah Ditetapkan (masuk domain publik master data).
        $isOperator = in_array($user->role, ['operator_skpd', 'skpd'], TRUE);
        if ($isOperator && $row->status_proses !== 'Ditetapkan') {
            if ((int)$row->id_skpd !== (int)$user->skpd_id) {
                return FALSE; // Ditolak oleh RLS
            }
        }

        return $row;
    }

    // =========================================================================
    // 2. OPERASI MUTASI DATA DENGAN PENEGAKAN RLS (INSERT, UPDATE, DELETE)
    // =========================================================================

    /**
     * RLS Policy: Insert Usulan baru oleh operator_skpd
     * - id_skpd wajib diikat dengan SKPD milik user yang sedang login.
     * - Status awal selalu 'Draft'.
     */
    public function insertUsulan($data, $user)
    {
        $tahun = !empty($data['tahun_anggaran']) ? (int)$data['tahun_anggaran'] : (int)date('Y');
        $tipe = in_array($data['tipe'], ['SSH', 'SBU'], TRUE) ? $data['tipe'] : 'SSH';

        // Auto generate kode usulan: misal SSH-2026-0001
        $kodeUsulan = $this->generateKodeUsulan($tipe, $tahun);

        $insertData = [
            'master_standar_id' => !empty($data['master_standar_id']) ? (int)$data['master_standar_id'] : NULL,
            'kode_usulan'       => $kodeUsulan,
            'tipe'              => $tipe,
            'kategori'          => trim($data['kategori']),
            'kode_kelompok'     => trim($data['kode_kelompok'] ?? ''),
            'uraian'            => trim($data['uraian']),
            'spesifikasi'       => trim($data['spesifikasi']),
            'satuan'            => trim($data['satuan']),
            'kode_rekening'     => trim($data['kode_rekening'] ?? ''),
            'nama_rekening'     => trim($data['nama_rekening'] ?? ''),
            'harga_usulan'      => (float) $data['harga_usulan'],
            'harga_acuan_master'=> !empty($data['harga_acuan_master']) ? (float)$data['harga_acuan_master'] : NULL,
            'harga_ditetapkan'  => NULL,
            'file_lampiran'     => $data['file_lampiran'] ?? NULL,
            'file_nama_asli'    => $data['file_nama_asli'] ?? NULL,
            'file_lampiran_2'   => $data['file_lampiran_2'] ?? NULL,
            'file_nama_asli_2'  => $data['file_nama_asli_2'] ?? NULL,
            'file_lampiran_3'   => $data['file_lampiran_3'] ?? NULL,
            'file_nama_asli_3'  => $data['file_nama_asli_3'] ?? NULL,
            'id_skpd'           => (int) $user->skpd_id, // RLS Bound
            'user_id'           => (int) $user->id,
            'status_proses'     => 'Draft', // RLS Initial Status Rule
            'tahun_anggaran'    => $tahun,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        $this->db->insert($this->table, $insertData);
        $insertId = (int) $this->db->insert_id();

        // Audit Trail
        $this->logActivity($insertId, $user, NULL, 'Draft', 'Membuat draft usulan baru: ' . $kodeUsulan);

        return ['success' => TRUE, 'id' => $insertId, 'kode_usulan' => $kodeUsulan];
    }

    /**
     * RLS Policy: Update Usulan oleh operator_skpd
     * Aturan:
     * 1. id_skpd HARUS cocok dengan session user.
     * 2. status_proses HANYA bernilai 'Draft' atau 'Direvisi'.
     * Jika melanggar, operasi ditolak dengan pesan kesalahan keamanan.
     */
    public function updateUsulan($id, $data, $user)
    {
        $existing = $this->db->get_where($this->table, ['id' => (int) $id])->row();
        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Data usulan tidak ditemukan.'];
        }

        // PENEGAKAN RLS 1: Cek kepemilikan SKPD
        if ($user->role !== 'admin' && (int)$existing->id_skpd !== (int)$user->skpd_id) {
            return ['success' => FALSE, 'message' => 'Pelanggaran RLS: Anda tidak berhak mengubah usulan dari SKPD lain!'];
        }

        // PENEGAKAN RLS 2: Cek status usulan (Hanya Draft atau Direvisi)
        if (!in_array($existing->status_proses, ['Draft', 'Direvisi'], TRUE)) {
            return [
                'success' => FALSE, 
                'message' => "Pelanggaran RLS: Data berstatus '{$existing->status_proses}' sudah terkunci dan tidak dapat diubah lagi."
            ];
        }

        $updateData = [
            'master_standar_id' => !empty($data['master_standar_id']) ? (int)$data['master_standar_id'] : $existing->master_standar_id,
            'tipe'              => in_array($data['tipe'], ['SSH', 'SBU'], TRUE) ? $data['tipe'] : $existing->tipe,
            'kategori'          => trim($data['kategori']),
            'kode_kelompok'     => trim($data['kode_kelompok'] ?? $existing->kode_kelompok),
            'uraian'            => trim($data['uraian']),
            'spesifikasi'       => trim($data['spesifikasi']),
            'satuan'            => trim($data['satuan']),
            'kode_rekening'     => trim($data['kode_rekening'] ?? $existing->kode_rekening),
            'nama_rekening'     => trim($data['nama_rekening'] ?? $existing->nama_rekening),
            'harga_usulan'      => (float) $data['harga_usulan'],
            'harga_acuan_master'=> !empty($data['harga_acuan_master']) ? (float)$data['harga_acuan_master'] : $existing->harga_acuan_master,
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        if (!empty($data['file_lampiran'])) {
            $updateData['file_lampiran']  = $data['file_lampiran'];
            $updateData['file_nama_asli'] = $data['file_nama_asli'] ?? $data['file_lampiran'];
        }
        if (!empty($data['file_lampiran_2'])) {
            $updateData['file_lampiran_2']  = $data['file_lampiran_2'];
            $updateData['file_nama_asli_2'] = $data['file_nama_asli_2'] ?? $data['file_lampiran_2'];
        }
        if (!empty($data['file_lampiran_3'])) {
            $updateData['file_lampiran_3']  = $data['file_lampiran_3'];
            $updateData['file_nama_asli_3'] = $data['file_nama_asli_3'] ?? $data['file_lampiran_3'];
        }

        $this->db->where('id', (int) $id)->update($this->table, $updateData);

        // Audit Trail
        $this->logActivity($id, $user, $existing->status_proses, $existing->status_proses, 'Memperbarui data usulan.');

        return ['success' => TRUE, 'message' => 'Data usulan berhasil diperbarui.'];
    }

    /**
     * RLS Policy: Kirim Usulan oleh operator_skpd (Draft / Direvisi -> Diajukan)
     */
    public function kirimUsulan($id, $user)
    {
        $existing = $this->db->get_where($this->table, ['id' => (int) $id])->row();
        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Data usulan tidak ditemukan.'];
        }

        // RLS Guard: Milik SKPD sendiri
        if ($user->role !== 'admin' && (int)$existing->id_skpd !== (int)$user->skpd_id) {
            return ['success' => FALSE, 'message' => 'Pelanggaran RLS: Anda tidak dapat mengirim usulan SKPD lain.'];
        }

        // RLS Guard: Status harus Draft atau Direvisi
        if (!in_array($existing->status_proses, ['Draft', 'Direvisi'], TRUE)) {
            return [
                'success' => FALSE, 
                'message' => "Usulan tidak dapat dikirim karena berstatus '{$existing->status_proses}'."
            ];
        }

        $statusSebelum = $existing->status_proses;
        $this->db->where('id', (int) $id)->update($this->table, [
            'status_proses' => 'Diajukan',
            'updated_at'    => date('Y-m-d H:i:s')
        ]);

        $this->logActivity($id, $user, $statusSebelum, 'Diajukan', 'Usulan diajukan ke BPKAD untuk verifikasi.');

        return ['success' => TRUE, 'message' => 'Usulan berhasil dikirim ke BPKAD untuk diverifikasi.'];
    }

    /**
     * RLS Policy untuk Verifikator:
     * Mengubah kolom status_proses, catatan_verifikator, serta harga_ditetapkan
     * HANYA ketika status data adalah 'Diajukan'.
     *
     * Aksi:
     * - 'setujui' => status_proses diubah menjadi 'Diverifikasi'
     * - 'revisi'  => status_proses diubah menjadi 'Direvisi' (wajib catatan)
     */
    public function verifikasiUsulan($id, $action, $catatan, $hargaDitetapkan, $user)
    {
        // RLS Guard: Role verifikator atau admin
        if (!in_array($user->role, ['verifikator', 'admin'], TRUE)) {
            return ['success' => FALSE, 'message' => 'Pelanggaran RLS: Hanya verifikator yang berhak melakukan verifikasi usulan.'];
        }

        $existing = $this->db->get_where($this->table, ['id' => (int) $id])->row();
        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Data usulan tidak ditemukan.'];
        }

        // PENEGAKAN RLS KUNCI: Hanya boleh update jika status adalah 'Diajukan'
        if ($existing->status_proses !== 'Diajukan') {
            return [
                'success' => FALSE, 
                'message' => "Pelanggaran RLS: Data hanya dapat diverifikasi jika berstatus 'Diajukan'. Status saat ini: '{$existing->status_proses}'."
            ];
        }

        $statusSebelum = $existing->status_proses;
        $now = date('Y-m-d H:i:s');

        if ($action === 'setujui') {
            // Penetapan harga oleh verifikator (bisa disesuaikan atau sama dengan usulan jika tidak diisi)
            $hargaFinal = ($hargaDitetapkan !== NULL && $hargaDitetapkan > 0) ? (float)$hargaDitetapkan : (float)$existing->harga_usulan;

            $updateData = [
                'status_proses'       => 'Diverifikasi',
                'harga_ditetapkan'    => $hargaFinal,
                'catatan_verifikator' => !empty($catatan) ? trim($catatan) : 'Usulan diverifikasi dan disetujui untuk penetapan.',
                'verifikator_id'      => (int) $user->id,
                'tgl_verifikasi'      => $now,
                'updated_at'          => $now
            ];

            $this->db->where('id', (int) $id)->update($this->table, $updateData);
            $this->logActivity($id, $user, $statusSebelum, 'Diverifikasi', 'Disetujui oleh verifikator. Catatan: ' . $updateData['catatan_verifikator']);

            return ['success' => TRUE, 'message' => 'Usulan berhasil disetujui dan berstatus Diverifikasi.'];

        } elseif ($action === 'revisi') {
            if (empty(trim($catatan))) {
                return ['success' => FALSE, 'message' => 'Catatan verifikator wajib diisi untuk usulan yang direvisi.'];
            }

            $updateData = [
                'status_proses'       => 'Direvisi',
                'catatan_verifikator' => trim($catatan),
                'verifikator_id'      => (int) $user->id,
                'tgl_verifikasi'      => $now,
                'updated_at'          => $now
            ];

            $this->db->where('id', (int) $id)->update($this->table, $updateData);
            $this->logActivity($id, $user, $statusSebelum, 'Direvisi', 'Dikembalikan ke SKPD untuk direvisi. Catatan: ' . $catatan);

            return ['success' => TRUE, 'message' => 'Usulan dikembalikan ke SKPD untuk dilakukan perbaikan/revisi.'];
        }

        return ['success' => FALSE, 'message' => 'Aksi verifikasi tidak valid.'];
    }

    /**
     * RLS Policy untuk Penetap:
     * Mengubah kolom status_proses menjadi 'Ditetapkan'
     * HANYA ketika status data adalah 'Diverifikasi'.
     * Setelah ditetapkan, data baris tersebut terkunci.
     */
    public function tetapkanUsulan($id, $user)
    {
        // RLS Guard: Role penetap, pimpinan, atau admin
        if (!in_array($user->role, ['penetap', 'pimpinan', 'admin'], TRUE)) {
            return ['success' => FALSE, 'message' => 'Pelanggaran RLS: Hanya pejabat penetap yang berhak menetapkan standar harga.'];
        }

        $existing = $this->db->get_where($this->table, ['id' => (int) $id])->row();
        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Data usulan tidak ditemukan.'];
        }

        // PENEGAKAN RLS KUNCI: Hanya boleh update jika status adalah 'Diverifikasi'
        if ($existing->status_proses !== 'Diverifikasi') {
            return [
                'success' => FALSE, 
                'message' => "Pelanggaran RLS: Usulan hanya dapat ditetapkan jika sudah berstatus 'Diverifikasi'. Status saat ini: '{$existing->status_proses}'."
            ];
        }

        $statusSebelum = $existing->status_proses;
        $now = date('Y-m-d H:i:s');

        // Pastikan harga_ditetapkan memiliki nilai pasti
        $hargaFinal = ($existing->harga_ditetapkan && $existing->harga_ditetapkan > 0) 
            ? $existing->harga_ditetapkan 
            : $existing->harga_usulan;

        $updateData = [
            'status_proses'    => 'Ditetapkan',
            'harga_ditetapkan' => $hargaFinal,
            'penetap_id'       => (int) $user->id,
            'tgl_penetapan'    => $now,
            'updated_at'       => $now
        ];

        $this->db->where('id', (int) $id)->update($this->table, $updateData);

        // Sinkronkan ke master data resmi (ref_standar_harga)
        $this->syncToMasterData($existing, $hargaFinal);

        // Audit Trail
        $this->logActivity($id, $user, $statusSebelum, 'Ditetapkan', 'Standar harga resmi ditetapkan dan disinkronkan ke Master Data.');

        return ['success' => TRUE, 'message' => "Item '{$existing->uraian}' berhasil ditetapkan dan dikunci sebagai Master Data resmi."];
    }

    /**
     * Hapus usulan (Hanya untuk Draft dan milik SKPD sendiri)
     */
    public function deleteUsulan($id, $user)
    {
        $existing = $this->db->get_where($this->table, ['id' => (int) $id])->row();
        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Data tidak ditemukan.'];
        }

        if ($user->role !== 'admin' && (int)$existing->id_skpd !== (int)$user->skpd_id) {
            return ['success' => FALSE, 'message' => 'Pelanggaran RLS: Anda tidak dapat menghapus data SKPD lain.'];
        }

        if ($existing->status_proses !== 'Draft') {
            return ['success' => FALSE, 'message' => 'Hanya data dengan status Draft yang dapat dihapus.'];
        }

        // Hapus file fisik jika ada (Survey 1, 2, 3)
        foreach (['file_lampiran', 'file_lampiran_2', 'file_lampiran_3'] as $fCol) {
            if (!empty($existing->$fCol)) {
                $filePath = FCPATH . 'uploads/ssh_sbu/' . $existing->$fCol;
                if (file_exists($filePath)) @unlink($filePath);
            }
        }

        $this->db->where('id', (int) $id)->delete($this->table);

        return ['success' => TRUE, 'message' => 'Data usulan berhasil dihapus.'];
    }

    // =========================================================================
    // 3. LOG AKTIVITAS & AUDIT TRAIL
    // =========================================================================

    public function logActivity($usulanId, $user, $statusSebelum, $statusSesudah, $catatan = '')
    {
        $logData = [
            'usulan_id'      => (int) $usulanId,
            'user_id'        => (int) $user->id,
            'role'           => $user->role,
            'status_sebelum' => $statusSebelum,
            'status_sesudah' => $statusSesudah,
            'catatan'        => $catatan,
            'created_at'     => date('Y-m-d H:i:s')
        ];
        return $this->db->insert($this->table_log, $logData);
    }

    public function getLogs($usulanId)
    {
        $this->db->select('l.*, u.nama_lengkap, u.username');
        $this->db->from($this->table_log . ' l');
        $this->db->join('users u', 'u.id = l.user_id', 'left');
        $this->db->where('l.usulan_id', (int) $usulanId);
        $this->db->order_by('l.id', 'ASC');
        return $this->db->get()->result();
    }

    // =========================================================================
    // 4. HELPER STATISTIK & KATEGORI
    // =========================================================================

    public function getKategoriList($tipe = NULL)
    {
        $tipe = strtoupper($tipe ?: 'SSH');
        $masterKat = $this->getDistinctKategoriMaster($tipe);
        $extraKat  = ($tipe === 'SSH') ? $this->getKategoriSsh() : $this->getKategoriSbu();
        $merged    = array_unique(array_merge($masterKat, $extraKat));

        if (($key = array_search('Lainnya', $merged)) !== false) {
            unset($merged[$key]);
            $merged[] = 'Lainnya';
        }

        return array_values($merged);
    }

    public function getKategoriSsh()
    {
        return [
            'Bahan & Persediaan Habis Pakai',
            'Peralatan dan Mesin',
            'Gedung dan Bangunan',
            'Jalan, Irigasi dan Jaringan',
            'Aset Tetap Lainnya',
            'Tanah',
            'Sewa, Jasa & Biaya Operasional',
            'Alat Tulis Kantor (ATK) & Kertas',
            'Peralatan Komputer & Elektronik',
            'Bahan Bangunan & Material Konstruksi',
            'Kendaraan & Alat Angkutan',
            'Alat Kedokteran & Kesehatan',
            'Obat-obatan & Perbekalan Medis',
            'Alat Laboratorium & Penelitian',
            'Buku & Bahan Pustaka',
            'Peralatan Listrik & Mekanikal',
            'Perlengkapan Rumah Tangga & Kantor',
            'Bibit, Pupuk & Perlengkapan Pertanian',
            'Lainnya'
        ];
    }

    public function getKategoriSbu()
    {
        return [
            'Honorarium & Jasa Tenaga Ahli',
            'Sewa, Jasa & Biaya Operasional',
            'Bahan & Persediaan Habis Pakai',
            'Peralatan dan Mesin',
            'Gedung dan Bangunan',
            'Honorarium Narasumber / Pakar / Praktisi',
            'Honorarium Panitia Pelaksana / Tim Kerja',
            'Honorarium Rohaniwan / Pembaca Doa',
            'Jasa Tenaga Ahli / Konsultansi',
            'Jasa Tenaga Non-ASN / Kebersihan / Keamanan',
            'Biaya Perjalanan Dinas (Uang Harian / Uang Saku)',
            'Biaya Transportasi & Akomodasi',
            'Sewa Gedung / Ruang Pertemuan',
            'Sewa Kendaraan Operasional',
            'Konsumsi Rapat & Jamuan Acara',
            'Publikasi, Dokumentasi & Sosialisasi',
            'Lainnya'
        ];
    }

    public function getSatuanList($tipe = NULL)
    {
        if ($tipe === 'SSH') return $this->getSatuanSsh();
        if ($tipe === 'SBU') return $this->getSatuanSbu();
        return array_merge($this->getSatuanSsh(), $this->getSatuanSbu());
    }

    public function getSatuanSsh()
    {
        return [
            'Unit', 'Buah', 'Rim', 'Dus', 'Kotak', 'Paket', 'Set',
            'Lembar', 'Rol', 'Meter', 'M2', 'M3', 'Kg', 'Ton', 'Liter', 'Sak', 'Batang', 'Kaleng', 'Botol'
        ];
    }

    public function getSatuanSbu()
    {
        return [
            'Orang/Bulan (OB)', 'Orang/Hari (OH)', 'Orang/Jam (OJ)', 'Orang/Tahun (OT)',
            'Orang/Kegiatan (OK)', 'Orang/Paket (OP)', 'Jam', 'Hari', 'Bulan', 'Tahun', 'Kegiatan', 'Kali', 'Titik', 'Hari/Orang'
        ];
    }

    public function generateKodeUsulan($tipe = 'SSH', $tahun = 2026)
    {
        $prefix = "{$tipe}-{$tahun}-";
        $this->db->like('kode_usulan', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row();

        $nextNum = 1;
        if ($last) {
            $parts = explode('-', $last->kode_usulan);
            $lastNum = (int) end($parts);
            $nextNum = $lastNum + 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function getSummaryCounts($user, $tipe = NULL)
    {
        $role = $user->role;
        $skpdId = (int) $user->skpd_id;

        $this->db->select("
            COUNT(CASE WHEN status_proses = 'Draft' THEN 1 END) as draft,
            COUNT(CASE WHEN status_proses = 'Diajukan' THEN 1 END) as diajukan,
            COUNT(CASE WHEN status_proses = 'Direvisi' THEN 1 END) as direvisi,
            COUNT(CASE WHEN status_proses = 'Diverifikasi' THEN 1 END) as diverifikasi,
            COUNT(CASE WHEN status_proses = 'Ditetapkan' THEN 1 END) as ditetapkan,
            COUNT(*) as total
        ");

        if (in_array($role, ['operator_skpd', 'skpd'], TRUE)) {
            $this->db->where('id_skpd', $skpdId);
        }

        if (!empty($tipe)) {
            $this->db->where('tipe', $tipe);
        }

        return $this->db->get($this->table)->row();
    }

    /**
     * Sinkronisasi data usulan yang telah DITETAPKAN ke tabel ref_standar_harga.
     */
    public function syncToMasterData($usulan, $hargaFinal)
    {
        $now = date('Y-m-d H:i:s');
        if (!empty($usulan->master_standar_id)) {
            // Update item yang ada di katalog master
            $this->db->where('id', (int)$usulan->master_standar_id)->update($this->table_master, [
                'harga_satuan' => (float)$hargaFinal,
                'updated_at'   => $now
            ]);
        } else {
            // Item baru ditambahkan ke katalog master untuk tahun anggaran berkenaan
            $kodeStandar = $this->generateKodeStandarMaster($usulan->tipe, $usulan->tahun_anggaran);
            $this->db->insert($this->table_master, [
                'tipe'           => in_array($usulan->tipe, ['SSH', 'SBU']) ? $usulan->tipe : 'SSH',
                'tahun_anggaran' => (int)$usulan->tahun_anggaran,
                'kode_kelompok'  => !empty($usulan->kode_kelompok) ? $usulan->kode_kelompok : ($usulan->tipe === 'SSH' ? '1.1.12.01.01.0001' : '8.1.02.01.01.0001'),
                'kode_standar'   => $kodeStandar,
                'uraian'         => $usulan->uraian,
                'spesifikasi'    => $usulan->spesifikasi,
                'satuan'         => $usulan->satuan,
                'harga_satuan'   => (float)$hargaFinal,
                'kode_rekening'  => !empty($usulan->kode_rekening) ? $usulan->kode_rekening : NULL,
                'nama_rekening'  => !empty($usulan->nama_rekening) ? $usulan->nama_rekening : NULL,
                'kategori'       => !empty($usulan->kategori) ? $usulan->kategori : 'Umum',
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now
            ]);
            $newMasterId = $this->db->insert_id();
            // Tautkan kembali ke usulan
            $this->db->where('id', (int)$usulan->id)->update($this->table, [
                'master_standar_id' => $newMasterId
            ]);
        }
    }

    public function generateKodeStandarMaster($tipe = 'SSH', $tahun = 2027)
    {
        $prefix = "{$tipe}-{$tahun}-";
        $this->db->like('kode_standar', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table_master)->row();

        $nextNum = 1;
        if ($last) {
            $parts = explode('-', $last->kode_standar);
            $lastNum = (int) end($parts);
            $nextNum = $lastNum + 1;
        }

        return $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // JADWAL PENGUSULAN STANDAR HARGA (SSH & SBU)
    // =========================================================================

    public function getJadwalList($filter = [])
    {
        $this->db->from($this->table_jadwal);
        if (!empty($filter['tipe']) && $filter['tipe'] !== 'SEMUA') {
            $this->db->group_start()
                ->where('tipe', $filter['tipe'])
                ->or_where('tipe', 'SEMUA')
                ->group_end();
        }
        if (!empty($filter['tahun'])) {
            $this->db->where('tahun_anggaran', (int)$filter['tahun']);
        }
        if (!empty($filter['status'])) {
            $this->db->where('status', $filter['status']);
        }
        return $this->db->order_by('tahun_anggaran', 'DESC')
            ->order_by('tanggal_mulai', 'DESC')
            ->get()->result();
    }

    public function getJadwalById($id)
    {
        return $this->db->get_where($this->table_jadwal, ['id' => (int)$id])->row();
    }

    /**
     * Cek apakah ada jadwal pengusulan yang sedang dibuka/aktif.
     */
    public function getJadwalAktif($tipe = NULL, $tahun = NULL)
    {
        $today = date('Y-m-d');
        $this->db->from($this->table_jadwal);
        $this->db->where('status', 'buka');
        $this->db->where('tanggal_mulai <=', $today);
        $this->db->where('tanggal_selesai >=', $today);

        if ($tipe) {
            $this->db->group_start()
                ->where('tipe', $tipe)
                ->or_where('tipe', 'SEMUA')
                ->group_end();
        }
        if ($tahun) {
            $this->db->where('tahun_anggaran', (int)$tahun);
        }

        return $this->db->order_by('tahun_anggaran', 'DESC')->get()->row();
    }

    public function isJadwalBuka($tipe = 'SSH', $tahun = NULL)
    {
        $jadwal = $this->getJadwalAktif($tipe, $tahun);
        return !empty($jadwal);
    }

    public function saveJadwal($data, $id = NULL, $userId = NULL)
    {
        $payload = [
            'tipe'            => in_array($data['tipe'] ?? '', ['SSH', 'SBU', 'SEMUA']) ? $data['tipe'] : 'SEMUA',
            'tahun_anggaran'  => (int) ($data['tahun_anggaran'] ?? date('Y') + 1),
            'nama_jadwal'     => trim($data['nama_jadwal'] ?? ''),
            'tanggal_mulai'   => $data['tanggal_mulai'] ?? date('Y-m-d'),
            'tanggal_selesai' => $data['tanggal_selesai'] ?? date('Y-12-31'),
            'status'          => in_array($data['status'] ?? '', ['buka', 'tutup']) ? $data['status'] : 'buka',
            'keterangan'      => trim($data['keterangan'] ?? '')
        ];

        if ($id) {
            $this->db->where('id', (int)$id)->update($this->table_jadwal, $payload);
            return ['success' => TRUE, 'message' => 'Jadwal pengusulan berhasil diperbarui.'];
        } else {
            $payload['created_by'] = $userId ? (int)$userId : NULL;
            $this->db->insert($this->table_jadwal, $payload);
            return ['success' => TRUE, 'message' => 'Jadwal pengusulan baru berhasil dibuat.'];
        }
    }

    public function toggleJadwalStatus($id)
    {
        $row = $this->getJadwalById($id);
        if (!$row) return FALSE;
        $newStatus = ($row->status === 'buka') ? 'tutup' : 'buka';
        $this->db->where('id', (int)$id)->update($this->table_jadwal, ['status' => $newStatus]);
        return $newStatus;
    }

    public function deleteJadwal($id)
    {
        return $this->db->delete($this->table_jadwal, ['id' => (int)$id]);
    }
}
