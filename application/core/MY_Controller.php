<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller untuk seluruh aplikasi.
 */
class MY_Controller extends CI_Controller
{
    public $currentUser = NULL;

    public function __construct()
    {
        parent::__construct();

        // Header keamanan HTTP
        $this->output
            ->set_header('X-Frame-Options: SAMEORIGIN')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_header('X-XSS-Protection: 1; mode=block')
            ->set_header('Referrer-Policy: strict-origin-when-cross-origin')
            ->set_header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

        // Cegah caching halaman setelah logout
        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache');

        $this->currentUser = $this->auth->user();
    }
}

/**
 * Controller untuk halaman yang wajib login.
 */
class Auth_Controller extends MY_Controller
{
    protected $allowedRoles = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->auth->restrict($this->allowedRoles);
        $this->load->model('notifikasi_model');
    }
}

/**
 * Controller khusus admin.
 */
class Admin_Controller extends Auth_Controller
{
    protected $allowedRoles = ['admin'];
}

/**
 * Controller khusus SKPD (operator).
 */
class Skpd_Controller extends Auth_Controller
{
    protected $allowedRoles = ['skpd', 'admin'];
}

/**
 * Controller khusus Verifikator BPKAD.
 */
class Verifikator_Controller extends Auth_Controller
{
    protected $allowedRoles = ['verifikator', 'admin'];
}
