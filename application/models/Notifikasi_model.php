<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifikasi_model extends CI_Model
{
    protected $table = 'notifikasi';

    /**
     * Ambil daftar notifikasi untuk user tertentu dengan formatted time & normalized link
     */
    public function getForUser($userId, $unreadOnly = FALSE, $limit = 15)
    {
        $this->db->where('user_id', (int) $userId);
        if ($unreadOnly) {
            $this->db->where('is_read', 0);
        }
        $list = $this->db->order_by('created_at', 'DESC')->limit($limit)->get($this->table)->result();

        // Format waktu dan normalkan link
        foreach ($list as &$n) {
            $n->waktu_lalu = $this->formatWaktuLalu($n->created_at);
            $n->link       = $this->normalizeLink($n->link);
        }

        return $list;
    }

    /**
     * Hitung jumlah notifikasi belum dibaca
     */
    public function countUnread($userId)
    {
        return (int) $this->db->where(['user_id' => (int) $userId, 'is_read' => 0])
            ->count_all_results($this->table);
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca
     */
    public function markAsRead($id, $userId)
    {
        return $this->db->where(['id' => (int) $id, 'user_id' => (int) $userId])
            ->update($this->table, ['is_read' => 1]);
    }

    /**
     * Tandai seluruh notifikasi user sebagai telah dibaca
     */
    public function markAllAsRead($userId)
    {
        return $this->db->where('user_id', (int) $userId)
            ->update($this->table, ['is_read' => 1]);
    }

    /**
     * Buat notifikasi baru untuk 1 user
     */
    public function create($userId, $judul, $pesan, $link = NULL, $tipe = 'info')
    {
        if (empty($userId) || empty($judul)) return FALSE;

        $validTipe = in_array($tipe, ['info', 'success', 'warning', 'danger'], TRUE) ? $tipe : 'info';

        return $this->db->insert($this->table, [
            'user_id'    => (int) $userId,
            'judul'      => trim($judul),
            'pesan'      => trim($pesan),
            'link'       => !empty($link) ? $this->normalizeLink($link) : NULL,
            'tipe'       => $validTipe,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Kirim notifikasi ke seluruh pengguna dengan role tertentu (misal: verifikator, admin)
     */
    public function sendToRoles($roles, $judul, $pesan, $link = NULL, $tipe = 'info', $excludeUserId = NULL)
    {
        if (empty($roles)) return 0;
        if (!is_array($roles)) $roles = [$roles];

        $this->db->select('id')->from('users')->where_in('role', $roles)->where('is_active', 1);
        if ($excludeUserId) {
            $this->db->where('id !=', (int) $excludeUserId);
        }
        $users = $this->db->get()->result();

        $count = 0;
        foreach ($users as $u) {
            if ($this->create($u->id, $judul, $pesan, $link, $tipe)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Kirim notifikasi ke operator SKPD tertentu
     */
    public function sendToSkpd($skpdId, $judul, $pesan, $link = NULL, $tipe = 'info', $excludeUserId = NULL)
    {
        if (empty($skpdId)) return 0;

        $this->db->select('id')->from('users')->where('skpd_id', (int) $skpdId)->where('is_active', 1);
        if ($excludeUserId) {
            $this->db->where('id !=', (int) $excludeUserId);
        }
        $users = $this->db->get()->result();

        $count = 0;
        foreach ($users as $u) {
            if ($this->create($u->id, $judul, $pesan, $link, $tipe)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Normalkan link agar selalu menggunakan URL aplikasi aktif
     */
    public function normalizeLink($link)
    {
        if (empty($link) || $link === '#') return '#';

        // Jika link mengandung domain lama atau absolute url, ambil path-nya
        if (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0) {
            $parsed = parse_url($link);
            $path = $parsed['path'] ?? '';
            $query = !empty($parsed['query']) ? '?' . $parsed['query'] : '';

            // Hapus subdirektori dasar jika ada
            $currentBase = parse_url(base_url(), PHP_URL_PATH);
            if (!empty($currentBase) && $currentBase !== '/' && strpos($path, $currentBase) === 0) {
                $path = substr($path, strlen($currentBase));
            }
            return site_url(ltrim($path, '/') . $query);
        }

        if (strpos($link, '/') === 0) {
            return site_url(ltrim($link, '/'));
        }

        return site_url($link);
    }

    /**
     * Format relatif waktu (Human friendly)
     */
    public function formatWaktuLalu($datetime)
    {
        if (empty($datetime)) return '';
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $m = floor($diff / 60);
            return $m . ' mnt lalu';
        } elseif ($diff < 86400) {
            $h = floor($diff / 3600);
            return $h . ' jam lalu';
        } elseif ($diff < 172800) {
            return 'Kemarin, ' . date('H:i', $time);
        } elseif ($diff < 604800) {
            $d = floor($diff / 86400);
            return $d . ' hari lalu';
        } else {
            return date('d M Y, H:i', $time);
        }
    }
}
