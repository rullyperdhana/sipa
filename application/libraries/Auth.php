<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Library Auth - Autentikasi & Otorisasi
 * Menggunakan password_hash() bcrypt + session aman + brute-force protection
 */
class Auth
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Login dengan proteksi brute-force (per-akun & per-IP) serta timing-attack mitigation.
     */
    public function attempt($username, $password, $remember = FALSE)
    {
        $username = trim($username);

        // Validasi minimum
        if (empty($username) || empty($password)) {
            return ['success' => FALSE, 'message' => 'Username dan password wajib diisi.'];
        }

        // 1. IP-Based Rate Limiting: Blokir IP jika ada >= 10 kegagalan login dalam 15 menit terakhir
        $clientIp = $this->CI->input->ip_address();
        $fifteenMinsAgo = date('Y-m-d H:i:s', time() - 900);
        try {
            $ipFails = $this->CI->db->where('ip_address', $clientIp)
                ->where('aksi', 'login_failed')
                ->where('created_at >=', $fifteenMinsAgo)
                ->count_all_results('log_aktivitas');

            if ($ipFails >= 10) {
                return [
                    'success' => FALSE,
                    'message' => 'Terlalu banyak percobaan login yang gagal dari jaringan/perangkat Anda. Demi keamanan, silakan coba lagi dalam 15 menit.'
                ];
            }
        } catch (\Throwable $e) {}

        // 2. Ambil user
        try {
            $user = $this->CI->db->get_where('users', ['username' => $username])->row();
        } catch (\Throwable $e) {
            log_message('error', 'Auth DB error: ' . $e->getMessage());
            return ['success' => FALSE, 'message' => 'Koneksi database/tabel bermasalah: ' . $e->getMessage()];
        }

        // Timing-attack mitigation: Jalankan dummy bcrypt jika username tidak ditemukan
        if (!$user) {
            password_verify($password, '$2y$10$abcdefghijklmnopqrstuvw1234567890abcdefghijklmnopqrstuv');
            usleep(150000); // 150ms delay
            $this->logAttempt($username, FALSE, 'user_not_found');
            return ['success' => FALSE, 'message' => 'Username atau password salah.'];
        }

        // 3. Cek status aktif / pending approval
        if (!$user->is_active) {
            return ['success' => FALSE, 'message' => 'Akun Anda belum aktif atau sedang menunggu persetujuan Administrator BPKAD. Silakan hubungi admin.'];
        }

        // 4. Cek lockout per user
        if ($user->locked_until && strtotime($user->locked_until) > time()) {
            $sisaDetik = strtotime($user->locked_until) - time();
            $sisaMenit = ceil($sisaDetik / 60);
            return ['success' => FALSE, 'message' => "Akun terkunci karena percobaan berulang. Coba lagi dalam {$sisaMenit} menit."];
        }

        // 5. Verifikasi password
        if (!password_verify($password, $user->password)) {
            $this->incrementFailedAttempts($user);
            usleep(150000); // 150ms delay
            $this->logAttempt($username, FALSE, 'wrong_password');
            return ['success' => FALSE, 'message' => 'Username atau password salah.'];
        }

        // 6. Cek apakah perlu rehash (algoritma berubah)
        if (password_needs_rehash($user->password, PASSWORD_BCRYPT, ['cost' => 10])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            try {
                $this->CI->db->where('id', $user->id)->update('users', ['password' => $newHash]);
            } catch (\Throwable $e) {}
        }

        // 7. Reset failed attempts & catat login sukses
        try {
            $this->CI->db->where('id', $user->id)->update('users', [
                'failed_attempts' => 0,
                'locked_until'    => NULL,
                'last_login'      => date('Y-m-d H:i:s'),
                'last_login_ip'   => $this->CI->input->ip_address()
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Auth update failed: ' . $e->getMessage());
        }

        // 8. Regenerate session ID untuk cegah session fixation
        $this->CI->session->sess_regenerate(FALSE);

        // Set session data
        $perms = !empty($user->menu_permissions) ? json_decode($user->menu_permissions, TRUE) : NULL;
        $sessionData = [
            'user_id'          => (int) $user->id,
            'username'         => $user->username,
            'nama_lengkap'     => $user->nama_lengkap,
            'nip'              => $user->nip,
            'role'             => $user->role,
            'skpd_id'          => $user->skpd_id ? (int) $user->skpd_id : NULL,
            'menu_permissions' => $perms,
            'is_logged_in'     => TRUE,
            'login_time'       => time(),
        ];
        $this->CI->session->set_userdata($sessionData);

        // 9. Remember-me token dengan hash aman
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            try {
                $this->CI->db->where('id', $user->id)->update('users', ['remember_token' => password_hash($token, PASSWORD_BCRYPT)]);
            } catch (\Throwable $e) {}
            set_cookie([
                'name'     => 'rkbmd_remember',
                'value'    => $user->id . ':' . $token,
                'expire'   => 60 * 60 * 24 * 30,
                'httponly' => TRUE,
                'samesite' => 'Lax'
            ]);
        }

        $this->logAttempt($username, TRUE, 'success');
        return ['success' => TRUE, 'message' => 'Login berhasil.', 'user' => $user];
    }

    /**
     * Registrasi pengguna mandiri baru dengan kontrol approval dan role default
     */
    public function registerUser($data)
    {
        // Cek status pendaftaran mandiri
        $regEnabled = function_exists('get_setting') ? get_setting('registration_enabled', '1') : '1';
        if ($regEnabled === '0') {
            return ['success' => FALSE, 'message' => 'Pendaftaran mandiri saat ini sedang ditutup oleh Administrator BPKAD.'];
        }

        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $skpdId   = (int) ($data['skpd_id'] ?? 0);

        if (empty($username) || empty($data['password']) || empty($data['nama_lengkap'])) {
            return ['success' => FALSE, 'message' => 'Nama lengkap, username, dan password wajib diisi.'];
        }

        // Cek duplikasi username
        $existUser = $this->CI->db->get_where('users', ['username' => $username])->row();
        if ($existUser) {
            return ['success' => FALSE, 'message' => 'Username sudah digunakan. Silakan gunakan username lain.'];
        }

        // Cek duplikasi email jika diisi
        if (!empty($email)) {
            $existEmail = $this->CI->db->get_where('users', ['email' => $email])->row();
            if ($existEmail) {
                return ['success' => FALSE, 'message' => 'Email sudah terdaftar. Silakan gunakan email lain.'];
            }
        }

        // Tentukan kebijakan aktivasi
        $requireApproval = function_exists('get_setting') ? get_setting('registration_require_approval', '1') : '1';
        $isActive = ($requireApproval === '1') ? 0 : 1;
        $defaultRole = function_exists('get_setting') ? get_setting('registration_default_role', 'operator_skpd') : 'operator_skpd';

        $insertData = [
            'username'         => $username,
            'nama_lengkap'     => trim($data['nama_lengkap']),
            'nip'              => !empty($data['nip']) ? trim($data['nip']) : NULL,
            'email'            => !empty($email) ? $email : NULL,
            'no_wa'            => !empty($data['no_wa']) ? trim($data['no_wa']) : NULL,
            'skpd_id'          => $skpdId ?: NULL,
            'role'             => $defaultRole,
            'password'         => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]),
            'is_active'        => $isActive,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ];

        $this->CI->db->insert('users', $insertData);
        $newId = $this->CI->db->insert_id();

        // Catat log aktivitas
        try {
            $this->CI->db->insert('log_aktivitas', [
                'user_id'    => $newId,
                'username'   => $username,
                'aksi'       => 'register',
                'modul'      => 'auth',
                'keterangan' => 'Pendaftaran mandiri (' . ($isActive ? 'Aktif' : 'Menunggu Approval Admin') . ')',
                'ip_address' => $this->CI->input->ip_address(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {}

        if ($isActive === 0) {
            $msg = 'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi dan persetujuan dari Administrator BPKAD. Anda akan dapat masuk setelah akun diverifikasi.';
        } else {
            $msg = 'Pendaftaran berhasil! Akun Anda telah aktif, silakan masuk ke sistem.';
        }

        return ['success' => TRUE, 'is_active' => $isActive, 'message' => $msg, 'user_id' => $newId];
    }

    protected function incrementFailedAttempts($user)
    {
        try {
            $maxAttempts = $this->CI->config->item('max_login_attempts') ?: 5;
            $lockoutTime = $this->CI->config->item('login_lockout_time') ?: 900;

            $newCount = (int) $user->failed_attempts + 1;
            $update = ['failed_attempts' => $newCount];

            if ($newCount >= $maxAttempts) {
                $update['locked_until'] = date('Y-m-d H:i:s', time() + $lockoutTime);
                $update['failed_attempts'] = 0;
            }

            $this->CI->db->where('id', $user->id)->update('users', $update);
        } catch (\Throwable $e) {
            log_message('error', 'incrementFailedAttempts error: ' . $e->getMessage());
        }
    }

    protected function logAttempt($username, $success, $reason = '')
    {
        try {
            $this->CI->db->insert('log_aktivitas', [
                'username'    => $username,
                'aksi'        => $success ? 'login' : 'login_failed',
                'modul'       => 'auth',
                'keterangan'  => $reason,
                'ip_address'  => $this->CI->input->ip_address(),
                'user_agent'  => substr($this->CI->input->user_agent() ?: '', 0, 255),
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Auth logAttempt error: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        $userId = $this->CI->session->userdata('user_id');
        if ($userId) {
            try {
                $this->CI->db->insert('log_aktivitas', [
                    'user_id'    => $userId,
                    'username'   => $this->CI->session->userdata('username'),
                    'aksi'       => 'logout',
                    'modul'      => 'auth',
                    'ip_address' => $this->CI->input->ip_address(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Throwable $e) {}
        }
        delete_cookie('rkbmd_remember');
        $this->CI->session->sess_destroy();
    }

    public function check()
    {
        if ((bool) $this->CI->session->userdata('is_logged_in')) {
            return TRUE;
        }

        // Auto-login via remember-me cookie yang aman
        $rememberCookie = get_cookie('rkbmd_remember');
        if (!empty($rememberCookie) && strpos($rememberCookie, ':') !== FALSE) {
            list($uid, $token) = explode(':', $rememberCookie, 2);
            $user = $this->CI->db->get_where('users', ['id' => (int) $uid, 'is_active' => 1])->row();
            if ($user && !empty($user->remember_token) && password_verify($token, $user->remember_token)) {
                $this->CI->session->sess_regenerate(FALSE);
                $perms = !empty($user->menu_permissions) ? json_decode($user->menu_permissions, TRUE) : NULL;
                $this->CI->session->set_userdata([
                    'user_id'          => (int) $user->id,
                    'username'         => $user->username,
                    'nama_lengkap'     => $user->nama_lengkap,
                    'nip'              => $user->nip,
                    'role'             => $user->role,
                    'skpd_id'          => $user->skpd_id ? (int) $user->skpd_id : NULL,
                    'menu_permissions' => $perms,
                    'is_logged_in'     => TRUE,
                    'login_time'       => time(),
                ]);
                // Rotate remember-me token
                $newToken = bin2hex(random_bytes(32));
                $this->CI->db->where('id', $user->id)->update('users', ['remember_token' => password_hash($newToken, PASSWORD_BCRYPT)]);
                set_cookie([
                    'name'     => 'rkbmd_remember',
                    'value'    => $user->id . ':' . $newToken,
                    'expire'   => 60 * 60 * 24 * 30,
                    'httponly' => TRUE,
                    'samesite' => 'Lax'
                ]);
                return TRUE;
            }
        }

        return FALSE;
    }


    public function user($field = NULL)
    {
        if (!$this->check()) return NULL;
        if ($field) return $this->CI->session->userdata($field);
        return (object) [
            'id'               => $this->CI->session->userdata('user_id'),
            'username'         => $this->CI->session->userdata('username'),
            'nama_lengkap'     => $this->CI->session->userdata('nama_lengkap'),
            'role'             => $this->CI->session->userdata('role'),
            'skpd_id'          => $this->CI->session->userdata('skpd_id'),
            'menu_permissions' => $this->CI->session->userdata('menu_permissions')
        ];
    }

    /**
     * Memeriksa apakah user yang sedang login memiliki hak akses ke menu/modul tertentu.
     * Admin selalu memiliki akses penuh (TRUE).
     */
    public function canAccess($menuKey)
    {
        if (!$this->check()) return FALSE;
        $role = $this->CI->session->userdata('role');
        if ($role === 'admin') return TRUE;

        $perms = $this->CI->session->userdata('menu_permissions');

        // Jika belum diset konfigurasi spesifik menu (perms is null), gunakan default role
        if ($perms === NULL) {
            return $this->defaultRolePermissions($role, $menuKey);
        }

        if (!is_array($perms)) {
            return FALSE;
        }

        return in_array($menuKey, $perms, TRUE);
    }

    /**
     * Hak akses default jika admin belum menentukan checklist khusus per-user.
     */
    public function defaultRolePermissions($role, $menuKey)
    {
        if ($role === 'admin') return TRUE;

        if (in_array($role, ['skpd', 'operator_skpd'], TRUE)) {
            return in_array($menuKey, [
                'rkbmd_pengadaan', 'rkbmd_pemeliharaan', 'rkbmd_pemanfaatan',
                'rkbmd_pemindahtanganan', 'rkbmd_penghapusan',
                'ssh', 'sbu', 'laporan'
            ], TRUE);
        }

        if ($role === 'verifikator') {
            return in_array($menuKey, [
                'verifikasi_rkbmd', 'verifikasi_standar', 'jadwal_standar',
                'ssh', 'sbu', 'laporan'
            ], TRUE);
        }

        if (in_array($role, ['penetap', 'pimpinan'], TRUE)) {
            return in_array($menuKey, [
                'rkbmd_pengadaan', 'rkbmd_pemeliharaan', 'rkbmd_pemanfaatan',
                'rkbmd_pemindahtanganan', 'rkbmd_penghapusan',
                'ssh', 'sbu', 'penetapan_standar', 'laporan'
            ], TRUE);
        }

        return FALSE;
    }

    public function hasRole($roles)
    {
        $current = $this->CI->session->userdata('role');
        if (is_array($roles)) return in_array($current, $roles, TRUE);
        return $current === $roles;
    }

    /**
     * Filter akses: redirect ke login jika tidak login,
     * atau ke 403 jika role tidak sesuai.
     */
    public function restrict($roles = NULL)
    {
        if (!$this->check()) {
            $this->CI->session->set_flashdata('warning', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
        if ($roles !== NULL && !$this->hasRole($roles)) {
            show_error('Anda tidak memiliki hak akses ke halaman ini.', 403, 'Akses Ditolak');
        }
    }

    /**
     * Membatasi akses controller/method berdasarkan menuKey.
     */
    public function restrictMenu($menuKey)
    {
        if (!$this->canAccess($menuKey)) {
            show_error('Anda tidak memiliki hak akses untuk membuka modul / menu ini. Hubungi Administrator.', 403, 'Akses Ditolak');
        }
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
}
