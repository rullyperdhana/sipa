<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WhatsApp Notification Service Library untuk SIPA Kabupaten Tapin
 * 
 * Mendukung 2 metode:
 * 1. Direct Click-to-Chat URL (https://api.whatsapp.com/send?phone=...&text=...)
 *    -> Gratis, 100% instan, tanpa langganan server API pihak ketiga.
 * 2. WhatsApp Gateway API (Fonnte / Generic HTTP POST Webhook)
 *    -> Pengiriman otomatis latar belakang (background headless) jika gateway dikonfigurasi.
 */
class Whatsapp
{
    protected $CI;
    protected $settingsTable = 'ex_settings';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    /**
     * Normalisasi nomor telepon ke format standar WhatsApp internasional (contoh: 6281234567890).
     */
    public function normalizePhone($phone)
    {
        if (empty($phone)) return '';

        // Hapus karakter non-angka
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);
        if (empty($clean)) return '';

        // Jika diawali '08', ganti menjadi '628'
        if (substr($clean, 0, 2) === '08') {
            return '62' . substr($clean, 1);
        }

        // Jika diawali '8', ganti menjadi '628'
        if (substr($clean, 0, 1) === '8') {
            return '62' . $clean;
        }

        // Jika diawali '0' (misal kode area), ganti menjadi '62'
        if (substr($clean, 0, 1) === '0') {
            return '62' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Membuat tautan langsung WhatsApp Web / WhatsApp Desktop / Mobile
     */
    public function createUrl($phone, $message)
    {
        $target = $this->normalizePhone($phone);
        if (empty($target)) return '';

        return 'https://api.whatsapp.com/send?phone=' . $target . '&text=' . rawurlencode($message);
    }

    /**
     * Menyusun draf pesan WhatsApp kedinasan yang rapi dan terstruktur (Markdown WhatsApp).
     *
     * @param string $type Jenis pemberitahuan (rkbmd_revisi, rkbmd_setuju, rkbmd_tolak, standar_revisi, standar_diverifikasi, standar_penetapan, custom)
     * @param array $data Data usulan, skpd, catatan, link
     * @return string
     */
    public function formatMessage($type, array $data = [])
    {
        $skpdNama     = !empty($data['nama_skpd']) ? $data['nama_skpd'] : 'SKPD Terkait';
        $penerimaNama = !empty($data['nama_penerima']) ? $data['nama_penerima'] : 'Bapak/Ibu Operator / Pengurus Barang';
        $nomorUsulan  = !empty($data['nomor_usulan']) ? $data['nomor_usulan'] : '-';
        $modulJudul   = !empty($data['modul']) ? $data['modul'] : 'Pengelolaan Aset Daerah';
        $catatan      = !empty($data['catatan']) ? trim($data['catatan']) : '';
        $link         = !empty($data['link']) ? $data['link'] : site_url('login');
        $uraian       = !empty($data['uraian']) ? $data['uraian'] : '';
        $tahun        = !empty($data['tahun']) ? $data['tahun'] : date('Y');

        $header  = "🏛️ *SIPA - PEMERINTAH KABUPATEN TAPIN*\n";
        $header .= "_(Sistem Informasi Pengelolaan Aset Daerah)_\n";
        $header .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        $salam  = "Yth. *{$penerimaNama}*\n";
        $salam .= "Unit Kerja: *{$skpdNama}*\n\n";

        $body = "";

        switch ($type) {
            case 'rkbmd_revisi':
                $body .= "⚠️ *PEMBERITAHUAN PERBAIKAN USULAN (REVISI)*\n\n";
                $body .= "Disampaikan bahwa usulan Perencanaan RKBMD berikut memerlukan perbaikan/revisi oleh SKPD:\n\n";
                $body .= "• *Nomor Usulan:* {$nomorUsulan}\n";
                $body .= "• *Instrumen:* {$modulJudul}\n";
                $body .= "• *Tahun Anggaran:* {$tahun}\n";
                $body .= "• *Status Saat Ini:* ⚠️ *PERLU REVISI SKPD*\n\n";
                if (!empty($catatan)) {
                    $body .= "📝 *Catatan Verifikator BPKAD:*\n";
                    $body .= "_{$catatan}_\n\n";
                }
                $body .= "Silakan login ke aplikasi SIPA untuk menyempurnakan data usulan tersebut:\n";
                $body .= "👉 {$link}\n\n";
                break;

            case 'rkbmd_setuju':
                $body .= "✅ *PEMBERITAHUAN USULAN DISETUJUI*\n\n";
                $body .= "Kabar baik, usulan Perencanaan RKBMD berikut telah *DISETUJUI* oleh BPKAD:\n\n";
                $body .= "• *Nomor Usulan:* {$nomorUsulan}\n";
                $body .= "• *Instrumen:* {$modulJudul}\n";
                $body .= "• *Tahun Anggaran:* {$tahun}\n";
                $body .= "• *Status Saat Ini:* ✅ *DISETUJUI*\n\n";
                if (!empty($catatan)) {
                    $body .= "📝 *Catatan Tambahan:*\n_{$catatan}_\n\n";
                }
                $body .= "Rincian dan rekap usulan dapat dilihat melalui tautan:\n";
                $body .= "👉 {$link}\n\n";
                break;

            case 'rkbmd_tolak':
                $body .= "❌ *PEMBERITAHUAN USULAN DITOLAK*\n\n";
                $body .= "Disampaikan bahwa usulan RKBMD berikut belum dapat disetujui:\n\n";
                $body .= "• *Nomor Usulan:* {$nomorUsulan}\n";
                $body .= "• *Instrumen:* {$modulJudul}\n";
                $body .= "• *Status:* ❌ *Ditolak*\n\n";
                if (!empty($catatan)) {
                    $body .= "📝 *Alasan Penolakan:*\n_{$catatan}_\n\n";
                }
                $body .= "Tautan usulan:\n👉 {$link}\n\n";
                break;

            case 'standar_revisi':
                $body .= "⚠️ *PERBAIKAN USULAN STANDAR HARGA ({$modulJudul})*\n\n";
                $body .= "Terdapat usulan Standar Harga/Biaya yang dikembalikan untuk diperbaiki:\n\n";
                $body .= "• *Kode Usulan:* {$nomorUsulan}\n";
                if (!empty($uraian)) $body .= "• *Uraian:* {$uraian}\n";
                $body .= "• *Status:* ⚠️ *DIREVISI*\n\n";
                if (!empty($catatan)) {
                    $body .= "📝 *Catatan Verifikator BPKAD:*\n";
                    $body .= "_{$catatan}_\n\n";
                }
                $body .= "Silakan perbaiki data & berkas survey usulan melalui tautan:\n";
                $body .= "👉 {$link}\n\n";
                break;

            case 'standar_tolak':
                $body .= "❌ *PEMBERITAHUAN USULAN STANDAR HARGA DITOLAK ({$modulJudul})*\n\n";
                $body .= "Disampaikan bahwa usulan Standar Harga/Biaya berikut belum dapat disetujui / ditolak oleh Tim Verifikator:\n\n";
                $body .= "• *Kode Usulan:* {$nomorUsulan}\n";
                if (!empty($uraian)) $body .= "• *Uraian:* {$uraian}\n";
                $body .= "• *Status:* ❌ *DITOLAK*\n\n";
                if (!empty($catatan)) {
                    $body .= "📝 *Alasan Penolakan:*\n";
                    $body .= "_{$catatan}_\n\n";
                }
                $body .= "Rincian usulan dapat dilihat melalui tautan:\n";
                $body .= "👉 {$link}\n\n";
                break;

            case 'standar_diverifikasi':
                $body .= "✅ *USULAN STANDAR HARGA DIVERIFIKASI*\n\n";
                $body .= "Usulan Standar Harga/Biaya berikut telah selesai diverifikasi oleh Tim BPKAD:\n\n";
                $body .= "• *Kode Usulan:* {$nomorUsulan}\n";
                if (!empty($uraian)) $body .= "• *Uraian:* {$uraian}\n";
                $body .= "• *Status:* ✅ *DIVERIFIKASI (Siap Penetapan SK)*\n\n";
                if (!empty($catatan)) {
                    $body .= "📝 *Catatan:*\n_{$catatan}_\n\n";
                }
                $body .= "Lihat detail:\n👉 {$link}\n\n";
                break;

            case 'standar_penetapan':
                $body .= "🎉 *PENETAPAN RESMI STANDAR SATUAN HARGA*\n\n";
                $body .= "Usulan harga telah *RESMI DITETAPKAN* menjadi Master Katalog Standar Harga Daerah Kab. Tapin:\n\n";
                $body .= "• *Kode Usulan:* {$nomorUsulan}\n";
                if (!empty($uraian)) $body .= "• *Uraian:* {$uraian}\n";
                $body .= "• *Status:* 🛡️ *DITETAPKAN RESMI (SK Bupati)*\n\n";
                $body .= "Lihat katalog master data:\n👉 {$link}\n\n";
                break;

            default:
                $body .= "📢 *PEMBERITAHUAN DINAS*\n\n";
                $body .= !empty($data['pesan']) ? $data['pesan'] . "\n\n" : "Terdapat pembaruan data usulan di SIPA.\n\n";
                $body .= "👉 {$link}\n\n";
                break;
        }

        $footer  = "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $footer .= "Terima kasih atas kerja samanya.\n";
        $footer .= "Bidang Pengelolaan Aset Daerah - BPKAD Kab. Tapin\n";
        $footer .= "Waktu: " . date('d/m/Y H:i') . " WITA";

        return $header . $salam . $body . $footer;
    }

    /**
     * Membaca pengaturan WhatsApp Gateway dari database.
     */
    public function getSettings()
    {
        $rows = $this->CI->db->get($this->settingsTable)->result();
        $settings = [
            'wa_mode'             => 'direct',
            'wa_gateway_provider' => 'fonnte',
            'wa_gateway_url'      => 'https://api.fonnte.com/send',
            'wa_gateway_token'    => '',
            'wa_auto_send'        => '0',
            'wa_sender_footer'    => 'BPKAD Kabupaten Tapin'
        ];

        foreach ($rows as $r) {
            $settings[$r->key] = $r->value;
        }

        return $settings;
    }

    /**
     * Menyimpan pengaturan WhatsApp Gateway.
     */
    public function saveSettings(array $data)
    {
        foreach ($data as $key => $val) {
            $exists = $this->CI->db->where('key', $key)->count_all_results($this->settingsTable);
            if ($exists > 0) {
                $this->CI->db->where('key', $key)->update($this->settingsTable, ['value' => (string)$val]);
            } else {
                $this->CI->db->insert($this->settingsTable, ['key' => $key, 'value' => (string)$val]);
            }
        }
        return true;
    }

    /**
     * Kirim notifikasi via WhatsApp Gateway API (jika diaktifkan oleh admin).
     */
    public function sendViaGateway($phone, $message)
    {
        $settings = $this->getSettings();
        $target = $this->normalizePhone($phone);

        if (empty($target)) {
            return ['success' => false, 'message' => 'Nomor WhatsApp tujuan tidak valid.'];
        }

        if (empty($settings['wa_gateway_token'])) {
            return ['success' => false, 'message' => 'Token API Gateway WhatsApp belum dikonfigurasi. Silakan gunakan tautan WhatsApp Web langsung.'];
        }

        $url = !empty($settings['wa_gateway_url']) ? $settings['wa_gateway_url'] : 'https://api.fonnte.com/send';

        $payload = [
            'target'  => $target,
            'message' => $message,
            'countryCode' => '62'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: " . $settings['wa_gateway_token']
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => "Gagal menghubungi Gateway API: {$err}"];
        }

        return [
            'success'   => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'response'  => $response,
            'message'   => ($httpCode >= 200 && $httpCode < 300) ? 'Pemberitahuan WhatsApp berhasil dikirim via Gateway.' : "Respon gateway HTTP {$httpCode}"
        ];
    }
}
