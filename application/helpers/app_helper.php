<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mencetak string dengan pengamanan htmlspecialchars.
 */
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Mencetak input hidden CSRF secara otomatis.
 */
if (!function_exists('csrf_input')) {
    function csrf_input() {
        $CI =& get_instance();
        return '<input type="hidden" name="' . $CI->security->get_csrf_token_name() . '" value="' . $CI->security->get_csrf_hash() . '">';
    }
}

/**
 * Menampilkan alert bootstrap berdasarkan session flashdata.
 * Dipanggil di header.php
 */
if (!function_exists('flash_alert')) {
    function flash_alert() {
        $CI =& get_instance();
        $types = ['success', 'danger', 'warning', 'info'];
        $output = '';

        foreach ($types as $type) {
            if ($msg = $CI->session->flashdata($type)) {
                $icon = [
                    'success' => 'check-circle-fill',
                    'danger'  => 'exclamation-triangle-fill',
                    'warning' => 'exclamation-circle-fill',
                    'info'    => 'info-circle-fill'
                ];
                
                $output .= '<div class="alert alert-' . $type . ' alert-dismissible fade show shadow-sm" role="alert">';
                $output .= '<i class="bi bi-' . ($icon[$type] ?? 'bell-fill') . ' me-2"></i>' . $msg;
                $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                $output .= '</div>';
            }
        }
        return $output;
    }
}

/**
 * Format label jenis usulan.
 */
if (!function_exists('label_jenis')) {
    function label_jenis($jenis) {
        $map = [
            'pengadaan'        => 'Pengadaan',
            'pemeliharaan'     => 'Pemeliharaan',
            'pemanfaatan'      => 'Pemanfaatan',
            'pemindahtanganan' => 'Pemindahtanganan',
            'penghapusan'      => 'Penghapusan'
        ];
        return $map[$jenis] ?? ucfirst($jenis);
    }
}

/**
 * Membersihkan nama file dari karakter aneh.
 */
if (!function_exists('clean_filename')) {
    function clean_filename($string) {
        $string = str_replace(' ', '_', $string);
        return preg_replace('/[^A-Za-z0-9\_]/', '', $string);
    }
}

/**
 * Format angka ke dalam format mata uang Rupiah.
 * Contoh: 1000000 -> Rp 1.000.000
 */
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return 'Rp ' . number_format($angka ?? 0, 0, ',', '.');
    }
}

/**
 * Format angka ke dalam format ribuan tanpa prefix Rp.
 * Digunakan untuk export Excel atau tampilan cetak tertentu.
 */
if (!function_exists('rupiah_excel')) {
    function rupiah_excel($angka) {
        return number_format($angka ?? 0, 0, ',', '.');
    }
}

/**
 * Generate nomor usulan RKBMD.
 * Format: RKBMD/JENIS/TAHUN/NO-URUT
 * Contoh: RKBMD/PENGADAAN/2026/0001
 */
if (!function_exists('generate_nomor_usulan')) {
    function generate_nomor_usulan($jenis, $kodeSkpd, $tahun) {
        $CI =& get_instance();
        
        // Mapping jenis ke kode
        $kodeJenis = [
            'pengadaan'        => 'PENGADAAN',
            'pemeliharaan'     => 'PEMELIHARAAN',
            'pemanfaatan'      => 'PEMANFAATAN',
            'pemindahtanganan' => 'PEMINDAHTANGANAN',
            'penghapusan'      => 'PENGHAPUSAN'
        ];
        
        $jenisKode = $kodeJenis[$jenis] ?? strtoupper($jenis);
        
        // Ambil nomor urut terakhir untuk jenis dan tahun ini
        $prefix = "RKBMD/{$jenisKode}/{$tahun}/";
        $CI->db->like('nomor_usulan', $prefix, 'after');
        $CI->db->order_by('nomor_usulan', 'DESC');
        $last = $CI->db->get('rkbmd_usulan', 1)->row();
        
        $nextNum = 1;
        if ($last) {
            $lastNum = (int) substr($last->nomor_usulan, -4);
            $nextNum = $lastNum + 1;
        }
        
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Menampilkan badge status usulan dengan label warna Bootstrap 5.
 */
if (!function_exists('badge_status')) {
    function badge_status($status) {
        $status = strtolower($status ?? '');
        $class = 'secondary';
        $label = ucfirst($status);

        switch ($status) {
            case 'draft':
                $class = 'secondary';
                break;
            case 'diajukan':
                $class = 'info text-dark';
                break;
            case 'revisi':
            case 'direvisi':
                $class = 'warning text-dark';
                $label = 'Direvisi';
                break;
            case 'diverifikasi':
                $class = 'primary';
                $label = 'Diverifikasi';
                break;
            case 'disetujui':
                $class = 'success';
                break;
            case 'ditolak':
                $class = 'danger';
                break;
            case 'ditetapkan':
                $class = 'success';
                $label = 'Ditetapkan';
                break;
        }

        return '<span class="badge bg-' . $class . '">' . $label . '</span>';
    }
}

/**
 * Mengubah format tanggal Y-m-d menjadi format Indonesia.
 * Contoh: 2026-05-01 -> 01 Mei 2026
 */
if (!function_exists('tanggal_id')) {
    function tanggal_id($tanggal, $cetak_hari = FALSE) {
        if (empty($tanggal) || $tanggal === '0000-00-00' || $tanggal === '0000-00-00 00:00:00') return '-';

        $hari = [
            1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
        ];

        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $time = strtotime($tanggal);
        $split = explode('-', date('Y-m-d', $time));
        $tgl_indo = $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];

        if ($cetak_hari) {
            $num = date('N', $time);
            return $hari[$num] . ', ' . $tgl_indo;
        }

        return $tgl_indo;
    }
}

/**
 * Pengecekan otorisasi hak akses menu / modul per user.
 */
if (!function_exists('can_access')) {
    function can_access($menuKey) {
        $CI =& get_instance();
        if (!isset($CI->auth)) {
            $CI->load->library('auth');
        }
        return $CI->auth->canAccess($menuKey);
    }
}

/**
 * Mengambil nilai konfigurasi dari tabel ex_settings dengan caching statis dalam 1 siklus request.
 */
if (!function_exists('get_setting')) {
    function get_setting($key, $default = null, $refresh = false) {
        static $cachedSettings = null;
        $CI =& get_instance();

        if ($cachedSettings === null || $refresh) {
            $cachedSettings = [];
            try {
                if ($CI->db->table_exists('ex_settings')) {
                    $rows = $CI->db->get('ex_settings')->result();
                    foreach ($rows as $r) {
                        $cachedSettings[$r->key] = $r->value;
                    }
                }
            } catch (\Throwable $e) {}
        }

        return array_key_exists($key, $cachedSettings) ? $cachedSettings[$key] : $default;
    }
}

/**
 * Menyimpan atau memperbarui nilai konfigurasi ke tabel ex_settings.
 */
if (!function_exists('set_setting')) {
    function set_setting($key, $val) {
        $CI =& get_instance();
        try {
            if ($CI->db->table_exists('ex_settings')) {
                $exists = $CI->db->where('key', $key)->count_all_results('ex_settings');
                if ($exists > 0) {
                    $CI->db->where('key', $key)->update('ex_settings', ['value' => (string)$val]);
                } else {
                    $CI->db->insert('ex_settings', ['key' => $key, 'value' => (string)$val]);
                }
                // Segarkan cache
                get_setting($key, null, true);
                return true;
            }
        } catch (\Throwable $e) {}
        return false;
    }
}