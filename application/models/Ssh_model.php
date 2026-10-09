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
     * RLS Policy untuk Master Data (Read-Only untuk SEMUA Role):
     * Menampilkan semua data yang sudah berstatus 'Ditetapkan'.
     * Dilengkapi fitur pencarian (search) dan penyaringan (filter) berdasarkan Kategori dan SKPD.
     */
    public function getMasterData($filter = [])
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, v.nama_lengkap as nama_verifikator, p.nama_lengkap as nama_penetap');
        $this->db->from($this->table . ' u');
        $this->db->join('skpd s', 's.id = u.id_skpd', 'left');
        $this->db->join('users v', 'v.id = u.verifikator_id', 'left');
        $this->db->join('users p', 'p.id = u.penetap_id', 'left');

        // Master data hanya menampilkan yang telah DITETAPKAN
        $this->db->where('u.status_proses', 'Ditetapkan');

        // Filter Kategori
        if (!empty($filter['kategori'])) {
            $this->db->where('u.kategori', $filter['kategori']);
        }

        // Filter SKPD
        if (!empty($filter['id_skpd'])) {
            $this->db->where('u.id_skpd', (int) $filter['id_skpd']);
        }

        // Filter Tipe (SSH/SBU)
        if (!empty($filter['tipe'])) {
            $this->db->where('u.tipe', $filter['tipe']);
        }

        // Filter Tahun Anggaran
        if (!empty($filter['tahun'])) {
            $this->db->where('u.tahun_anggaran', (int) $filter['tahun']);
        }

        // Pencarian (Search)
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.kode_usulan', $q)
                ->or_like('u.uraian', $q)
                ->or_like('u.spesifikasi', $q)
                ->or_like('u.kategori', $q)
                ->or_like('s.nama_skpd', $q)
                ->group_end();
        }

        return $this->db->order_by('u.uraian', 'ASC')->get()->result();
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
            'kode_usulan'     => $kodeUsulan,
            'tipe'            => $tipe,
            'kategori'        => trim($data['kategori']),
            'uraian'          => trim($data['uraian']),
            'spesifikasi'     => trim($data['spesifikasi']),
            'satuan'          => trim($data['satuan']),
            'harga_usulan'    => (float) $data['harga_usulan'],
            'harga_ditetapkan'=> NULL,
            'file_lampiran'   => $data['file_lampiran'] ?? NULL,
            'file_nama_asli'  => $data['file_nama_asli'] ?? NULL,
            'id_skpd'         => (int) $user->skpd_id, // RLS Bound
            'user_id'         => (int) $user->id,
            'status_proses'   => 'Draft', // RLS Initial Status Rule
            'tahun_anggaran'  => $tahun,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
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
            'tipe'         => in_array($data['tipe'], ['SSH', 'SBU'], TRUE) ? $data['tipe'] : $existing->tipe,
            'kategori'     => trim($data['kategori']),
            'uraian'       => trim($data['uraian']),
            'spesifikasi'  => trim($data['spesifikasi']),
            'satuan'       => trim($data['satuan']),
            'harga_usulan' => (float) $data['harga_usulan'],
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if (!empty($data['file_lampiran'])) {
            $updateData['file_lampiran']  = $data['file_lampiran'];
            $updateData['file_nama_asli'] = $data['file_nama_asli'] ?? $data['file_lampiran'];
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

        // Audit Trail
        $this->logActivity($id, $user, $statusSebelum, 'Ditetapkan', 'Standar harga resmi ditetapkan dan dikunci ke Master Data.');

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

        // Hapus file fisik jika ada
        if (!empty($existing->file_lampiran)) {
            $filePath = FCPATH . 'uploads/ssh_sbu/' . $existing->file_lampiran;
            if (file_exists($filePath)) @unlink($filePath);
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

    public function getKategoriList()
    {
        return [
            'Alat Tulis Kantor',
            'Bahan Bangunan & Material',
            'Peralatan Komputer & Elektronik',
            'Kendaraan & Alat Angkutan',
            'Alat Kedokteran & Kesehatan',
            'Alat Laboratorium',
            'Buku & Bahan Pustaka',
            'Honorarium & Jasa Tenaga Ahli',
            'Jasa Konsultansi',
            'Sewa Gedung & Perlengkapan',
            'Pemeliharaan Sarana & Prasarana',
            'Lainnya'
        ];
    }

    public function getSatuanList()
    {
        return [
            'Unit', 'Buah', 'Rim', 'Dus', 'Kotak', 'Paket', 'Set',
            'Meter', 'M2', 'M3', 'Kg', 'Ton', 'Liter', 'Sak',
            'Orang/Bulan', 'Orang/Hari', 'Orang/Jam', 'Kegiatan', 'Titik', 'Bulan', 'Tahun'
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

    public function getSummaryCounts($user)
    {
        $role = $user->role;
        $skpdId = (int) $user->skpd_id;

        $query = $this->db->select("
            COUNT(CASE WHEN status_proses = 'Draft' THEN 1 END) as draft,
            COUNT(CASE WHEN status_proses = 'Diajukan' THEN 1 END) as diajukan,
            COUNT(CASE WHEN status_proses = 'Direvisi' THEN 1 END) as direvisi,
            COUNT(CASE WHEN status_proses = 'Diverifikasi' THEN 1 END) as diverifikasi,
            COUNT(CASE WHEN status_proses = 'Ditetapkan' THEN 1 END) as ditetapkan,
            COUNT(*) as total
        ");

        if (in_array($role, ['operator_skpd', 'skpd'], TRUE)) {
            $query->where('id_skpd', $skpdId);
        }

        return $query->get($this->table)->row();
    }
}
