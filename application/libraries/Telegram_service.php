<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Telegram Notification Service Library untuk SIPA Kabupaten Tapin
 * 
 * Mengirim notifikasi otomatis secara real-time ke Admin BKAD / Grup Telegram Tim Verifikator
 * setiap kali SKPD mengirimkan usulan Standar Satuan Harga (SSH) atau Standar Biaya Umum (SBU).
 */
class Telegram_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('app');
    }

    /**
     * Mengambil seluruh parameter pengaturan Telegram dari tabel ex_settings
     * @return array
     */
    public function getSettings()
    {
        return [
            'telegram_enabled'   => get_setting('telegram_enabled', '0'),
            'telegram_bot_token' => get_setting('telegram_bot_token', ''),
            'telegram_chat_id'   => get_setting('telegram_chat_id', ''),
            'telegram_parse_mode'=> get_setting('telegram_parse_mode', 'HTML')
        ];
    }

    /**
     * Menyimpan pengaturan Telegram ke tabel ex_settings
     * @param array $data
     * @return bool
     */
    public function saveSettings(array $data)
    {
        if (isset($data['telegram_enabled'])) {
            set_setting('telegram_enabled', $data['telegram_enabled'] ? '1' : '0');
        }
        if (isset($data['telegram_bot_token'])) {
            set_setting('telegram_bot_token', trim($data['telegram_bot_token']));
        }
        if (isset($data['telegram_chat_id'])) {
            set_setting('telegram_chat_id', trim($data['telegram_chat_id']));
        }
        if (isset($data['telegram_parse_mode'])) {
            set_setting('telegram_parse_mode', trim($data['telegram_parse_mode']));
        }
        return true;
    }

    /**
     * Periksa apakah notifikasi Telegram aktif dan siap digunakan
     * @return bool
     */
    public function isEnabled()
    {
        $settings = $this->getSettings();
        return ($settings['telegram_enabled'] === '1' && !empty($settings['telegram_bot_token']) && !empty($settings['telegram_chat_id']));
    }

    /**
     * Mengirim pesan teks ke Telegram Bot API
     * 
     * @param string $message Teks pesan (HTML atau Markdown)
     * @param string|null $chatId Target Chat ID / Group ID (opsional, default: dari settings)
     * @param string|null $token Bot token khusus (opsional, default: dari settings)
     * @param string $parseMode Mode parse pesan ('HTML' atau 'Markdown')
     * @return array ['success' => bool, 'message' => string, 'details' => array]
     */
    public function sendMessage($message, $chatId = null, $token = null, $parseMode = 'HTML')
    {
        $settings = $this->getSettings();
        $botToken = !empty($token) ? trim($token) : $settings['telegram_bot_token'];
        $targetChat = !empty($chatId) ? trim($chatId) : $settings['telegram_chat_id'];

        if (empty($botToken)) {
            return ['success' => false, 'message' => 'Token Bot Telegram belum dikonfigurasi.'];
        }

        if (empty($targetChat)) {
            return ['success' => false, 'message' => 'Chat ID Telegram penerima belum dikonfigurasi.'];
        }

        // Pisahkan jika ada beberapa Chat ID (dipisahkan koma, titik koma, atau baris baru)
        $chatIds = preg_split('/[\s,;]+/', $targetChat, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($chatIds)) {
            return ['success' => false, 'message' => 'Format Chat ID tidak valid.'];
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $results = [];
        $overallSuccess = true;
        $lastError = '';

        foreach ($chatIds as $cId) {
            $payload = [
                'chat_id'                  => $cId,
                'text'                     => $message,
                'parse_mode'               => $parseMode,
                'disable_web_page_preview' => false
            ];

            $response = $this->postHttp($url, $payload);
            $results[$cId] = $response;

            if (!$response['success']) {
                $overallSuccess = false;
                $lastError = $response['message'];
            }
        }

        return [
            'success' => $overallSuccess,
            'message' => $overallSuccess ? 'Notifikasi Telegram berhasil dikirim.' : "Gagal mengirim ke sebagian/seluruh Chat ID: {$lastError}",
            'details' => $results
        ];
    }

    /**
     * Mengirim notifikasi pengajuan Surat Usulan SSH/SBU baru ke Telegram Admin BKAD
     * 
     * @param object $surat Objek baris standar_harga_surat
     * @param int $itemCount Jumlah rincian item dalam surat
     * @param object $user Pengguna yang melakukan pengiriman
     * @param string $tipe SSH atau SBU
     * @return array
     */
    public function sendSuratNotification($surat, $itemCount, $user, $tipe = 'SSH')
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Notifikasi Telegram dinonaktifkan.'];
        }

        // Dapatkan nama SKPD
        $namaSkpd = 'SKPD Pengusul';
        if (!empty($surat->id_skpd)) {
            $skpd = $this->CI->db->select('nama_skpd')->get_where('skpd', ['id' => (int)$surat->id_skpd])->row();
            if ($skpd) $namaSkpd = $skpd->nama_skpd;
        }

        $namaPengusul = !empty($user->nama_lengkap) ? $user->nama_lengkap : ($user->username ?? 'Operator SKPD');
        $tipeUpper    = strtoupper($tipe ?: ($surat->tipe ?? 'SSH'));
        $modulJudul   = ($tipeUpper === 'SBU') ? 'Standar Biaya Umum (SBU)' : 'Standar Satuan Harga (SSH)';
        $nomorSurat   = htmlspecialchars($surat->nomor_surat ?? '-', ENT_QUOTES, 'UTF-8');
        $perihal      = htmlspecialchars($surat->perihal ?? '-', ENT_QUOTES, 'UTF-8');
        $tglSurat     = !empty($surat->tanggal_surat) ? tanggal_id($surat->tanggal_surat) : date('d-m-Y');
        $nominal      = rupiah($surat->total_nominal ?? 0);
        $waktuKirim   = date('d F Y, H:i') . ' WITA';
        $ta           = !empty($surat->tahun_anggaran) ? $surat->tahun_anggaran : (function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : date('Y'));

        $prefixUrl    = strtolower($tipeUpper);
        $linkVerifikasi = site_url("{$prefixUrl}/verifikasi/surat/{$surat->id}");

        $icon = ($tipeUpper === 'SBU') ? '💼' : '📦';

        $text  = "🏛️ <b>NOTIFIKASI USULAN BARU SIPA</b>\n";
        $text .= "<i>Pemerintah Kabupaten Tapin</i>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $text .= "{$icon} <b>Instrumen:</b> <b>{$modulJudul} (TA {$ta})</b>\n";
        $text .= "🏢 <b>SKPD Pengusul:</b> <b>" . htmlspecialchars($namaSkpd, ENT_QUOTES, 'UTF-8') . "</b>\n";
        $text .= "📄 <b>Nomor Surat:</b> <code>{$nomorSurat}</code>\n";
        $text .= "📅 <b>Tanggal Surat:</b> {$tglSurat}\n";
        $text .= "👤 <b>Operator:</b> " . htmlspecialchars($namaPengusul, ENT_QUOTES, 'UTF-8') . "\n";
        $text .= "📊 <b>Rincian Usulan:</b> <b>{$itemCount} Item</b>\n";
        $text .= "💰 <b>Total Nilai:</b> <b>{$nominal}</b>\n";
        $text .= "📝 <b>Perihal:</b> <i>\"{$perihal}\"</i>\n\n";

        if (!empty($surat->keterangan)) {
            $ket = htmlspecialchars($surat->keterangan, ENT_QUOTES, 'UTF-8');
            $text .= "💬 <b>Catatan SKPD:</b> <i>\"{$ket}\"</i>\n\n";
        }

        $text .= "⏱️ <i>Terkirim ke sistem pada: {$waktuKirim}</i>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "🔗 <b>Aksi Verifikator BKAD:</b>\n";
        $text .= "👉 <a href=\"{$linkVerifikasi}\">Klik di sini untuk Membuka &amp; Menelaah Berkas</a>\n\n";
        $text .= "<i>#SIPA #BKADTapin #Usulan{$tipeUpper}</i>";

        return $this->sendMessage($text);
    }

    /**
     * Mengirim notifikasi pengajuan item perorangan jika tidak melalui surat
     */
    public function sendItemNotification($item, $user, $tipe = 'SSH')
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Notifikasi Telegram dinonaktifkan.'];
        }

        $namaSkpd = 'SKPD Pengusul';
        if (!empty($item->id_skpd)) {
            $skpd = $this->CI->db->select('nama_skpd')->get_where('skpd', ['id' => (int)$item->id_skpd])->row();
            if ($skpd) $namaSkpd = $skpd->nama_skpd;
        }

        $tipeUpper  = strtoupper($tipe ?: ($item->tipe ?? 'SSH'));
        $uraian     = htmlspecialchars($item->uraian ?? '-', ENT_QUOTES, 'UTF-8');
        $kode       = htmlspecialchars($item->kode_usulan ?? '-', ENT_QUOTES, 'UTF-8');
        $harga      = rupiah($item->harga_usulan ?? 0);
        $satuan     = htmlspecialchars($item->satuan ?? '-', ENT_QUOTES, 'UTF-8');
        $prefixUrl  = strtolower($tipeUpper);
        $linkDetail = site_url("{$prefixUrl}/verifikasi/detail/{$item->id}");

        $text  = "🏛️ <b>NOTIFIKASI ITEM USULAN BARU SIPA</b>\n";
        $text .= "<i>Pemerintah Kabupaten Tapin</i>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $text .= "📌 <b>Instrumen:</b> <b>{$tipeUpper}</b>\n";
        $text .= "🏢 <b>SKPD:</b> <b>" . htmlspecialchars($namaSkpd, ENT_QUOTES, 'UTF-8') . "</b>\n";
        $text .= "🏷️ <b>Kode:</b> <code>{$kode}</code>\n";
        $text .= "📦 <b>Uraian:</b> <b>{$uraian}</b>\n";
        $text .= "💵 <b>Harga Usul:</b> <b>{$harga}</b> / {$satuan}\n\n";
        $text .= "🔗 <b>Akses Telaah:</b>\n";
        $text .= "👉 <a href=\"{$linkDetail}\">Lihat &amp; Verifikasi Usulan</a>";

        return $this->sendMessage($text);
    }

    /**
     * Menguji koneksi Bot Telegram dengan mengirim pesan tes
     * @param string|null $token
     * @param string|null $chatId
     * @return array
     */
    public function testConnection($token = null, $chatId = null)
    {
        $serverHost = $_SERVER['HTTP_HOST'] ?? 'sipa.bkadtapinkab.online';
        $waktu = date('d F Y, H:i:s') . ' WITA';

        $text  = "✅ <b>UJI COBA KONEKSI TELEGRAM BOT SIPA</b>\n";
        $text .= "<i>Pemerintah Kabupaten Tapin</i>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $text .= "Bot Telegram berhasil terhubung dengan Sistem Informasi Pengelolaan Aset (SIPA).\n\n";
        $text .= "🤖 <b>Status:</b> <b>AKTIF &amp; SIAP DIGUNAKAN</b>\n";
        $text .= "⏰ <b>Waktu Uji:</b> {$waktu}\n";
        $text .= "🖥️ <b>Alamat Server:</b> <code>{$serverHost}</code>\n\n";
        $text .= "Notifikasi otomatis akan dikirim ke saluran ini setiap kali operator SKPD mengirimkan usulan Standar Satuan Harga (SSH) atau Standar Biaya Umum (SBU) ke BKAD.\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "<i>BKAD Kabupaten Tapin &copy; " . date('Y') . "</i>";

        return $this->sendMessage($text, $chatId, $token);
    }

    /**
     * Eksekutor HTTP POST cURL dengan fallback ke stream_context
     */
    protected function postHttp($url, array $params)
    {
        $postData = json_encode($params);

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ]);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $raw = curl_exec($ch);
            $errNo = curl_errno($ch);
            $errMsg = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($errNo !== 0) {
                return [
                    'success' => false,
                    'message' => "cURL Error ({$errNo}): {$errMsg}",
                    'raw'     => null
                ];
            }

            $json = json_decode($raw, true);
            if ($httpCode === 200 && !empty($json['ok'])) {
                return ['success' => true, 'message' => 'Berhasil terkirim.', 'raw' => $json];
            }

            $apiDesc = $json['description'] ?? "HTTP Code {$httpCode}";
            return [
                'success' => false,
                'message' => "Telegram API Error: {$apiDesc}",
                'raw'     => $json
            ];
        }

        // Fallback jika ekstensi cURL dinonaktifkan
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($postData) . "\r\n",
                'content' => $postData,
                'timeout' => 8
            ]
        ];
        $context = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $context);

        if ($raw === false) {
            $err = error_get_last();
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Telegram: ' . ($err['message'] ?? 'Network error'),
                'raw'     => null
            ];
        }

        $json = json_decode($raw, true);
        if (!empty($json['ok'])) {
            return ['success' => true, 'message' => 'Berhasil terkirim.', 'raw' => $json];
        }

        return [
            'success' => false,
            'message' => 'Telegram API Error: ' . ($json['description'] ?? 'Gagal'),
            'raw'     => $json
        ];
    }
}
