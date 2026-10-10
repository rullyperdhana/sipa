<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Notifikasi WhatsApp SIPA Kabupaten Tapin
 */
class Wa extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['whatsapp', 'auth']);
        $this->load->model(['user_model', 'master_model', 'rkbmd_model', 'ssh_model']);
        $this->auth->restrict(); // Wajib login
    }

    /**
     * Endpoint AJAX untuk menyiapkan draf pesan dan tautan WhatsApp usulan
     */
    public function ajax_prepare()
    {
        if ($this->input->method() !== 'post') {
            return $this->output->set_status_header(405)->set_output(json_encode(['success' => false, 'message' => 'Method not allowed']));
        }

        $module     = $this->input->post('module', TRUE); // 'rkbmd', 'ssh', 'sbu'
        $id         = (int) $this->input->post('id');
        $actionType = $this->input->post('action_type', TRUE); // 'revisi', 'setuju', 'tolak'
        $customNote = $this->input->post('catatan', TRUE);

        if (!$id || !$module) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']));
        }

        $nomorUsulan = '-';
        $namaSkpd    = '-';
        $skpdId      = null;
        $modulJudul  = 'SIPA';
        $uraian      = '';
        $tahun       = date('Y');
        $link        = site_url('dashboard');
        $catatanDb   = '';

        if ($module === 'rkbmd') {
            $usulan = $this->rkbmd_model->findUsulan($id);
            if (!$usulan) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Usulan RKBMD tidak ditemukan.']));
            }
            $nomorUsulan = $usulan->nomor_usulan;
            $namaSkpd    = $usulan->nama_skpd;
            $skpdId      = $usulan->skpd_id;
            $modulJudul  = 'RKBMD ' . ucfirst($usulan->jenis_usulan);
            $tahun       = $usulan->tahun_anggaran;
            $catatanDb   = $usulan->catatan_verifikator;
            $link        = site_url("rkbmd/{$usulan->jenis_usulan}/detail/{$id}");

            // Tentukan template type
            if ($actionType === 'revisi') {
                $templateType = 'rkbmd_revisi';
            } elseif ($actionType === 'setuju') {
                $templateType = 'rkbmd_setuju';
            } elseif ($actionType === 'tolak') {
                $templateType = 'rkbmd_tolak';
            } else {
                $templateType = ($usulan->status === 'revisi') ? 'rkbmd_revisi' : (($usulan->status === 'disetujui') ? 'rkbmd_setuju' : 'custom');
            }

        } elseif (in_array($module, ['ssh', 'sbu'], TRUE)) {
            $usulan = $this->ssh_model->getUsulanById($id);
            if (!$usulan) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Usulan Standar Harga tidak ditemukan.']));
            }
            $nomorUsulan = $usulan->kode_usulan;
            $namaSkpd    = $usulan->nama_skpd;
            $skpdId      = $usulan->id_skpd;
            $modulJudul  = strtoupper($usulan->tipe);
            $uraian      = $usulan->uraian . (!empty($usulan->spesifikasi) ? ' (' . $usulan->spesifikasi . ')' : '');
            $tahun       = !empty($usulan->tahun_anggaran) ? $usulan->tahun_anggaran : (!empty($usulan->tahun) ? $usulan->tahun : date('Y'));
            $catatanDb   = $usulan->catatan_verifikator;
            $link        = site_url("{$module}/usulan");

            if ($actionType === 'revisi') {
                $templateType = 'standar_revisi';
            } elseif ($actionType === 'setuju') {
                $templateType = 'standar_diverifikasi';
            } elseif ($actionType === 'penetapan') {
                $templateType = 'standar_penetapan';
            } else {
                $templateType = ($usulan->status_proses === 'Direvisi') ? 'standar_revisi' : (($usulan->status_proses === 'Ditetapkan') ? 'standar_penetapan' : 'standar_diverifikasi');
            }
        } else {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Modul tidak dikenal.']));
        }

        // Cari nomor WhatsApp operator SKPD
        $kontak = $this->user_model->getOperatorContactBySkpd($skpdId);
        $noWa   = $kontak ? $kontak['no_wa'] : '';
        $namaOp = $kontak ? $kontak['nama'] : 'Operator SKPD';

        $catatanFinal = !empty($customNote) ? $customNote : $catatanDb;

        // Susun teks pesan WhatsApp
        $pesan = $this->whatsapp->formatMessage($templateType, [
            'nama_skpd'     => $namaSkpd,
            'nama_penerima' => $namaOp,
            'nomor_usulan'  => $nomorUsulan,
            'modul'         => $modulJudul,
            'catatan'       => $catatanFinal,
            'uraian'        => $uraian,
            'tahun'         => $tahun,
            'link'          => $link
        ]);

        $waUrl = !empty($noWa) ? $this->whatsapp->createUrl($noWa, $pesan) : '';
        $settings = $this->whatsapp->getSettings();

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'success'         => true,
                'no_wa'           => $noWa,
                'no_wa_clean'     => $this->whatsapp->normalizePhone($noWa),
                'nama_operator'   => $namaOp,
                'nama_skpd'       => $namaSkpd,
                'nomor_usulan'    => $nomorUsulan,
                'modul'           => $modulJudul,
                'pesan'           => $pesan,
                'wa_url'          => $waUrl,
                'gateway_enabled' => !empty($settings['wa_gateway_token'])
            ]));
    }

    /**
     * Endpoint AJAX kirim otomatis via WhatsApp Gateway API
     */
    public function ajax_send_gateway()
    {
        if ($this->input->method() !== 'post') {
            return $this->output->set_status_header(405)->set_output(json_encode(['success' => false, 'message' => 'Method not allowed']));
        }

        $phone   = $this->input->post('phone', TRUE);
        $message = $this->input->post('message');

        if (empty($phone) || empty($message)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Nomor HP atau isi pesan tidak boleh kosong.']));
        }

        $res = $this->whatsapp->sendViaGateway($phone, $message);
        return $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    /**
     * Halaman / Pengaturan WhatsApp Gateway (Admin Only)
     */
    public function settings()
    {
        $this->auth->restrict('admin');

        if ($this->input->method() === 'post') {
            $data = [
                'wa_mode'             => $this->input->post('wa_mode', TRUE) ?: 'direct',
                'wa_gateway_provider' => $this->input->post('wa_gateway_provider', TRUE) ?: 'fonnte',
                'wa_gateway_url'      => $this->input->post('wa_gateway_url', TRUE) ?: 'https://api.fonnte.com/send',
                'wa_gateway_token'    => trim($this->input->post('wa_gateway_token', TRUE) ?? ''),
                'wa_auto_send'        => $this->input->post('wa_auto_send') ? '1' : '0',
                'wa_sender_footer'    => trim($this->input->post('wa_sender_footer', TRUE) ?? 'BPKAD Kabupaten Tapin')
            ];

            $this->whatsapp->saveSettings($data);
            $this->session->set_flashdata('success', 'Pengaturan integrasi WhatsApp berhasil disimpan.');
            redirect('wa/settings');
        }

        $data = [
            'title'    => 'Pengaturan Integrasi WhatsApp',
            'settings' => $this->whatsapp->getSettings()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('master/wa_settings', $data);
        $this->load->view('templates/footer');
    }
}
