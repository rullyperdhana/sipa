<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function __construct()
    {
        parent::__construct();
        $this->ensureMenuPermissionsColumn();
        $this->ensureWaColumn();
    }

    /**
     * Memastikan kolom menu_permissions tersedia pada tabel users (safe auto-migration).
     */
    public function ensureMenuPermissionsColumn()
    {
        try {
            if (!$this->db->field_exists('menu_permissions', $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `menu_permissions` TEXT NULL DEFAULT NULL AFTER `role`");
            }
        } catch (\Throwable $e) {
            log_message('error', 'ensureMenuPermissionsColumn error: ' . $e->getMessage());
        }
    }

    /**
     * Memastikan kolom no_wa tersedia pada tabel users (safe auto-migration).
     */
    public function ensureWaColumn()
    {
        try {
            if (!$this->db->field_exists('no_wa', $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `no_wa` VARCHAR(25) NULL DEFAULT NULL AFTER `email`");
            }
        } catch (\Throwable $e) {
            log_message('error', 'ensureWaColumn error: ' . $e->getMessage());
        }
    }

    /**
     * Definisi seluruh modul / menu yang dapat diatur hak aksesnya oleh Admin.
     */
    public static function getAvailablePermissions()
    {
        return [
            'rkbmd' => [
                'label' => 'Perencanaan Aset (RKBMD)',
                'icon'  => 'bi-diagram-3-fill',
                'items' => [
                    'rkbmd_pengadaan'        => ['label' => 'RKBMD Pengadaan', 'desc' => 'Perolehan & belanja modal barang baru'],
                    'rkbmd_pemeliharaan'     => ['label' => 'RKBMD Pemeliharaan', 'desc' => 'Perawatan rutin & servis berkala aset'],
                    'rkbmd_pemanfaatan'      => ['label' => 'RKBMD Pemanfaatan', 'desc' => 'Sewa, pinjam pakai, KSP aset BMD'],
                    'rkbmd_pemindahtanganan' => ['label' => 'RKBMD Pemindahtanganan', 'desc' => 'Penjualan, hibah, tukar menukar aset'],
                    'rkbmd_penghapusan'      => ['label' => 'RKBMD Penghapusan', 'desc' => 'Pemusnahan & pembebasan aset daerah']
                ]
            ],
            'standar_harga' => [
                'label' => 'Standar Satuan Harga & Biaya Daerah',
                'icon'  => 'bi-tags-fill',
                'items' => [
                    'ssh' => ['label' => 'Standar Satuan Harga (SSH)', 'desc' => 'Usulan & master data harga barang fisik / ATK'],
                    'sbu' => ['label' => 'Standar Biaya Umum (SBU)', 'desc' => 'Usulan & master data honorarium, sewa, dan jasa']
                ]
            ],
            'laporan' => [
                'label' => 'Pusat Laporan & Rekapitulasi',
                'icon'  => 'bi-file-earmark-bar-graph-fill',
                'items' => [
                    'laporan' => ['label' => 'Pusat Laporan & Rekap Eksekutif', 'desc' => 'Akses dashboard eksekutif, rekap, cetak & ekspor Excel']
                ]
            ],
            'verifikasi' => [
                'label' => 'Verifikasi, Penetapan & Jadwal (Tim BPKAD / Pimpinan)',
                'icon'  => 'bi-shield-check',
                'items' => [
                    'verifikasi_rkbmd'   => ['label' => 'Verifikasi Usulan RKBMD', 'desc' => 'Menelaah & memverifikasi usulan RKBMD SKPD'],
                    'verifikasi_standar' => ['label' => 'Verifikasi Usulan SSH & SBU', 'desc' => 'Menelaah usulan harga satuan & biaya SKPD'],
                    'penetapan_standar'  => ['label' => 'Penetapan Usulan SSH & SBU', 'desc' => 'Pengesahan harga SSH & SBU menjadi Master Data'],
                    'jadwal_standar'     => ['label' => 'Kelola Jadwal Pengusulan', 'desc' => 'Membuka / menutup jadwal pengusulan SSH & SBU']
                ]
            ]
        ];
    }

    public function getAll($filter = [])
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd');
        $this->db->from('users u');
        $this->db->join('skpd s', 's.id = u.skpd_id', 'left');

        if (!empty($filter['role']))    $this->db->where('u.role', $filter['role']);
        if (!empty($filter['skpd_id'])) $this->db->where('u.skpd_id', $filter['skpd_id']);
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.username', $q)
                ->or_like('u.nama_lengkap', $q)
                ->or_like('u.nip', $q)
                ->group_end();
        }

        return $this->db->order_by('u.created_at', 'DESC')->get()->result();
    }

    public function find($id)
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, s.kepala_skpd, s.nip_kepala, s.jabatan_kepala, s.nama_pengurus, s.nip_pengurus');
        $this->db->from('users u');
        $this->db->join('skpd s', 's.id = u.skpd_id', 'left');
        $this->db->where('u.id', (int) $id);
        return $this->db->get()->row();
    }

    public function findByUsername($username)
    {
        return $this->db->get_where($this->table, ['username' => $username])->row();
    }

    public function create($data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        }
        if (isset($data['menu_permissions']) && is_array($data['menu_permissions'])) {
            $data['menu_permissions'] = json_encode(array_values($data['menu_permissions']));
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        } else {
            unset($data['password']);
        }
        if (isset($data['menu_permissions']) && is_array($data['menu_permissions'])) {
            $data['menu_permissions'] = json_encode(array_values($data['menu_permissions']));
        }
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

    public function changePassword($userId, $newPassword)
    {
        return $this->db->where('id', (int) $userId)
            ->update($this->table, ['password' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10])]);
    }

    public function verifyPassword($userId, $password)
    {
        $user = $this->find($userId);
        return $user && password_verify($password, $user->password);
    }

    /**
     * Mengambil nomor kontak WhatsApp operator SKPD atau nomor kontak dinas SKPD.
     */
    public function getOperatorContactBySkpd($skpdId)
    {
        if (empty($skpdId)) return null;

        // 1. Cari user aktif di SKPD ini yang memiliki nomor WA
        $user = $this->db->select('id, username, nama_lengkap, no_wa')
            ->from($this->table)
            ->where('skpd_id', (int) $skpdId)
            ->where('is_active', 1)
            ->where("no_wa IS NOT NULL AND no_wa != ''")
            ->order_by("FIELD(role, 'skpd', 'operator_skpd', 'admin'), id ASC")
            ->limit(1)
            ->get()->row();

        if ($user && !empty($user->no_wa)) {
            return [
                'nama'   => $user->nama_lengkap ?: $user->username,
                'no_wa'  => $user->no_wa,
                'source' => 'user',
                'user_id'=> (int) $user->id
            ];
        }

        // 2. Fallback ke data kontak telepon / pengurus SKPD
        $skpd = $this->db->select('id, nama_skpd, telepon, nama_pengurus')
            ->from('skpd')
            ->where('id', (int) $skpdId)
            ->limit(1)
            ->get()->row();

        if ($skpd && !empty($skpd->telepon)) {
            return [
                'nama'   => $skpd->nama_pengurus ?: ('Operator ' . $skpd->nama_skpd),
                'no_wa'  => $skpd->telepon,
                'source' => 'skpd',
                'user_id'=> null
            ];
        }

        return [
            'nama'   => $skpd ? ('Operator ' . $skpd->nama_skpd) : 'Operator SKPD',
            'no_wa'  => '',
            'source' => 'none',
            'user_id'=> null
        ];
    }
}

