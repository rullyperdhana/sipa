<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Ssh
 * Modul Standar Satuan Harga (SSH) dan Standar Biaya Umum (SBU).
 * Mengatur alur usulan, verifikasi, penetapan harga, dan master data publik.
 */
class Ssh extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['ssh_model', 'master_model']);
        $this->load->library(['ssh_service', 'form_validation']);
        $this->load->helper(['app', 'form']);
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

        $filter = [
            'status_proses' => $this->input->get('status', TRUE),
            'tipe'          => $this->input->get('tipe', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $this->input->get('tahun', TRUE),
            'q'             => $this->input->get('q', TRUE)
        ];

        // Jika user adalah admin dan ingin melihat SKPD tertentu
        $skpdId = $this->currentUser->skpd_id;
        if ($this->currentUser->role === 'admin' && $this->input->get('skpd_id')) {
            $skpdId = (int) $this->input->get('skpd_id');
        }

        $data = [
            'title'     => 'Usulan SSH & SBU',
            'list'      => $this->ssh_model->getUsulanBySkpd($skpdId, $filter),
            'summary'   => $this->ssh_model->getSummaryCounts($this->currentUser),
            'filter'    => $filter,
            'kategori'  => $this->ssh_model->getKategoriList(),
            'skpdList'  => ($this->currentUser->role === 'admin') ? $this->master_model->getAllSkpd() : []
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Form CRUD: Tambah Usulan SSH/SBU Baru
     */
    public function tambah()
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        if ($this->input->method() === 'post') {
            // Validasi input melalui service layer
            $validation = $this->ssh_service->validateInput($this->input->post());

            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect('ssh/tambah');
            }

            // Handle file upload
            $uploadResult = $this->ssh_service->handleFileUpload('file_lampiran');
            if (isset($uploadResult['error'])) {
                $this->session->set_flashdata('danger', 'Gagal upload file: ' . $uploadResult['error']);
                redirect('ssh/tambah');
            }

            $postData = $validation['cleanData'];
            if ($uploadResult['hasFile']) {
                $postData['file_lampiran']  = $uploadResult['fileName'];
                $postData['file_nama_asli'] = $uploadResult['origName'];
            }

            // Simpan usulan (RLS bound to current user & skpd)
            $result = $this->ssh_model->insertUsulan($postData, $this->currentUser);

            if ($result['success']) {
                $this->session->set_flashdata('success', "Usulan <strong>{$result['kode_usulan']}</strong> berhasil dibuat dengan status <strong>Draft</strong>.");
                redirect('ssh/usulan');
            } else {
                $this->session->set_flashdata('danger', 'Gagal menyimpan usulan.');
                redirect('ssh/tambah');
            }
        }

        $data = [
            'title'    => 'Tambah Usulan SSH & SBU Baru',
            'kategori' => $this->ssh_model->getKategoriList(),
            'satuan'   => $this->ssh_model->getSatuanList()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Form CRUD: Edit Usulan SSH/SBU
     * Dilindungi RLS: Hanya milik SKPD sendiri dan berstatus 'Draft' atau 'Direvisi'.
     */
    public function edit($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item) {
            $this->session->set_flashdata('danger', 'Data tidak ditemukan atau Anda tidak memiliki hak akses (RLS Violation).');
            redirect('ssh/usulan');
        }

        // Cek status usulan: hanya Draft atau Direvisi yang dapat diedit
        if (!in_array($item->status_proses, ['Draft', 'Direvisi'], TRUE)) {
            $this->session->set_flashdata('danger', "Usulan dengan status '{$item->status_proses}' tidak dapat diedit karena sudah terkunci.");
            redirect('ssh/usulan');
        }

        if ($this->input->method() === 'post') {
            $validation = $this->ssh_service->validateInput($this->input->post());

            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect("ssh/edit/{$id}");
            }

            $updateData = $validation['cleanData'];

            // Handle optional new file upload
            if (!empty($_FILES['file_lampiran']['name'])) {
                $uploadResult = $this->ssh_service->handleFileUpload('file_lampiran');
                if (isset($uploadResult['error'])) {
                    $this->session->set_flashdata('danger', 'Gagal upload file: ' . $uploadResult['error']);
                    redirect("ssh/edit/{$id}");
                }
                if ($uploadResult['hasFile']) {
                    $updateData['file_lampiran']  = $uploadResult['fileName'];
                    $updateData['file_nama_asli'] = $uploadResult['origName'];
                }
            }

            $res = $this->ssh_model->updateUsulan($id, $updateData, $this->currentUser);
            if ($res['success']) {
                $this->session->set_flashdata('success', $res['message']);
                redirect('ssh/usulan');
            } else {
                $this->session->set_flashdata('danger', $res['message']);
                redirect("ssh/edit/{$id}");
            }
        }

        $data = [
            'title'    => 'Edit Usulan - ' . $item->kode_usulan,
            'item'     => $item,
            'kategori' => $this->ssh_model->getKategoriList(),
            'satuan'   => $this->ssh_model->getSatuanList()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Aksi: Kirim Usulan (Mengubah status Draft / Direvisi -> Diajukan)
     */
    public function kirim($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $res = $this->ssh_model->kirimUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/usulan');
    }

    /**
     * Aksi: Hapus Usulan (Hanya untuk Draft)
     */
    public function hapus($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $res = $this->ssh_model->deleteUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/usulan');
    }

    // =========================================================================
    // 2. MENU: VERIFIKASI USULAN (Role: verifikator, admin)
    // =========================================================================

    public function verifikasi()
    {
        $this->_restrictRoles(['verifikator', 'admin']);

        $filter = [
            'status_proses' => $this->input->get('status', TRUE) ?: 'Diajukan',
            'id_skpd'       => $this->input->get('skpd_id', TRUE),
            'tipe'          => $this->input->get('tipe', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $this->input->get('tahun', TRUE),
            'q'             => $this->input->get('q', TRUE)
        ];

        $data = [
            'title'    => 'Verifikasi Usulan SSH & SBU',
            'list'     => $this->ssh_model->getUsulanVerifikasi($filter),
            'filter'   => $filter,
            'kategori' => $this->ssh_model->getKategoriList(),
            'skpdList' => $this->master_model->getAllSkpd()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/verifikasi_index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Aksi: Verifikasi Usulan (Setujui atau Revisi)
     */
    public function proses_verifikasi($id)
    {
        $this->_restrictRoles(['verifikator', 'admin']);

        if ($this->input->method() !== 'post') {
            show_404();
        }

        $aksi = $this->input->post('aksi', TRUE); // 'setujui' atau 'revisi'
        $catatan = $this->input->post('catatan_verifikator', TRUE);
        $rawHarga = str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], $this->input->post('harga_ditetapkan', TRUE) ?? '');
        $hargaDitetapkan = is_numeric($rawHarga) && $rawHarga > 0 ? (float) $rawHarga : NULL;

        $res = $this->ssh_model->verifikasiUsulan($id, $aksi, $catatan, $hargaDitetapkan, $this->currentUser);

        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/verifikasi');
    }

    // =========================================================================
    // 3. MENU: PENETAPAN HARGA (Role: penetap, pimpinan, admin)
    // =========================================================================

    public function penetapan()
    {
        $this->_restrictRoles(['penetap', 'pimpinan', 'admin']);

        $filter = [
            'status_proses' => $this->input->get('status', TRUE) ?: 'Diverifikasi',
            'id_skpd'       => $this->input->get('skpd_id', TRUE),
            'tipe'          => $this->input->get('tipe', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $this->input->get('tahun', TRUE),
            'q'             => $this->input->get('q', TRUE)
        ];

        $data = [
            'title'    => 'Penetapan Standar Harga (SSH & SBU)',
            'list'     => $this->ssh_model->getUsulanPenetapan($filter),
            'filter'   => $filter,
            'kategori' => $this->ssh_model->getKategoriList(),
            'skpdList' => $this->master_model->getAllSkpd()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/penetapan_index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Aksi: Tetapkan Harga (Diverifikasi -> Ditetapkan)
     */
    public function proses_penetapan($id)
    {
        $this->_restrictRoles(['penetap', 'pimpinan', 'admin']);

        if ($this->input->method() !== 'post') {
            show_404();
        }

        $res = $this->ssh_model->tetapkanUsulan($id, $this->currentUser);

        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/penetapan');
    }

    // =========================================================================
    // 4. MENU: MASTER DATA (Role: SEMUA PENGGUNA)
    // =========================================================================

    public function master_data()
    {
        // Terbuka untuk SEMUA role yang terautentikasi
        $filter = [
            'kategori' => $this->input->get('kategori', TRUE),
            'id_skpd'  => $this->input->get('skpd_id', TRUE),
            'tipe'     => $this->input->get('tipe', TRUE),
            'tahun'    => $this->input->get('tahun', TRUE),
            'q'        => $this->input->get('q', TRUE)
        ];

        $data = [
            'title'    => 'Master Data Standar Satuan Harga (SSH & SBU)',
            'list'     => $this->ssh_model->getMasterData($filter),
            'filter'   => $filter,
            'kategori' => $this->ssh_model->getKategoriList(),
            'skpdList' => $this->master_model->getAllSkpd()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/master_index', $data);
        $this->load->view('templates/footer');
    }

    // =========================================================================
    // 5. DETAIL & RIWAYAT AUDIT TRAIL MODAL (JSON / VIEW)
    // =========================================================================

    public function detail($id)
    {
        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item) {
            return $this->output->set_status_header(404)->set_output(json_encode(['error' => 'Data tidak ditemukan']));
        }

        $logs = $this->ssh_model->getLogs($id);

        if ($this->input->is_ajax_request()) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'item' => $item,
                    'logs' => $logs
                ]));
        }

        $data = [
            'title' => 'Detail Usulan - ' . $item->kode_usulan,
            'item'  => $item,
            'logs'  => $logs
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/detail', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Download File Lampiran Bukti Pendukung
     */
    public function download_lampiran($id)
    {
        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item || empty($item->file_lampiran)) {
            show_404();
        }

        $filePath = FCPATH . 'uploads/ssh_sbu/' . $item->file_lampiran;
        if (!file_exists($filePath)) {
            $this->session->set_flashdata('danger', 'File lampiran tidak ditemukan di server.');
            redirect($this->agent->referrer() ?: 'ssh/master_data');
        }

        $this->load->helper('download');
        force_download($item->file_nama_asli ?: $item->file_lampiran, file_get_contents($filePath));
    }

    // =========================================================================
    // 6. REST API / AJAX ENDPOINT FOR STATE TRANSITIONS & CLIENT HOOKS
    // =========================================================================

    /**
     * API: Transisi Status Terpadu via AJAX
     * Menangani: Draft -> Diajukan, Diajukan -> Diverifikasi/Direvisi, Diverifikasi -> Ditetapkan
     */
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

        // Cek transisi status sah melalui Ssh_service
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
        } elseif ($targetStatus === 'Ditetapkan') {
            $result = $this->ssh_model->tetapkanUsulan($id, $this->currentUser);
        }

        // Regenerasi token CSRF baru untuk respons AJAX
        $result['csrfName'] = $this->security->get_csrf_token_name();
        $result['csrfHash'] = $this->security->get_csrf_hash();

        return $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    /**
     * Helper proteksi hak akses controller
     */
    private function _restrictRoles($allowedRoles)
    {
        if (!in_array($this->currentUser->role, $allowedRoles, TRUE)) {
            show_error('Anda tidak memiliki izin (hak akses) untuk membuka halaman ini.', 403, 'Akses Ditolak');
        }
    }
}
