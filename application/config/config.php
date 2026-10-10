<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| KONFIGURASI APLIKASI RKBMD
| Pemerintah Kabupaten Tapin - Badan Pengelolaan Keuangan dan Aset Daerah
| SIRKBMD - Sistem Informasi Rencana Kebutuhan Barang Milik Daerah
| Versi 1.0.0
| Dikembangkan oleh Tim IT BPKAD Tapin
|--------------------------------------------------------------------------
*/
date_default_timezone_set('Asia/Singapore');

// ===== Base URL (auto-detect) =====
$is_https = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on'));
$protocol = $is_https ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/').'/';
$config['base_url'] = $protocol . $host . $path;

$config['index_page']    = '';
$config['uri_protocol']  = 'AUTO';
$config['url_suffix']    = '';
$config['enable_query_strings'] = FALSE;
$config['language']      = 'indonesian';
$config['charset']       = 'UTF-8';
$config['enable_hooks']  = TRUE;
$config['subclass_prefix'] = 'MY_';

$config['composer_autoload'] = APPPATH . '../vendor/autoload.php';
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';

// ===== Logging (production) =====
$config['log_threshold']    = 3; // 1=error, 2=debug, 3=info, 4=all
$config['log_path']         = '';
$config['log_file_extension'] = 'php';
$config['log_file_permissions'] = 0644;
$config['log_date_format']  = 'Y-m-d H:i:s';

// ===== Cache =====
$config['cache_path']       = '';
$config['cache_query_string'] = FALSE;

// ===== KEAMANAN: Encryption =====
// PENTING: Ganti key ini dengan yang baru di produksi (32 char random)
$config['encryption_key']   = 'Rk8mD!2025_Pwk_BPKAD_S3cur3K3y!!';

// ===== Session (database-driven untuk keamanan) =====
$config['sess_driver']            = 'files';
$config['sess_cookie_name']       = 'rkbmd_session';
$config['sess_expiration']        = 7200; // 2 jam

$sess_dir = APPPATH . 'cache/sessions';
if (!is_dir($sess_dir)) {
    @mkdir($sess_dir, 0755, TRUE);
}
$config['sess_save_path']         = (is_dir($sess_dir) && is_writable($sess_dir)) ? $sess_dir : sys_get_temp_dir();
$config['sess_match_ip']          = FALSE;
$config['sess_time_to_update']    = 300;
$config['sess_regenerate_destroy'] = FALSE;

// ===== Cookie Security =====
$config['cookie_prefix']    = 'rkbmd_';
$config['cookie_domain']    = '';
$config['cookie_path']      = '/';
$config['cookie_secure']    = $is_https; // otomatis TRUE jika HTTPS, FALSE jika HTTP
$config['cookie_httponly']  = TRUE;
$config['cookie_samesite']  = 'Lax';

// ===== Standardize newlines =====
$config['standardize_newlines'] = FALSE;
$config['global_xss_filtering'] = FALSE; // gunakan htmlspecialchars manual

// ===== CSRF Protection (WAJIB AKTIF) =====
$config['csrf_protection']  = TRUE;
$config['csrf_token_name']  = 'rkbmd_csrf_token';
$config['csrf_cookie_name'] = 'rkbmd_csrf_cookie';
$config['csrf_expire']      = 7200;
$config['csrf_regenerate']  = TRUE;
$config['csrf_exclude_uris'] = array();

// ===== Output Compression =====
$config['compress_output'] = FALSE;

// ===== Misc =====
$config['time_reference']   = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips']        = '';

// ===== Konfigurasi Aplikasi SIPA =====
$config['app_name']         = 'SIPA - Sistem Informasi Pengelolaan Aset';
$config['app_version']      = '2.4.0';
$config['app_owner']        = 'Pemerintah Kabupaten Tapin';
$config['app_unit']         = 'Badan Pengelolaan Keuangan dan Aset Daerah (BPKAD)';
$config['app_provinsi']     = 'Kalimantan Selatan';
$config['app_kabupaten']    = 'Tapin';

// Batas upload file
$config['upload_max_size']  = 5120; // 5 MB
$config['upload_allowed_types'] = 'pdf|jpg|jpeg|png|xlsx|xls|docx|doc';

// Login security
$config['max_login_attempts']  = 5;
$config['login_lockout_time']  = 900; // 15 menit
$config['password_min_length'] = 8;
