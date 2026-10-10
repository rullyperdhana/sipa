<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Dokumentasi & Riwayat Pembaruan Aplikasi SIPA
 * Khusus dapat diakses oleh Administrator Sistem (Role: admin)
 */
class Dokumentasi extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Halaman Utama Dokumentasi & Pembaruan Aplikasi
     */
    public function index()
    {
        $tab = $this->input->get('tab', TRUE) ?: 'changelog';

        // 1. Parse CHANGELOG.md untuk riwayat rilis
        $changelogPath = FCPATH . 'CHANGELOG.md';
        $changelogList = [];
        if (file_exists($changelogPath)) {
            $changelogRaw = file_get_contents($changelogPath);
            preg_match_all("/## \[([0-9\.]+)\] - ([0-9\-]+)(.*?)(?=(## \[[0-9\.]+\]|$))/s", $changelogRaw, $matches, PREG_SET_ORDER);
            foreach ($matches as $idx => $m) {
                $changelogList[] = [
                    'version'   => $m[1],
                    'date'      => $m[2],
                    'raw_body'  => trim($m[3]),
                    'html_body' => $this->_renderMarkdown(trim($m[3])),
                    'is_latest' => ($idx === 0)
                ];
            }
        }

        // 2. Parse README.md untuk ringkasan manual teknis
        $readmePath = FCPATH . 'README.md';
        $readmeHtml = '';
        if (file_exists($readmePath)) {
            $readmeRaw = file_get_contents($readmePath);
            $readmeHtml = $this->_renderMarkdown($readmeRaw);
        }

        // 3. Kumpulkan Diagnostik Lingkungan Sistem & Server
        $systemInfo = $this->_getSystemDiagnostics();

        $data = [
            'title'         => 'Dokumentasi & Pembaruan Sistem',
            'activeTab'     => $tab,
            'appVersion'    => $this->config->item('app_version') ?: '2.8.1',
            'changelogList' => $changelogList,
            'readmeHtml'    => $readmeHtml,
            'systemInfo'    => $systemInfo,
            'user'          => $this->currentUser ?? $this->auth->user()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('dokumentasi/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Mengumpulkan data diagnostik lingkungan server dan aplikasi secara aman
     */
    private function _getSystemDiagnostics()
    {
        $uploadDir = FCPATH . 'uploads/';
        $isUploadWritable = is_dir($uploadDir) && is_writable($uploadDir);

        $dbVersion = 'Unknown';
        try {
            if (!empty($this->db) && method_exists($this->db, 'version')) {
                $dbVersion = (string) $this->db->version();
            }
        } catch (\Throwable $e) {
            $dbVersion = 'MySQL Error';
        }

        $totalUsers   = 0;
        $totalSkpd    = 0;
        $totalStandar = 0;
        $totalRkbmd   = 0;

        try {
            if (!empty($this->db) && $this->db->table_exists('users')) {
                $totalUsers = (int) $this->db->count_all('users');
            }
        } catch (\Throwable $e) {}

        try {
            if (!empty($this->db) && $this->db->table_exists('skpd')) {
                $totalSkpd = (int) $this->db->count_all('skpd');
            }
        } catch (\Throwable $e) {}

        try {
            if (!empty($this->db) && $this->db->table_exists('standar_harga_usulan')) {
                $totalStandar = (int) $this->db->count_all('standar_harga_usulan');
            }
        } catch (\Throwable $e) {}

        try {
            if (!empty($this->db) && $this->db->table_exists('rkbmd_usulan')) {
                $totalRkbmd = (int) $this->db->count_all('rkbmd_usulan');
            }
        } catch (\Throwable $e) {}

        return [
            'app_name'           => $this->config->item('app_name') ?: 'SIPA',
            'app_version'        => $this->config->item('app_version') ?: '2.8.1',
            'app_owner'          => $this->config->item('app_owner') ?: 'Pemerintah Kabupaten Tapin',
            'app_unit'           => $this->config->item('app_unit') ?: 'BPKAD',
            'environment'        => defined('ENVIRONMENT') ? ENVIRONMENT : 'production',
            'base_url'           => function_exists('base_url') ? base_url() : '/',
            'ci_version'         => defined('CI_VERSION') ? CI_VERSION : '3.1.13',
            'php_version'        => PHP_VERSION,
            'php_sapi'           => php_sapi_name(),
            'server_os'          => PHP_OS . ' (' . php_uname('m') . ')',
            'server_software'    => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'db_driver'          => $this->db->dbdriver ?? 'mysqli',
            'db_version'         => $dbVersion,
            'db_name'            => $this->db->database ?? '-',
            'memory_limit'       => ini_get('memory_limit') ?: 'N/A',
            'max_execution_time' => (ini_get('max_execution_time') ?: '0') . ' detik',
            'upload_max_filesize'=> ini_get('upload_max_filesize') ?: 'N/A',
            'post_max_size'      => ini_get('post_max_size') ?: 'N/A',
            'uploads_writable'   => $isUploadWritable,
            'fiscal_year'        => function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027,
            'counts'             => [
                'users'         => $totalUsers,
                'skpd'          => $totalSkpd,
                'standar_harga' => $totalStandar,
                'rkbmd'         => $totalRkbmd
            ]
        ];
    }

    /**
     * Konversi Markdown sederhana ke HTML bersih untuk tampilan dokumentasi
     */
    private function _renderMarkdown($text)
    {
        $lines = explode("\n", $text);
        $output = [];
        $inList = false;
        $inSubList = false;
        $inCodeBlock = false;
        $codeBlockContent = [];

        foreach ($lines as $line) {
            // 1. Blok Kode
            if (preg_match("/^```([a-z0-9_-]*)/i", $line)) {
                if ($inCodeBlock) {
                    $output[] = "<pre class=\"bg-dark text-light p-3 rounded font-monospace small mb-3 overflow-auto border\"><code>" . htmlspecialchars(implode("\n", $codeBlockContent)) . "</code></pre>";
                    $inCodeBlock = false;
                    $codeBlockContent = [];
                } else {
                    $inCodeBlock = true;
                }
                continue;
            }

            if ($inCodeBlock) {
                $codeBlockContent[] = $line;
                continue;
            }

            // 2. Garis Horizontal
            if (preg_match("/^(\-{3,}|\*{3,})$/", trim($line))) {
                if ($inSubList) { $output[] = "</ul>"; $inSubList = false; }
                if ($inList) { $output[] = "</ul>"; $inList = false; }
                $output[] = "<hr class=\"my-4 border-secondary opacity-25\">";
                continue;
            }

            // 3. Alerts GitHub Style
            if (preg_match("/>\s*\[!NOTE\]\s*(.*)$/i", $line, $al)) {
                $output[] = "<div class=\"alert alert-info py-2 px-3 small border-start border-4 border-info my-2\"><i class=\"bi bi-info-circle-fill me-1 text-info\"></i> <strong>Catatan:</strong> " . $this->_formatInline($al[1]);
                continue;
            }
            if (preg_match("/>\s*\[!TIP\]\s*(.*)$/i", $line, $al)) {
                $output[] = "<div class=\"alert alert-success py-2 px-3 small border-start border-4 border-success my-2\"><i class=\"bi bi-lightbulb-fill me-1 text-success\"></i> <strong>Tips:</strong> " . $this->_formatInline($al[1]);
                continue;
            }
            if (preg_match("/>\s*\[!WARNING\]\s*(.*)$/i", $line, $al)) {
                $output[] = "<div class=\"alert alert-warning py-2 px-3 small border-start border-4 border-warning my-2\"><i class=\"bi bi-exclamation-triangle-fill me-1 text-warning\"></i> <strong>Perhatian:</strong> " . $this->_formatInline($al[1]);
                continue;
            }
            if (preg_match("/>\s*(.*)$/", $line, $al) && !empty($al[1])) {
                $output[] = "<div class=\"text-secondary small border-start border-3 border-secondary ps-2 my-1\">" . $this->_formatInline($al[1]) . "</div>";
                continue;
            }

            // 4. Headers
            if (preg_match("/^### (.*)$/", $line, $h)) {
                if ($inSubList) { $output[] = "</ul>"; $inSubList = false; }
                if ($inList) { $output[] = "</ul>"; $inList = false; }
                $output[] = "<h6 class=\"fw-bold text-dark mt-3 mb-2 d-flex align-items-center gap-2\"><span class=\"badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2.5\"><i class=\"bi bi-stars me-1\"></i>" . htmlspecialchars($h[1]) . "</span></h6>";
                continue;
            }
            if (preg_match("/^## (.*)$/", $line, $h)) {
                if ($inSubList) { $output[] = "</ul>"; $inSubList = false; }
                if ($inList) { $output[] = "</ul>"; $inList = false; }
                $output[] = "<h5 class=\"fw-bold text-primary mt-4 mb-3 pb-1 border-bottom d-flex align-items-center gap-2\"><i class=\"bi bi-bookmark-check-fill text-primary\"></i>" . htmlspecialchars($h[1]) . "</h5>";
                continue;
            }
            if (preg_match("/^# (.*)$/", $line, $h)) {
                if ($inSubList) { $output[] = "</ul>"; $inSubList = false; }
                if ($inList) { $output[] = "</ul>"; $inList = false; }
                $output[] = "<h4 class=\"fw-bold text-dark mb-3\">" . htmlspecialchars($h[1]) . "</h4>";
                continue;
            }

            // 5. Sublist
            if (preg_match("/^(\s{2,}|\t)\-\s+(.*)$/", $line, $li)) {
                if (!$inSubList) {
                    $output[] = "<ul class=\"mb-2 ps-3 text-secondary small\" style=\"list-style-type: circle;\">";
                    $inSubList = true;
                }
                $output[] = "<li class=\"mb-1\">" . $this->_formatInline($li[2]) . "</li>";
                continue;
            }

            // 6. List Utama
            if (preg_match("/^\-\s+(.*)$/", $line, $li)) {
                if ($inSubList) {
                    $output[] = "</ul>";
                    $inSubList = false;
                }
                if (!$inList) {
                    $output[] = "<ul class=\"mb-3 ps-3 text-dark\">";
                    $inList = true;
                }
                $output[] = "<li class=\"mb-1.5\">" . $this->_formatInline($li[1]) . "</li>";
                continue;
            }

            // Baris Kosong
            if (trim($line) === "") {
                if ($inSubList) { $output[] = "</ul>"; $inSubList = false; }
                if ($inList) { $output[] = "</ul>"; $inList = false; }
                continue;
            }

            if ($inSubList) { $output[] = "</ul>"; $inSubList = false; }
            if ($inList) { $output[] = "</ul>"; $inList = false; }

            $output[] = "<p class=\"mb-2 text-secondary\">" . $this->_formatInline($line) . "</p>";
        }

        if ($inSubList) { $output[] = "</ul>"; }
        if ($inList) { $output[] = "</ul>"; }

        return implode("\n", $output);
    }

    /**
     * Format inline markdown (Bold, Italic, Code, Link)
     */
    private function _formatInline($text)
    {
        $text = preg_replace("/\*\*(.*?)\*\*/", "<strong class=\"text-dark fw-semibold\">$1</strong>", $text);
        $text = preg_replace("/\*(.*?)\*/", "<em>$1</em>", $text);
        $text = preg_replace("/`([^`]+)`/", "<code class=\"bg-light text-danger px-1.5 py-0.5 rounded font-monospace\" style=\"font-size:0.875em;\">$1</code>", $text);
        $text = preg_replace("/\[(.*?)\]\((.*?)\)/", "<a href=\"$2\" target=\"_blank\" class=\"fw-semibold text-primary text-decoration-none\">$1 <i class=\"bi bi-box-arrow-up-right small\" style=\"font-size:10px;\"></i></a>", $text);
        return $text;
    }
}
