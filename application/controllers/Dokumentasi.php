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
        $rawTab = $this->input->get('tab', TRUE) ?: 'changelog';
        $tab = str_replace('tab-', '', strtolower(trim($rawTab)));
        if (!in_array($tab, ['changelog', 'panduan', 'sistem', 'readme'], TRUE)) {
            $tab = 'changelog';
        }

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
            'appVersion'    => $this->config->item('app_version') ?: '2.9.0',
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
            'app_version'        => $this->config->item('app_version') ?: '2.9.0',
            'app_owner'          => $this->config->item('app_owner') ?: 'Pemerintah Kabupaten Tapin',
            'app_unit'           => $this->config->item('app_unit') ?: 'BKAD',
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
     * Konversi Markdown ke HTML bersih untuk tampilan dokumentasi
     */
    protected function _renderMarkdown($text)
    {
        $lines = explode("\n", $text);
        $output = [];
        $listStack = []; // Stack level list: ['ul_1', 'ul_2', etc]
        $inCodeBlock = false;
        $codeBlockContent = [];
        $inTable = false;
        $tableLines = [];

        $closeLists = function(&$output, &$listStack, $targetLevel = 0) {
            while (count($listStack) > $targetLevel) {
                array_pop($listStack);
                $output[] = "</ul>";
            }
        };

        $flushTable = function(&$output, &$tableLines) {
            if (empty($tableLines)) return;
            if (count($tableLines) >= 2) {
                $headerCols = array_map('trim', explode('|', trim($tableLines[0], '|')));
                $aligns = [];
                if (isset($tableLines[1])) {
                    $sepCols = array_map('trim', explode('|', trim($tableLines[1], '|')));
                    foreach ($sepCols as $s) {
                        if (strpos($s, ':') === 0 && substr($s, -1) === ':') $aligns[] = 'text-center';
                        elseif (substr($s, -1) === ':') $aligns[] = 'text-end';
                        else $aligns[] = 'text-start';
                    }
                }
                $tHtml = "<div class=\"table-responsive my-3 shadow-sm rounded border\"><table class=\"table table-bordered table-striped table-hover table-sm align-middle small mb-0 bg-white\"><thead class=\"table-light\"><tr>";
                foreach ($headerCols as $idx => $hc) {
                    $align = $aligns[$idx] ?? 'text-start';
                    $tHtml .= "<th class=\"{$align} py-2 px-3 fw-bold\">" . $this->_formatInline($hc) . "</th>";
                }
                $tHtml .= "</tr></thead><tbody>";
                for ($i = 2; $i < count($tableLines); $i++) {
                    $rowCols = array_map('trim', explode('|', trim($tableLines[$i], '|')));
                    $tHtml .= "<tr>";
                    foreach ($rowCols as $idx => $rc) {
                        $align = $aligns[$idx] ?? 'text-start';
                        $tHtml .= "<td class=\"{$align} py-2 px-3\">" . $this->_formatInline($rc) . "</td>";
                    }
                    $tHtml .= "</tr>";
                }
                $tHtml .= "</tbody></table></div>";
                $output[] = $tHtml;
            }
            $tableLines = [];
        };

        foreach ($lines as $line) {
            // 1. Blok Kode
            if (preg_match("/^```([a-z0-9_-]*)/i", $line)) {
                if ($inTable) { $flushTable($output, $tableLines); $inTable = false; }
                $closeLists($output, $listStack, 0);

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

            // 2. Tabel Markdown
            $trimmedLine = trim($line);
            if (strpos($trimmedLine, '|') === 0 && substr($trimmedLine, -1) === '|') {
                $closeLists($output, $listStack, 0);
                $inTable = true;
                $tableLines[] = $trimmedLine;
                continue;
            } else {
                if ($inTable) {
                    $flushTable($output, $tableLines);
                    $inTable = false;
                }
            }

            // 3. Garis Horizontal
            if (preg_match("/^(\-{3,}|\*{3,})$/", $trimmedLine)) {
                $closeLists($output, $listStack, 0);
                $output[] = "<hr class=\"my-4 border-secondary opacity-25\">";
                continue;
            }

            // 4. Alerts GitHub Style
            if (preg_match("/>\s*\[!NOTE\]\s*(.*)$/i", $line, $al)) {
                $closeLists($output, $listStack, 0);
                $output[] = "<div class=\"alert alert-info py-2 px-3 small border-start border-4 border-info my-2 shadow-sm\"><i class=\"bi bi-info-circle-fill me-1 text-info\"></i> <strong>Catatan:</strong> " . $this->_formatInline($al[1]) . "</div>";
                continue;
            }
            if (preg_match("/>\s*\[!TIP\]\s*(.*)$/i", $line, $al)) {
                $closeLists($output, $listStack, 0);
                $output[] = "<div class=\"alert alert-success py-2 px-3 small border-start border-4 border-success my-2 shadow-sm\"><i class=\"bi bi-lightbulb-fill me-1 text-success\"></i> <strong>Tips:</strong> " . $this->_formatInline($al[1]) . "</div>";
                continue;
            }
            if (preg_match("/>\s*\[!WARNING\]\s*(.*)$/i", $line, $al)) {
                $closeLists($output, $listStack, 0);
                $output[] = "<div class=\"alert alert-warning py-2 px-3 small border-start border-4 border-warning my-2 shadow-sm\"><i class=\"bi bi-exclamation-triangle-fill me-1 text-warning\"></i> <strong>Perhatian:</strong> " . $this->_formatInline($al[1]) . "</div>";
                continue;
            }
            if (preg_match("/>\s*(.*)$/", $line, $al) && !empty($al[1])) {
                $closeLists($output, $listStack, 0);
                $output[] = "<div class=\"text-secondary small border-start border-3 border-secondary ps-2 my-1\">" . $this->_formatInline($al[1]) . "</div>";
                continue;
            }

            // 5. Headers
            if (preg_match("/^### (.*)$/", $line, $h)) {
                $closeLists($output, $listStack, 0);
                $output[] = "<h6 class=\"fw-bold text-dark mt-3 mb-2 d-flex align-items-center gap-2\"><span class=\"badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2.5\"><i class=\"bi bi-stars me-1\"></i>" . htmlspecialchars($h[1]) . "</span></h6>";
                continue;
            }
            if (preg_match("/^## (.*)$/", $line, $h)) {
                $closeLists($output, $listStack, 0);
                $output[] = "<h5 class=\"fw-bold text-primary mt-4 mb-3 pb-1 border-bottom d-flex align-items-center gap-2\"><i class=\"bi bi-bookmark-check-fill text-primary\"></i>" . htmlspecialchars($h[1]) . "</h5>";
                continue;
            }
            if (preg_match("/^# (.*)$/", $line, $h)) {
                $closeLists($output, $listStack, 0);
                $output[] = "<h4 class=\"fw-bold text-dark mb-3\">" . htmlspecialchars($h[1]) . "</h4>";
                continue;
            }

            // 6. Multi-level List Items
            // Level 3: Indent >= 4 spaces
            if (preg_match("/^(\s{4,}|\t{2,})\-\s+(.*)$/", $line, $li)) {
                while (count($listStack) < 3) {
                    $lvl = count($listStack) + 1;
                    $style = ($lvl === 3) ? 'list-style-type: square;' : (($lvl === 2) ? 'list-style-type: circle;' : '');
                    $class = ($lvl === 3) ? 'mb-1 ps-3 text-muted small' : 'mb-1.5 ps-3 text-secondary small';
                    $output[] = "<ul class=\"{$class}\" style=\"{$style}\">";
                    $listStack[] = 'ul_' . $lvl;
                }
                $output[] = "<li class=\"mb-1\">" . $this->_formatInline($li[2]) . "</li>";
                continue;
            }

            // Level 2: Indent 2-3 spaces
            if (preg_match("/^(\s{2,3}|\t)\-\s+(.*)$/", $line, $li)) {
                if (count($listStack) > 2) {
                    $closeLists($output, $listStack, 2);
                }
                while (count($listStack) < 2) {
                    $lvl = count($listStack) + 1;
                    $style = ($lvl === 2) ? 'list-style-type: circle;' : '';
                    $class = ($lvl === 2) ? 'mb-1.5 ps-3 text-secondary small' : 'mb-2 ps-3 text-dark';
                    $output[] = "<ul class=\"{$class}\" style=\"{$style}\">";
                    $listStack[] = 'ul_' . $lvl;
                }
                $output[] = "<li class=\"mb-1\">" . $this->_formatInline($li[2]) . "</li>";
                continue;
            }

            // Level 1: Indent 0-1 space
            if (preg_match("/^(\s{0,1})\-\s+(.*)$/", $line, $li)) {
                if (count($listStack) > 1) {
                    $closeLists($output, $listStack, 1);
                }
                if (count($listStack) === 0) {
                    $output[] = "<ul class=\"mb-3 ps-3 text-dark\">";
                    $listStack[] = 'ul_1';
                }
                $output[] = "<li class=\"mb-1.5\">" . $this->_formatInline($li[2]) . "</li>";
                continue;
            }

            // Baris Kosong
            if ($trimmedLine === "") {
                $closeLists($output, $listStack, 0);
                continue;
            }

            // Teks Biasa Paragraf
            $closeLists($output, $listStack, 0);
            $output[] = "<p class=\"mb-2 text-secondary\">" . $this->_formatInline($line) . "</p>";
        }

        if ($inTable) {
            $flushTable($output, $tableLines);
        }
        $closeLists($output, $listStack, 0);

        return implode("\n", $output);
    }

    /**
     * Format inline markdown (Gambar, Badge, Bold, Italic, Code, Link GitHub)
     */
    protected function _formatInline($text)
    {
        // 1. Gambar bersarang dalam link [![Alt](img)](link)
        $text = preg_replace_callback("/\[\!\[(.*?)\]\((.*?)\)\]\((.*?)\)/", function($m) {
            $alt = htmlspecialchars($m[1]);
            $img = $m[2];
            $url = $m[3];
            return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"d-inline-block me-1 mb-1\"><img src=\"{$img}\" alt=\"{$alt}\" style=\"height:20px;vertical-align:middle;\"></a>";
        }, $text);

        // 2. Gambar biasa ![Alt](img)
        $text = preg_replace_callback("/\!\[(.*?)\]\((.*?)\)/", function($m) {
            $alt = htmlspecialchars($m[1]);
            $img = $m[2];
            return "<img src=\"{$img}\" alt=\"{$alt}\" class=\"d-inline-block me-1 mb-1\" style=\"height:20px;vertical-align:middle;\">";
        }, $text);

        // 3. Format teks tebal, miring, dan kode
        $text = preg_replace("/\*\*(.*?)\*\*/", "<strong class=\"text-dark fw-semibold\">$1</strong>", $text);
        $text = preg_replace("/\*(.*?)\*/", "<em>$1</em>", $text);
        $text = preg_replace("/`([^`]+)`/", "<code class=\"bg-light text-danger px-1.5 py-0.5 rounded font-monospace\" style=\"font-size:0.875em;\">$1</code>", $text);

        // 4. Format link [Label](URL) - Mengarahkan berkas repositori ke GitHub resmi
        $githubBase = 'https://github.com/rullyperdhana/sipa/blob/main/';
        $text = preg_replace_callback("/\[(.*?)\]\((.*?)\)/", function($m) use ($githubBase) {
            $label = $m[1];
            $url = trim($m[2]);

            // Jika URL adalah path file/folder lokal di repositori
            if (preg_match('/^(application|assets|database|system|uploads|CHANGELOG|README|\.\/)/i', $url) || preg_match('/\.(php|sql|md|js|css|json|html|xlsx|png|jpg)$/i', $url)) {
                $cleanUrl = ltrim($url, './');
                $ghUrl = $githubBase . $cleanUrl;
                return "<a href=\"{$ghUrl}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none py-1 px-2 font-monospace small\" title=\"Buka berkas di GitHub: {$cleanUrl}\"><i class=\"bi bi-github me-1\"></i>{$label} <i class=\"bi bi-box-arrow-up-right ms-0.5\" style=\"font-size:9px;\"></i></a>";
            }

            // Jika anchor di halaman yang sama (#...)
            if (strpos($url, '#') === 0) {
                return "<a href=\"{$url}\" class=\"fw-semibold text-primary text-decoration-none\">{$label}</a>";
            }

            // Jika tautan eksternal web (http/https/mailto)
            return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"fw-semibold text-primary text-decoration-none\">{$label} <i class=\"bi bi-box-arrow-up-right small\" style=\"font-size:10px;\"></i></a>";
        }, $text);

        return $text;
    }
}
