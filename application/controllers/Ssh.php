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
            'tipe'          => $this->tipe,
            'status_proses' => $this->input->get('status', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $this->input->get('tahun', TRUE),
            'q'             => $this->input->get('q', TRUE)
        ];

        $skpdId = $this->currentUser->skpd_id;
        if ($this->currentUser->role === 'admin' && $this->input->get('skpd_id')) {
            $skpdId = (int) $this->input->get('skpd_id');
        }

        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe);
        $isJadwalBuka = $this->ssh_model->isJadwalBuka($this->tipe);

        $data = [
            'title'        => 'Usulan ' . $this->moduleTitle,
            'tipe'         => $this->tipe,
            'prefixUrl'    => $this->prefixUrl,
            'moduleTitle'  => $this->moduleTitle,
            'list'         => $this->ssh_model->getUsulanBySkpd($skpdId, $filter),
            'summary'      => $this->ssh_model->getSummaryCounts($this->currentUser, $this->tipe),
            'filter'       => $filter,
            'kategori'     => $this->ssh_model->getKategoriSsh(),
            'skpdList'     => ($this->currentUser->role === 'admin') ? $this->master_model->getAllSkpd() : [],
            'jadwalAktif'  => $jadwalAktif,
            'isJadwalBuka' => $isJadwalBuka
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Form CRUD: Tambah Usulan SSH Baru
     */
    public function tambah()
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        // Pengecekan Jadwal Pengusulan Aktif (Khusus role SKPD)
        $isJadwalBuka = $this->ssh_model->isJadwalBuka($this->tipe);
        if (!in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE) && !$isJadwalBuka) {
            $this->session->set_flashdata('warning', "Pengusulan {$this->moduleTitle} saat ini belum dibuka atau telah ditutup. Silakan menunggu pembuatan/pembukaan jadwal pengusulan oleh BPKAD.");
            redirect("{$this->prefixUrl}/usulan");
        }

        if ($this->input->method() === 'post') {
            $post = $this->input->post();
            $post['tipe'] = $this->tipe;

            $validation = $this->ssh_service->validateInput($post);
            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect("{$this->prefixUrl}/tambah");
            }

            $uploadResult = $this->ssh_service->handleFileUpload('file_lampiran');
            if (isset($uploadResult['error'])) {
                $this->session->set_flashdata('danger', 'Gagal upload file: ' . $uploadResult['error']);
                redirect("{$this->prefixUrl}/tambah");
            }

            $postData = $validation['cleanData'];
            $postData['tipe'] = $this->tipe;
            if ($uploadResult['hasFile']) {
                $postData['file_lampiran']  = $uploadResult['fileName'];
                $postData['file_nama_asli'] = $uploadResult['origName'];
            }

            $result = $this->ssh_model->insertUsulan($postData, $this->currentUser);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Usulan {$this->tipe} <strong>{$result['kode_usulan']}</strong> berhasil dibuat dengan status <strong>Draft</strong>.");
                redirect("{$this->prefixUrl}/usulan");
            } else {
                $this->session->set_flashdata('danger', 'Gagal menyimpan usulan.');
                redirect("{$this->prefixUrl}/tambah");
            }
        }

        // Cek jika prefill dari item master katalog 2027
        $masterItem = NULL;
        if ($masterId = (int) $this->input->get('master_id')) {
            $masterItem = $this->ssh_model->getMasterById($masterId);
        }

        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe);

        $data = [
            'title'       => 'Tambah Usulan ' . $this->moduleTitle,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'kategori'    => $this->ssh_model->getKategoriSsh(),
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
        if (!$item || $item->tipe !== 'SSH') {
            $this->session->set_flashdata('danger', 'Data usulan SSH tidak ditemukan atau Anda tidak memiliki hak akses.');
            redirect('ssh/usulan');
        }

        if (!in_array($item->status_proses, ['Draft', 'Direvisi'], TRUE)) {
            $this->session->set_flashdata('danger', "Usulan berstatus '{$item->status_proses}' tidak dapat diedit karena sudah terkunci.");
            redirect('ssh/usulan');
        }

        if ($this->input->method() === 'post') {
            $post = $this->input->post();
            $post['tipe'] = 'SSH';

            $validation = $this->ssh_service->validateInput($post);
            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect("ssh/edit/{$id}");
            }

            $updateData = $validation['cleanData'];
            $updateData['tipe'] = 'SSH';

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
            'title'       => 'Edit Usulan SSH - ' . $item->kode_usulan,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'item'        => $item,
            'kategori'    => $this->ssh_model->getKategoriSsh(),
            'satuan'      => $this->ssh_model->getSatuanSsh()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_form', $data);
        $this->load->view('templates/footer');
    }

    public function kirim($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);
        $res = $this->ssh_model->kirimUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/usulan');
    }

    public function hapus($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);
        $res = $this->ssh_model->deleteUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/usulan');
    }

    // =========================================================================
    // 2. MENU: VERIFIKASI USULAN SSH (Role: verifikator, admin)
    // =========================================================================

    public function verifikasi()
    {
        $this->_restrictRoles(['verifikator', 'admin']);

        $filter = [
            'tipe'          => 'SSH',
            'status_proses' => $this->input->get('status', TRUE) ?: 'Diajukan',
            'id_skpd'       => $this->input->get('skpd_id', TRUE),
            'kategori'      => $this->input->get('kategori', TRUE),
            'tahun'         => $this->input->get('tahun', TRUE),
            'q'             => $this->input->get('q', TRUE)
        ];

        $data = [
            'title'       => 'Verifikasi Usulan ' . $this->moduleTitle,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'list'        => $this->ssh_model->getUsulanVerifikasi($filter),
            'filter'      => $filter,
            'kategori'    => $this->ssh_model->getKategoriSsh(),
            'skpdList'    => $this->master_model->getAllSkpd()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/verifikasi_index', $data);
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

        $res = $this->ssh_model->verifikasiUsulan($id, $aksi, $catatan, $hargaDitetapkan, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('ssh/verifikasi');
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
            'filter'      => $filter,
            'kategori'    => $this->ssh_model->getKategoriSsh(),
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

    public function download_lampiran($id)
    {
        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item || empty($item->file_lampiran)) show_404();

        $filePath = FCPATH . 'uploads/ssh_sbu/' . $item->file_lampiran;
        if (!file_exists($filePath)) {
            $this->session->set_flashdata('danger', 'File lampiran tidak ditemukan di server.');
            redirect($this->agent->referrer() ?: 'ssh/master_data');
        }

        $this->load->helper('download');
        force_download($item->file_nama_asli ?: $item->file_lampiran, file_get_contents($filePath));
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
