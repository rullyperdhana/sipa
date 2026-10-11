<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Ssh
 * Modul Standar Satuan Harga (SSH) untuk standarisasi harga satuan barang & material fisik.
 */
class Ssh extends Auth_Controller
{
    protected $tipe = 'SSH';
    protected $prefixUrl = 'ssh';
    protected $moduleTitle = 'Standar Satuan Harga (SSH)';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['ssh_model', 'master_model']);
        $this->load->library(['ssh_service', 'form_validation']);
        $this->load->helper(['app', 'form']);

        if (!can_access('ssh')) {
            show_error('Anda tidak memiliki hak akses untuk membuka modul Standar Satuan Harga (SSH). Hubungi Administrator.', 403, 'Akses Ditolak');
        }
    }

    /**
     * Index Router berdasarkan Role Pengguna
     */
    public function index()
    {
        $role = $this->currentUser->role;
        if (in_array($role, ['operator_skpd', 'skpd'], TRUE)) {
            redirect('ssh/usulan');
        } elseif ($role === 'verifikator') {
            redirect('ssh/verifikasi');
        } elseif (in_array($role, ['penetap', 'pimpinan'], TRUE)) {
            redirect('ssh/penetapan');
        } else {
            redirect('ssh/master_data');
        }
    }

    // =========================================================================
    // 1. MENU: USULAN SKPD (Role: operator_skpd, skpd, admin)
    // =========================================================================

    public function usulan()
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $thAktif = ($this->input->get('tahun') !== NULL) ? (int)$this->input->get('tahun', TRUE) : (function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027);

        $filter = [
            'tipe'          => $this->tipe,
            'status_proses' => $this->input->get('status', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $thAktif,
            'q'             => $this->input->get('q', TRUE)
        ];

        $skpdId = $this->currentUser->skpd_id;
        if ($this->currentUser->role === 'admin' && $this->input->get('skpd_id')) {
            $skpdId = (int) $this->input->get('skpd_id');
        }

        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe, $thAktif);
        $isJadwalBuka = $this->ssh_model->isJadwalBuka($this->tipe, $thAktif);

        // Ambil daftar Surat Usulan (Workflow Induk) & Rincian Item Usulan
        $suratList = $this->ssh_model->getSuratBySkpd($skpdId, $filter);
        $summarySurat = $this->ssh_model->getSummaryCountsSurat($this->currentUser, $this->tipe, $thAktif);

        $data = [
            'title'        => 'Usulan ' . $this->moduleTitle,
            'tipe'         => $this->tipe,
            'prefixUrl'    => $this->prefixUrl,
            'moduleTitle'  => $this->moduleTitle,
            'suratList'    => $suratList,
            'summarySurat' => $summarySurat,
            'list'         => $this->ssh_model->getUsulanBySkpd($skpdId, $filter),
            'summary'      => $this->ssh_model->getSummaryCounts($this->currentUser, $this->tipe),
            'filter'       => $filter,
            'kategori'     => $this->ssh_model->getKategoriList($this->tipe),
            'skpdList'     => ($this->currentUser->role === 'admin') ? $this->master_model->getAllSkpd() : [],
            'jadwalAktif'  => $jadwalAktif,
            'isJadwalBuka' => $isJadwalBuka
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_index', $data);
        $this->load->view('templates/footer');
    }

    // =========================================================================
    // 1.B PENGELOLAAN SURAT USULAN (SKPD & ADMIN)
    // =========================================================================

    /**
     * Form Buat Surat Usulan Baru (Wajib Lampirkan Surat Resmi PDF)
     */
    public function surat_tambah()
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $thAktif = function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027;

        $isJadwalBuka = $this->ssh_model->isJadwalBuka($this->tipe, $thAktif);
        if (!in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE) && !$isJadwalBuka) {
            $this->session->set_flashdata('warning', "Pengusulan {$this->moduleTitle} untuk TA {$thAktif} saat ini belum dibuka atau telah ditutup. Silakan menunggu pembuatan/pembukaan jadwal pengusulan oleh BPKAD.");
            redirect("{$this->prefixUrl}/usulan");
            return;
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nomor_surat', 'Nomor Surat', 'required|trim');
            $this->form_validation->set_rules('tanggal_surat', 'Tanggal Surat', 'required|trim');
            $this->form_validation->set_rules('perihal', 'Perihal Surat', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
                redirect("{$this->prefixUrl}/surat/tambah");
                return;
            }

            if (empty($_FILES['file_surat']['name'])) {
                $this->session->set_flashdata('danger', 'Berkas Surat Usulan resmi (format PDF) wajib diunggah.');
                redirect("{$this->prefixUrl}/surat/tambah");
                return;
            }

            $uploadResult = $this->ssh_service->handleFileUpload('file_surat');
            if (isset($uploadResult['error'])) {
                $this->session->set_flashdata('danger', 'Gagal upload berkas Surat Usulan: ' . $uploadResult['error']);
                redirect("{$this->prefixUrl}/surat/tambah");
                return;
            }

            $postData = [
                'nomor_surat'    => $this->input->post('nomor_surat', TRUE),
                'tanggal_surat'  => $this->input->post('tanggal_surat', TRUE),
                'perihal'        => $this->input->post('perihal', TRUE),
                'keterangan'     => $this->input->post('keterangan', TRUE),
                'tipe'           => $this->tipe,
                'file_surat'     => $uploadResult['fileName'],
                'file_nama_asli' => $uploadResult['origName'],
                'tahun_anggaran' => (int)($this->input->post('tahun_anggaran') ?: $thAktif),
                'id_skpd'        => ($this->currentUser->role === 'admin' && $this->input->post('id_skpd')) ? (int)$this->input->post('id_skpd') : $this->currentUser->skpd_id
            ];

            $res = $this->ssh_model->insertSurat($postData, $this->currentUser);
            if ($res['success']) {
                $this->session->set_flashdata('success', "Surat Usulan resmi <strong>{$res['nomor_surat']}</strong> berhasil dibuat. Silakan tambahkan rincian usulan item {$this->tipe} Anda di bawah ini.");
                redirect("{$this->prefixUrl}/surat/detail/{$res['id']}");
                return;
            } else {
                $this->session->set_flashdata('danger', $res['message'] ?? 'Gagal membuat Surat Usulan.');
                redirect("{$this->prefixUrl}/surat/tambah");
                return;
            }
        }

        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe, $thAktif);

        $data = [
            'title'       => 'Buat Surat Usulan ' . $this->moduleTitle,
            'mode'        => 'tambah',
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'thAktif'     => $thAktif,
            'jadwalAktif' => $jadwalAktif,
            'skpdList'    => ($this->currentUser->role === 'admin') ? $this->master_model->getAllSkpd() : []
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/surat_form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Form Edit Surat Usulan
     */
    public function surat_edit($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $surat = $this->ssh_model->getSuratById($id);
        if (!$surat || $surat->tipe !== $this->tipe) {
            $this->session->set_flashdata('danger', 'Data Surat Usulan tidak ditemukan.');
            redirect("{$this->prefixUrl}/usulan");
            return;
        }

        if ($this->currentUser->role !== 'admin' && (int)$surat->id_skpd !== (int)$this->currentUser->skpd_id) {
            $this->session->set_flashdata('danger', 'Akses ditolak: Surat Usulan ini milik SKPD lain.');
            redirect("{$this->prefixUrl}/usulan");
            return;
        }

        if (!in_array($surat->status_proses, ['Draft', 'Direvisi'], TRUE)) {
            $this->session->set_flashdata('danger', "Surat Usulan berstatus '{$surat->status_proses}' tidak dapat diedit karena sudah diajukan/terkunci.");
            redirect("{$this->prefixUrl}/surat/detail/{$id}");
            return;
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nomor_surat', 'Nomor Surat', 'required|trim');
            $this->form_validation->set_rules('tanggal_surat', 'Tanggal Surat', 'required|trim');
            $this->form_validation->set_rules('perihal', 'Perihal Surat', 'required|trim');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
                redirect("{$this->prefixUrl}/surat/edit/{$id}");
                return;
            }

            $updateData = [
                'nomor_surat'   => $this->input->post('nomor_surat', TRUE),
                'tanggal_surat' => $this->input->post('tanggal_surat', TRUE),
                'perihal'       => $this->input->post('perihal', TRUE),
                'keterangan'    => $this->input->post('keterangan', TRUE)
            ];

            if (!empty($_FILES['file_surat']['name'])) {
                $uploadResult = $this->ssh_service->handleFileUpload('file_surat');
                if (isset($uploadResult['error'])) {
                    $this->session->set_flashdata('danger', 'Gagal upload Surat: ' . $uploadResult['error']);
                    redirect("{$this->prefixUrl}/surat/edit/{$id}");
                    return;
                }
                if ($uploadResult['hasFile']) {
                    $updateData['file_surat']     = $uploadResult['fileName'];
                    $updateData['file_nama_asli'] = $uploadResult['origName'];
                }
            }

            $res = $this->ssh_model->updateSurat($id, $updateData, $this->currentUser);
            $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
            redirect("{$this->prefixUrl}/surat/detail/{$id}");
            return;
        }

        $thAktif = function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027;
        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe, $thAktif);

        $data = [
            'title'       => 'Edit Surat Usulan - ' . $surat->nomor_surat,
            'mode'        => 'edit',
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'surat'       => $surat,
            'thAktif'     => $thAktif,
            'jadwalAktif' => $jadwalAktif,
            'skpdList'    => ($this->currentUser->role === 'admin') ? $this->master_model->getAllSkpd() : []
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/surat_form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Detail Surat Usulan beserta Daftar Rincian Item di Dalamnya
     */
    public function surat_detail($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin', 'verifikator', 'penetap', 'pimpinan']);

        $surat = $this->ssh_model->getSuratById($id);
        if (!$surat || $surat->tipe !== $this->tipe) {
            $this->session->set_flashdata('danger', 'Data Surat Usulan tidak ditemukan.');
            redirect("{$this->prefixUrl}/usulan");
            return;
        }

        if (in_array($this->currentUser->role, ['operator_skpd', 'skpd'], TRUE) && (int)$surat->id_skpd !== (int)$this->currentUser->skpd_id) {
            $this->session->set_flashdata('danger', 'Akses ditolak: Surat Usulan ini milik SKPD lain.');
            redirect("{$this->prefixUrl}/usulan");
            return;
        }

        $items = $this->ssh_model->getItemsBySuratId($id);
        $thAktif = (int) $surat->tahun_anggaran;
        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe, $thAktif);
        $isJadwalBuka = $this->ssh_model->isJadwalBuka($this->tipe, $thAktif);

        $data = [
            'title'        => 'Detail Surat Usulan - ' . $surat->nomor_surat,
            'tipe'         => $this->tipe,
            'prefixUrl'    => $this->prefixUrl,
            'moduleTitle'  => $this->moduleTitle,
            'surat'        => $surat,
            'items'        => $items,
            'jadwalAktif'  => $jadwalAktif,
            'isJadwalBuka' => $isJadwalBuka
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/surat_detail', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Kirim Seluruh Usulan dalam 1 Surat ke BPKAD
     */
    public function surat_kirim($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);
        $res = $this->ssh_model->kirimSurat($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect("{$this->prefixUrl}/surat/detail/{$id}");
    }

    /**
     * Hapus Surat Usulan beserta Seluruh Rincian Item di Dalamnya
     */
    public function surat_hapus($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);
        $res = $this->ssh_model->deleteSurat($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect("{$this->prefixUrl}/usulan");
    }

    /**
     * Unduh Berkas Surat Usulan PDF
     */
    public function surat_download($id)
    {
        $surat = $this->ssh_model->getSuratById($id);
        if (!$surat || empty($surat->file_surat)) {
            show_404();
        }

        if (in_array($this->currentUser->role, ['operator_skpd', 'skpd'], TRUE) && (int)$surat->id_skpd !== (int)$this->currentUser->skpd_id) {
            show_error('Akses ditolak: Surat ini milik SKPD lain.', 403, 'Akses Ditolak');
        }

        $filePath = FCPATH . 'uploads/ssh_sbu/' . $surat->file_surat;
        if (!file_exists($filePath)) {
            $this->session->set_flashdata('danger', 'Berkas Surat Usulan fisik tidak ditemukan di server.');
            redirect($this->agent->referrer() ?: "{$this->prefixUrl}/usulan");
        }

        $this->load->helper('download');
        force_download($surat->file_nama_asli ?: $surat->file_surat, file_get_contents($filePath));
    }

    /**
     * Pratinjau Inline Surat Usulan PDF di Browser
     */
    public function surat_preview($id)
    {
        $surat = $this->ssh_model->getSuratById($id);
        if (!$surat || empty($surat->file_surat)) {
            show_404();
        }

        if (in_array($this->currentUser->role, ['operator_skpd', 'skpd'], TRUE) && (int)$surat->id_skpd !== (int)$this->currentUser->skpd_id) {
            show_error('Akses ditolak: Surat ini milik SKPD lain.', 403, 'Akses Ditolak');
        }

        $filePath = FCPATH . 'uploads/ssh_sbu/' . $surat->file_surat;
        if (!file_exists($filePath)) {
            show_404();
        }

        $ext = strtolower(pathinfo($surat->file_surat, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png'
        ];
        $mime = $mimeMap[$ext] ?? 'application/pdf';
        $clientName = $surat->file_nama_asli ?: $surat->file_surat;

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . addslashes($clientName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');

        readfile($filePath);
        exit;
    }

    /**
     * Endpoint JSON Metadata Surat Usulan
     */
    public function api_surat($id)
    {
        $surat = $this->ssh_model->getSuratById($id);
        if (!$surat) {
            return $this->output->set_status_header(404)->set_output(json_encode(['success' => FALSE, 'message' => 'Surat usulan tidak ditemukan.']));
        }

        if (in_array($this->currentUser->role, ['operator_skpd', 'skpd'], TRUE) && (int)$surat->id_skpd !== (int)$this->currentUser->skpd_id) {
            return $this->output->set_status_header(403)->set_output(json_encode(['success' => FALSE, 'message' => 'Akses ditolak oleh RLS.']));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'        => TRUE,
            'id'             => $surat->id,
            'nomor_surat'    => $surat->nomor_surat,
            'tanggal_surat'  => tanggal_id($surat->tanggal_surat),
            'perihal'        => $surat->perihal,
            'nama_skpd'      => $surat->nama_skpd ?? '',
            'total_item'     => (int)$surat->total_item,
            'total_nominal'  => rupiah($surat->total_nominal),
            'status_proses'  => $surat->status_proses,
            'preview_url'    => site_url("{$this->prefixUrl}/surat/preview/{$surat->id}"),
            'download_url'   => site_url("{$this->prefixUrl}/surat/download/{$surat->id}")
        ]));
    }

    // =========================================================================
    // 1.C FORM CRUD: RINCIAN USULAN ITEM SSH
    // =========================================================================

    /**
     * Form CRUD: Tambah Rincian Usulan Item Baru ke Dalam Surat Usulan
     */
    public function tambah($suratId = NULL)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $thAktif = function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027;

        // Pengecekan Jadwal Pengusulan Aktif (Khusus role SKPD)
        $isJadwalBuka = $this->ssh_model->isJadwalBuka($this->tipe, $thAktif);
        if (!in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE) && !$isJadwalBuka) {
            $this->session->set_flashdata('warning', "Pengusulan {$this->moduleTitle} untuk TA {$thAktif} saat ini belum dibuka atau telah ditutup. Silakan menunggu pembuatan/pembukaan jadwal pengusulan oleh BPKAD.");
            redirect("{$this->prefixUrl}/usulan");
            return;
        }

        // Resolusi Surat Usulan Induk
        $suratId = $suratId ?: (int)$this->input->get('surat_id', TRUE);
        if (!$suratId && $this->input->method() === 'post') {
            $suratId = (int)$this->input->post('surat_id', TRUE);
        }

        $suratInduk = NULL;
        if ($suratId) {
            $suratInduk = $this->ssh_model->getSuratById($suratId);
            if (!$suratInduk || $suratInduk->tipe !== $this->tipe) {
                $this->session->set_flashdata('danger', 'Surat Usulan tidak ditemukan.');
                redirect("{$this->prefixUrl}/usulan");
                return;
            }
            if ($this->currentUser->role !== 'admin' && (int)$suratInduk->id_skpd !== (int)$this->currentUser->skpd_id) {
                $this->session->set_flashdata('danger', 'Akses Ditolak: Surat Usulan ini milik SKPD lain.');
                redirect("{$this->prefixUrl}/usulan");
                return;
            }
            if (!in_array($suratInduk->status_proses, ['Draft', 'Direvisi'], TRUE)) {
                $this->session->set_flashdata('danger', "Surat Usulan ini sudah berstatus '{$suratInduk->status_proses}' dan terkunci sehingga rincian item tidak dapat ditambah lagi.");
                redirect("{$this->prefixUrl}/surat/detail/{$suratId}");
                return;
            }
        } else {
            // Wajib lampirkan Surat Usulan terlebih dahulu sebelum input rincian item
            $this->session->set_flashdata('warning', "Sesuai ketentuan, sebelum menambahkan rincian usulan {$this->moduleTitle}, SKPD wajib membuat dan mengunggah Surat Usulan resmi terlebih dahulu.");
            redirect("{$this->prefixUrl}/surat/tambah");
            return;
        }

        if ($this->input->method() === 'post') {
            $post = $this->input->post();
            $post['tipe'] = $this->tipe;
            $post['surat_id'] = $suratId;

            $thPost = !empty($post['tahun_anggaran']) ? (int)$post['tahun_anggaran'] : $thAktif;
            if (!in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE) && !$this->ssh_model->isJadwalBuka($this->tipe, $thPost)) {
                $this->session->set_flashdata('danger', "Akses Ditolak: Jadwal pengusulan {$this->moduleTitle} untuk TA {$thPost} sedang DITUTUP oleh Administrator BPKAD.");
                redirect("{$this->prefixUrl}/surat/detail/{$suratId}");
                return;
            }

            $validation = $this->ssh_service->validateInput($post);
            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect("{$this->prefixUrl}/tambah/{$suratId}");
                return;
            }

            // Validasi 3 Berkas Bukti Survey Harga Pasar / Brosur Resmi (Wajib Terisi)
            $fileSlots = [
                'file_lampiran'   => 'Bukti Survey 1 / Brosur Resmi 1',
                'file_lampiran_2' => 'Bukti Survey 2 / Brosur Resmi 2',
                'file_lampiran_3' => 'Bukti Survey 3 / Brosur Resmi 3',
            ];

            $missingFiles = [];
            foreach ($fileSlots as $slotField => $slotLabel) {
                if (empty($_FILES[$slotField]['name'])) {
                    $missingFiles[] = $slotLabel;
                }
            }

            if (!empty($missingFiles)) {
                $this->session->set_flashdata('danger', 'Seluruh 3 Bukti Survey Harga Pasar / Brosur Resmi wajib diunggah. Berkas yang belum diunggah: <strong>' . implode(', ', $missingFiles) . '</strong>.');
                redirect("{$this->prefixUrl}/tambah/{$suratId}");
                return;
            }

            // Proses upload ketiga berkas
            $uploadedFiles = [];
            foreach ($fileSlots as $slotField => $slotLabel) {
                $uploadResult = $this->ssh_service->handleFileUpload($slotField);
                if (isset($uploadResult['error'])) {
                    foreach ($uploadedFiles as $up) {
                        @unlink(FCPATH . 'uploads/ssh_sbu/' . $up['fileName']);
                    }
                    $this->session->set_flashdata('danger', "Gagal upload {$slotLabel}: " . $uploadResult['error']);
                    redirect("{$this->prefixUrl}/tambah/{$suratId}");
                    return;
                }
                $uploadedFiles[$slotField] = $uploadResult;
            }

            $postData = $validation['cleanData'];
            $postData['tipe'] = $this->tipe;
            $postData['surat_id'] = $suratId;
            $postData['file_lampiran']    = $uploadedFiles['file_lampiran']['fileName'];
            $postData['file_nama_asli']   = $uploadedFiles['file_lampiran']['origName'];
            $postData['file_lampiran_2']  = $uploadedFiles['file_lampiran_2']['fileName'];
            $postData['file_nama_asli_2'] = $uploadedFiles['file_lampiran_2']['origName'];
            $postData['file_lampiran_3']  = $uploadedFiles['file_lampiran_3']['fileName'];
            $postData['file_nama_asli_3'] = $uploadedFiles['file_lampiran_3']['origName'];

            $result = $this->ssh_model->insertUsulan($postData, $this->currentUser);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Rincian usulan {$this->tipe} <strong>{$result['kode_usulan']}</strong> berhasil ditambahkan ke Surat Usulan No. <strong>{$suratInduk->nomor_surat}</strong>.");
                redirect("{$this->prefixUrl}/surat/detail/{$suratId}");
            } else {
                $this->session->set_flashdata('danger', $result['message'] ?? 'Gagal menyimpan usulan.');
                redirect("{$this->prefixUrl}/tambah/{$suratId}");
            }
            return;
        }

        // Cek jika prefill dari item master katalog 2027
        $masterItem = NULL;
        if ($masterId = (int) $this->input->get('master_id')) {
            $masterItem = $this->ssh_model->getMasterById($masterId);
        }

        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe, $thAktif);

        $data = [
            'title'       => 'Tambah Rincian Usulan ' . $this->moduleTitle,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'suratInduk'  => $suratInduk,
            'suratId'     => $suratId,
            'kategori'    => $this->ssh_model->getKategoriList($this->tipe),
            'satuan'      => $this->ssh_model->getSatuanSsh(),
            'masterItem'  => $masterItem,
            'jadwalAktif' => $jadwalAktif
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Form CRUD: Edit Usulan SSH
     */
    public function edit($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item || $item->tipe !== $this->tipe) {
            $this->session->set_flashdata('danger', "Data usulan {$this->tipe} tidak ditemukan atau Anda tidak memiliki hak akses.");
            redirect("{$this->prefixUrl}/usulan");
        }

        // Security / Schedule Guard: Operator SKPD dilarang edit usulan pada jadwal yang ditutup
        $thUsulan = !empty($item->tahun_anggaran) ? (int)$item->tahun_anggaran : (function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027);
        if (!in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE) && !$this->ssh_model->isJadwalBuka($this->tipe, $thUsulan)) {
            $this->session->set_flashdata('danger', "Akses Ditolak: Jadwal pengusulan {$this->moduleTitle} TA {$thUsulan} saat ini sedang DITUTUP oleh Administrator BPKAD. Usulan tidak dapat diubah.");
            redirect("{$this->prefixUrl}/usulan");
            return;
        }

        if (!in_array($item->status_proses, ['Draft', 'Direvisi'], TRUE)) {
            $this->session->set_flashdata('danger', "Usulan berstatus '{$item->status_proses}' tidak dapat diedit karena sudah terkunci.");
            redirect("{$this->prefixUrl}/usulan");
        }

        if ($this->input->method() === 'post') {
            $post = $this->input->post();
            $post['tipe'] = $this->tipe;

            $validation = $this->ssh_service->validateInput($post);
            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect("{$this->prefixUrl}/edit/{$id}");
            }

            $updateData = $validation['cleanData'];
            $updateData['tipe'] = $this->tipe;

            $fileSlots = [
                'file_lampiran'   => ['label' => 'Bukti Survey 1 / Brosur Resmi 1', 'file' => 'file_lampiran', 'orig' => 'file_nama_asli'],
                'file_lampiran_2' => ['label' => 'Bukti Survey 2 / Brosur Resmi 2', 'file' => 'file_lampiran_2', 'orig' => 'file_nama_asli_2'],
                'file_lampiran_3' => ['label' => 'Bukti Survey 3 / Brosur Resmi 3', 'file' => 'file_lampiran_3', 'orig' => 'file_nama_asli_3'],
            ];

            $missingFiles = [];
            foreach ($fileSlots as $slotField => $cfg) {
                $hasOld = !empty($item->{$cfg['file']});
                $hasNew = !empty($_FILES[$slotField]['name']);
                if (!$hasOld && !$hasNew) {
                    $missingFiles[] = $cfg['label'];
                }
            }

            if (!empty($missingFiles)) {
                $this->session->set_flashdata('danger', 'Ketiga Bukti Survey Harga Pasar / Brosur Resmi wajib terisi. Berkas yang belum ada: <strong>' . implode(', ', $missingFiles) . '</strong>.');
                redirect("{$this->prefixUrl}/edit/{$id}");
            }

            foreach ($fileSlots as $slotField => $cfg) {
                if (!empty($_FILES[$slotField]['name'])) {
                    $uploadResult = $this->ssh_service->handleFileUpload($slotField);
                    if (isset($uploadResult['error'])) {
                        $this->session->set_flashdata('danger', "Gagal upload {$cfg['label']}: " . $uploadResult['error']);
                        redirect("{$this->prefixUrl}/edit/{$id}");
                    }
                    if ($uploadResult['hasFile']) {
                        $updateData[$cfg['file']] = $uploadResult['fileName'];
                        $updateData[$cfg['orig']] = $uploadResult['origName'];
                    }
                }
            }

            $res = $this->ssh_model->updateUsulan($id, $updateData, $this->currentUser);
            if ($res['success']) {
                $this->session->set_flashdata('success', $res['message']);
                if (!empty($item->surat_id)) {
                    redirect("{$this->prefixUrl}/surat/detail/{$item->surat_id}");
                } else {
                    redirect("{$this->prefixUrl}/usulan");
                }
            } else {
                $this->session->set_flashdata('danger', $res['message']);
                redirect("{$this->prefixUrl}/edit/{$id}");
            }
        }

        $suratInduk = !empty($item->surat_id) ? $this->ssh_model->getSuratById($item->surat_id) : NULL;

        $data = [
            'title'       => 'Edit Usulan ' . $this->tipe . ' - ' . $item->kode_usulan,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'item'        => $item,
            'suratInduk'  => $suratInduk,
            'suratId'     => $item->surat_id,
            'kategori'    => $this->ssh_model->getKategoriList($this->tipe),
            'satuan'      => $this->ssh_model->getSatuanSsh()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_form', $data);
        $this->load->view('templates/footer');
    }

    public function kirim($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);
        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        $suratId = $item ? $item->surat_id : NULL;

        $res = $this->ssh_model->kirimUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);

        if ($suratId) {
            redirect("{$this->prefixUrl}/surat/detail/{$suratId}");
        } else {
            redirect("{$this->prefixUrl}/usulan");
        }
    }

    public function hapus($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);
        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        $suratId = $item ? $item->surat_id : NULL;

        $res = $this->ssh_model->deleteUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);

        if ($suratId) {
            redirect("{$this->prefixUrl}/surat/detail/{$suratId}");
        } else {
            redirect("{$this->prefixUrl}/usulan");
        }
    }

    // =========================================================================
    // 2. MENU: VERIFIKASI USULAN SSH (Role: verifikator, admin)
    // =========================================================================

    public function verifikasi()
    {
        $this->_restrictRoles(['verifikator', 'admin']);

        $rawStatus = $this->input->get('status', TRUE);
        $statusProses = ($rawStatus !== NULL) ? $rawStatus : 'Diajukan';

        $filter = [
            'tipe'          => $this->tipe,
            'status_proses' => $statusProses,
            'id_skpd'       => $this->input->get('skpd_id', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $this->input->get('tahun', TRUE),
            'q'             => $this->input->get('q', TRUE)
        ];

        $data = [
            'title'        => 'Verifikasi Usulan ' . $this->moduleTitle,
            'tipe'         => $this->tipe,
            'prefixUrl'    => $this->prefixUrl,
            'moduleTitle'  => $this->moduleTitle,
            'suratList'    => $this->ssh_model->getSuratVerifikasi($filter),
            'summarySurat' => $this->ssh_model->getSummaryCountsSurat($this->currentUser, $this->tipe, $thAktif),
            'list'         => $this->ssh_model->getUsulanVerifikasi($filter),
            'summary'      => $this->ssh_model->getSummaryCounts($this->currentUser, $this->tipe),
            'filter'       => $filter,
            'kategori'     => $this->ssh_model->getKategoriList($this->tipe),
            'skpdList'     => $this->master_model->getAllSkpd()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/verifikasi_index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Halaman Verifikasi Berbasis Surat Usulan (Periksa Surat & Rincian Item di Dalamnya)
     */
    public function verifikasi_surat($id)
    {
        $this->_restrictRoles(['verifikator', 'admin']);

        $surat = $this->ssh_model->getSuratById($id);
        if (!$surat || $surat->tipe !== $this->tipe) {
            $this->session->set_flashdata('danger', 'Data Surat Usulan tidak ditemukan.');
            redirect("{$this->prefixUrl}/verifikasi");
            return;
        }

        $items = $this->ssh_model->getItemsBySuratId($id);

        $data = [
            'title'       => 'Verifikasi Surat Usulan - ' . $surat->nomor_surat,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'surat'       => $surat,
            'items'       => $items,
            'kategori'    => $this->ssh_model->getKategoriList($this->tipe)
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/verifikasi_surat', $data);
        $this->load->view('templates/footer');
    }

    public function proses_verifikasi($id)
    {
        $this->_restrictRoles(['verifikator', 'admin']);
        if ($this->input->method() !== 'post') show_404();

        $aksi = $this->input->post('aksi', TRUE);
        $catatan = $this->input->post('catatan_verifikator', TRUE);
        $rawHarga = str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], $this->input->post('harga_ditetapkan', TRUE) ?? '');
        $hargaDitetapkan = is_numeric($rawHarga) && $rawHarga > 0 ? (float) $rawHarga : NULL;

        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        $suratId = $item ? $item->surat_id : NULL;

        $res = $this->ssh_model->verifikasiUsulan($id, $aksi, $catatan, $hargaDitetapkan, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);

        if ($suratId && ($this->input->post('redirect_surat') || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'verifikasi/surat') !== FALSE))) {
            redirect("{$this->prefixUrl}/verifikasi/surat/{$suratId}");
        } else {
            redirect("{$this->prefixUrl}/verifikasi");
        }
    }

    // =========================================================================
    // 3. MENU: PENETAPAN HARGA SSH (Role: penetap, pimpinan, admin)
    // =========================================================================

    public function penetapan()
    {
        $this->_restrictRoles(['penetap', 'pimpinan', 'admin']);

        $filter = [
            'tipe'          => 'SSH',
            'status_proses' => $this->input->get('status', TRUE) ?: 'Diverifikasi',
            'id_skpd'       => $this->input->get('skpd_id', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $this->input->get('tahun', TRUE),
            'q'             => $this->input->get('q', TRUE)
        ];

        $data = [
            'title'       => 'Penetapan ' . $this->moduleTitle,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'list'        => $this->ssh_model->getUsulanPenetapan($filter),
            'summary'     => $this->ssh_model->getSummaryCounts($this->currentUser, $this->tipe),
            'filter'      => $filter,
            'kategori'    => $this->ssh_model->getKategoriList($this->tipe),
            'skpdList'    => $this->master_model->getAllSkpd()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/penetapan_index', $data);
        $this->load->view('templates/footer');
    }

    public function proses_penetapan($id)
    {
        $this->_restrictRoles(['penetap', 'pimpinan', 'admin']);
        if ($this->input->method() !== 'post') show_404();

        $res = $this->ssh_model->tetapkanUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/penetapan');
    }

    // =========================================================================
    // 4. MENU: MASTER DATA SSH (Role: SEMUA PENGGUNA)
    // =========================================================================

    public function master_data()
    {
        $page = max(1, (int) $this->input->get('page'));
        $perPage = min(100, max(10, (int) ($this->input->get('per_page') ?: 25)));
        $offset = ($page - 1) * $perPage;

        $filter = [
            'tipe'     => $this->tipe,
            'kategori' => $this->input->get('kategori', TRUE),
            'tahun'    => $this->input->get('tahun', TRUE) ?: 2027,
            'q'        => $this->input->get('q', TRUE)
        ];

        $totalRows = $this->ssh_model->countMasterData($filter);
        $totalPages = max(1, ceil($totalRows / $perPage));
        $list = $this->ssh_model->getMasterDataPaginated($filter, $perPage, $offset);

        $data = [
            'title'       => 'Master Data ' . $this->moduleTitle,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'list'        => $list,
            'filter'      => $filter,
            'kategori'    => $this->ssh_model->getDistinctKategoriMaster($this->tipe, $filter['tahun']),
            'tahunList'   => $this->ssh_model->getDistinctTahunMaster($this->tipe),
            'page'        => $page,
            'perPage'     => $perPage,
            'totalRows'   => $totalRows,
            'totalPages'  => $totalPages
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/master_index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Cetak Lembar Dokumen Katalog Resmi Master Data SSH
     */
    public function cetak_master()
    {
        $mode = $this->input->get('mode', TRUE) ?: 'all';
        $filter = [
            'tipe'     => $this->tipe,
            'kategori' => $this->input->get('kategori', TRUE),
            'tahun'    => $this->input->get('tahun', TRUE) ?: (function_exists('get_tahun_anggaran') ? get_tahun_anggaran() : 2027),
            'q'        => $this->input->get('q', TRUE)
        ];

        $totalFiltered = $this->ssh_model->countMasterData($filter);

        if ($mode === 'current_page') {
            $page = max(1, (int) $this->input->get('page'));
            $perPage = min(100, max(10, (int) ($this->input->get('per_page') ?: 25)));
            $offset = ($page - 1) * $perPage;
            $items = $this->ssh_model->getMasterDataPaginated($filter, $perPage, $offset);
            $printLimit = count($items);
            $startNumber = $offset + 1;
        } else {
            // Batas maksimal print untuk performa browser & kejelasan dokumen
            $limitParam = (int) $this->input->get('limit');
            $printLimit = ($limitParam > 0) ? min(1000, $limitParam) : 500;
            $items = $this->ssh_model->getMasterDataPaginated($filter, $printLimit, 0);
            $startNumber = 1;
        }

        $data = [
            'title'         => 'Katalog Resmi ' . $this->moduleTitle . ' TA ' . $filter['tahun'],
            'tipe'          => $this->tipe,
            'prefixUrl'     => $this->prefixUrl,
            'moduleTitle'   => $this->moduleTitle,
            'list'          => $items,
            'filter'        => $filter,
            'mode'          => $mode,
            'totalFiltered' => $totalFiltered,
            'printLimit'    => $printLimit,
            'startNumber'   => $startNumber,
            'printedAt'     => date('Y-m-d H:i:s')
        ];

        $this->load->view('ssh/cetak_master', $data);
    }

    // =========================================================================
    // JADWAL PENGUSULAN STANDAR HARGA
    // =========================================================================

    public function jadwal()
    {
        $this->_restrictRoles(['admin', 'verifikator']);

        $filter = [
            'tipe'   => $this->tipe,
            'tahun'  => $this->input->get('tahun', TRUE),
            'status' => $this->input->get('status', TRUE)
        ];

        $data = [
            'title'       => 'Jadwal Pengusulan ' . $this->moduleTitle,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'list'        => $this->ssh_model->getJadwalList($filter),
            'filter'      => $filter
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/jadwal_index', $data);
        $this->load->view('templates/footer');
    }

    public function simpan_jadwal()
    {
        $this->_restrictRoles(['admin', 'verifikator']);
        if ($this->input->method() !== 'post') show_404();

        $id = $this->input->post('id') ? (int) $this->input->post('id') : NULL;
        $postData = [
            'tipe'            => $this->input->post('tipe', TRUE) ?: $this->tipe,
            'tahun_anggaran'  => (int) $this->input->post('tahun_anggaran'),
            'nama_jadwal'     => $this->input->post('nama_jadwal', TRUE),
            'tanggal_mulai'   => $this->input->post('tanggal_mulai', TRUE),
            'tanggal_selesai' => $this->input->post('tanggal_selesai', TRUE),
            'status'          => $this->input->post('status', TRUE) ?: 'buka',
            'keterangan'      => $this->input->post('keterangan', TRUE)
        ];

        $res = $this->ssh_model->saveJadwal($postData, $id, $this->currentUser->id);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect("{$this->prefixUrl}/jadwal");
    }

    public function toggle_jadwal($id)
    {
        $this->_restrictRoles(['admin', 'verifikator']);
        $newStatus = $this->ssh_model->toggleJadwalStatus($id);
        if ($newStatus) {
            $msg = ($newStatus === 'buka') ? 'Jadwal pengusulan berhasil DIBUKA.' : 'Jadwal pengusulan berhasil DITUTUP.';
            $this->session->set_flashdata('success', $msg);
        } else {
            $this->session->set_flashdata('danger', 'Gagal mengubah status jadwal.');
        }
        redirect("{$this->prefixUrl}/jadwal");
    }

    public function hapus_jadwal($id)
    {
        $this->_restrictRoles(['admin']);
        $this->ssh_model->deleteJadwal($id);
        $this->session->set_flashdata('success', 'Jadwal pengusulan berhasil dihapus.');
        redirect("{$this->prefixUrl}/jadwal");
    }

    // =========================================================================
    // 5. DETAIL, DOWNLOAD, & API TRANSISI
    // =========================================================================

    public function detail($id)
    {
        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item) {
            return $this->output->set_status_header(404)->set_output(json_encode(['error' => 'Data tidak ditemukan']));
        }

        $logs = $this->ssh_model->getLogs($id);

        if ($this->input->is_ajax_request()) {
            $item->harga_usulan_formatted = rupiah($item->harga_usulan);
            $item->harga_ditetapkan_formatted = ($item->harga_ditetapkan !== NULL && $item->harga_ditetapkan !== '') ? rupiah($item->harga_ditetapkan) : NULL;
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['item' => $item, 'logs' => $logs]));
        }

        $data = [
            'title'       => 'Detail Usulan - ' . $item->kode_usulan,
            'tipe'        => $item->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'item'        => $item,
            'logs'        => $logs
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/detail', $data);
        $this->load->view('templates/footer');
    }

    public function download_lampiran($id, $slot = 1)
    {
        $slot = (int) $slot;
        if (!in_array($slot, [1, 2, 3], TRUE)) {
            $slot = 1;
        }

        $colFile = ($slot === 1) ? 'file_lampiran' : "file_lampiran_{$slot}";
        $colOrig = ($slot === 1) ? 'file_nama_asli' : "file_nama_asli_{$slot}";

        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item || empty($item->$colFile)) {
            show_404();
        }

        $filePath = FCPATH . 'uploads/ssh_sbu/' . $item->$colFile;
        if (!file_exists($filePath)) {
            $this->session->set_flashdata('danger', 'File lampiran tidak ditemukan di server.');
            redirect($this->agent->referrer() ?: 'ssh/usulan');
        }

        $this->load->helper('download');
        force_download($item->$colOrig ?: $item->$colFile, file_get_contents($filePath));
    }

    /**
     * Tampilkan Berkas Bukti Survey Secara Langsung (Inline) di Browser Tanpa Download
     */
    public function preview_lampiran($id, $slot = 1)
    {
        $slot = (int) $slot;
        if (!in_array($slot, [1, 2, 3], TRUE)) {
            $slot = 1;
        }

        $colFile = ($slot === 1) ? 'file_lampiran' : "file_lampiran_{$slot}";
        $colOrig = ($slot === 1) ? 'file_nama_asli' : "file_nama_asli_{$slot}";

        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item || empty($item->$colFile)) {
            show_404();
        }

        $filePath = FCPATH . 'uploads/ssh_sbu/' . $item->$colFile;
        if (!file_exists($filePath)) {
            show_404();
        }

        $ext = strtolower(pathinfo($item->$colFile, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            'txt'  => 'text/plain',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        $mime = $mimeMap[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/octet-stream');
        $clientName = $item->$colOrig ?: $item->$colFile;

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . addslashes($clientName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');

        readfile($filePath);
        exit;
    }

    /**
     * API JSON Metadata Seluruh Bukti Lampiran untuk Modal Viewer
     */
    public function api_lampiran($id)
    {
        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item) {
            return $this->output->set_status_header(404)->set_output(json_encode(['success' => FALSE, 'message' => 'Usulan tidak ditemukan.']));
        }

        $lampiran = [];
        for ($i = 1; $i <= 3; $i++) {
            $colFile = ($i === 1) ? 'file_lampiran' : "file_lampiran_{$i}";
            $colOrig = ($i === 1) ? 'file_nama_asli' : "file_nama_asli_{$i}";

            if (!empty($item->$colFile)) {
                $filePath = FCPATH . 'uploads/ssh_sbu/' . $item->$colFile;
                $exists = file_exists($filePath);
                $ext = strtolower(pathinfo($item->$colFile, PATHINFO_EXTENSION));
                $lampiran[] = [
                    'slot'           => $i,
                    'label'          => "Survey {$i}",
                    'nama_asli'      => $item->$colOrig ?: "Berkas Survey {$i}",
                    'file_name'      => $item->$colFile,
                    'ext'            => $ext,
                    'is_previewable' => in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'], TRUE),
                    'size'           => $exists ? round(filesize($filePath) / 1024, 1) . ' KB' : '-',
                    'preview_url'    => site_url("{$this->prefixUrl}/preview/{$item->id}/{$i}"),
                    'download_url'   => site_url("{$this->prefixUrl}/download/{$item->id}/{$i}")
                ];
            }
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'     => TRUE,
            'id'          => $item->id,
            'kode_usulan' => $item->kode_usulan,
            'uraian'      => $item->uraian,
            'spesifikasi' => $item->spesifikasi,
            'satuan'      => $item->satuan,
            'harga_usulan'=> rupiah($item->harga_usulan),
            'nama_skpd'   => $item->nama_skpd ?? '',
            'lampiran'    => $lampiran
        ]));
    }

    public function api_transisi_status()
    {
        if ($this->input->method() !== 'post') {
            return $this->output->set_status_header(405)->set_output(json_encode(['success' => FALSE, 'message' => 'Method Not Allowed']));
        }

        $id = (int) $this->input->post('id');
        $targetStatus = $this->input->post('target_status', TRUE);
        $catatan = $this->input->post('catatan', TRUE);
        $hargaDitetapkan = $this->input->post('harga_ditetapkan', TRUE);

        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item) {
            return $this->output->set_status_header(403)->set_output(json_encode([
                'success' => FALSE,
                'message' => 'Akses ditolak oleh kebijakan Row Level Security (RLS).'
            ]));
        }

        $canTransition = $this->ssh_service->canTransition($item->status_proses, $targetStatus, $this->currentUser->role);
        if (!$canTransition['allowed']) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'success' => FALSE,
                'message' => $canTransition['message']
            ]));
        }

        $result = ['success' => FALSE, 'message' => 'Aksi tidak dikenal.'];
        if ($targetStatus === 'Diajukan') {
            $result = $this->ssh_model->kirimUsulan($id, $this->currentUser);
        } elseif ($targetStatus === 'Diverifikasi') {
            $result = $this->ssh_model->verifikasiUsulan($id, 'setujui', $catatan, $hargaDitetapkan, $this->currentUser);
        } elseif ($targetStatus === 'Direvisi') {
            $result = $this->ssh_model->verifikasiUsulan($id, 'revisi', $catatan, NULL, $this->currentUser);
        } elseif ($targetStatus === 'Ditolak') {
            $result = $this->ssh_model->verifikasiUsulan($id, 'tolak', $catatan, NULL, $this->currentUser);
        } elseif ($targetStatus === 'Ditetapkan') {
            $result = $this->ssh_model->tetapkanUsulan($id, $this->currentUser);
        }

        $result['csrfName'] = $this->security->get_csrf_token_name();
        $result['csrfHash'] = $this->security->get_csrf_hash();

        return $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    private function _restrictRoles($allowedRoles)
    {
        if (!in_array($this->currentUser->role, $allowedRoles, TRUE)) {
            show_error('Anda tidak memiliki izin (hak akses) untuk membuka halaman ini.', 403, 'Akses Ditolak');
        }
    }
}
