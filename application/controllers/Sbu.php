<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Sbu
 * Modul Standar Biaya Umum (SBU) untuk standarisasi honorarium, jasa, sewa, dan tarif belanja non-fisik.
 */
class Sbu extends Auth_Controller
{
    protected $tipe = 'SBU';
    protected $prefixUrl = 'sbu';
    protected $moduleTitle = 'Standar Biaya Umum (SBU)';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['ssh_model', 'master_model']);
        $this->load->library(['ssh_service', 'form_validation']);
        $this->load->helper(['app', 'form']);

        if (!can_access('sbu')) {
            show_error('Anda tidak memiliki hak akses untuk membuka modul Standar Biaya Umum (SBU). Hubungi Administrator.', 403, 'Akses Ditolak');
        }
    }

    /**
     * Index Router berdasarkan Role Pengguna
     */
    public function index()
    {
        $role = $this->currentUser->role;
        if (in_array($role, ['operator_skpd', 'skpd'], TRUE)) {
            redirect('sbu/usulan');
        } elseif ($role === 'verifikator') {
            redirect('sbu/verifikasi');
        } elseif (in_array($role, ['penetap', 'pimpinan'], TRUE)) {
            redirect('sbu/penetapan');
        } else {
            redirect('sbu/master_data');
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

        $data = [
            'title'        => 'Usulan ' . $this->moduleTitle,
            'tipe'         => $this->tipe,
            'prefixUrl'    => $this->prefixUrl,
            'moduleTitle'  => $this->moduleTitle,
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

    /**
     * Form CRUD: Tambah Usulan SBU Baru
     */
    public function tambah()
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

        if ($this->input->method() === 'post') {
            $post = $this->input->post();
            $post['tipe'] = $this->tipe;

            $thPost = !empty($post['tahun_anggaran']) ? (int)$post['tahun_anggaran'] : $thAktif;
            if (!in_array($this->currentUser->role, ['admin', 'pimpinan'], TRUE) && !$this->ssh_model->isJadwalBuka($this->tipe, $thPost)) {
                $this->session->set_flashdata('danger', "Akses Ditolak: Jadwal pengusulan {$this->moduleTitle} untuk TA {$thPost} sedang DITUTUP oleh Administrator BPKAD.");
                redirect("{$this->prefixUrl}/usulan");
                return;
            }

            $validation = $this->ssh_service->validateInput($post);
            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect("{$this->prefixUrl}/tambah");
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
                redirect("{$this->prefixUrl}/tambah");
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
                    redirect("{$this->prefixUrl}/tambah");
                }
                $uploadedFiles[$slotField] = $uploadResult;
            }

            $postData = $validation['cleanData'];
            $postData['tipe'] = $this->tipe;
            $postData['file_lampiran']    = $uploadedFiles['file_lampiran']['fileName'];
            $postData['file_nama_asli']   = $uploadedFiles['file_lampiran']['origName'];
            $postData['file_lampiran_2']  = $uploadedFiles['file_lampiran_2']['fileName'];
            $postData['file_nama_asli_2'] = $uploadedFiles['file_lampiran_2']['origName'];
            $postData['file_lampiran_3']  = $uploadedFiles['file_lampiran_3']['fileName'];
            $postData['file_nama_asli_3'] = $uploadedFiles['file_lampiran_3']['origName'];

            $result = $this->ssh_model->insertUsulan($postData, $this->currentUser);
            if ($result['success']) {
                $this->session->set_flashdata('success', "Usulan {$this->tipe} <strong>{$result['kode_usulan']}</strong> berhasil dibuat dengan status <strong>Draft</strong>.");
                redirect("{$this->prefixUrl}/usulan");
            } else {
                $this->session->set_flashdata('danger', $result['message'] ?? 'Gagal menyimpan usulan.');
                redirect("{$this->prefixUrl}/tambah");
            }
        }

        // Cek jika prefill dari item master katalog 2027
        $masterItem = NULL;
        if ($masterId = (int) $this->input->get('master_id')) {
            $masterItem = $this->ssh_model->getMasterById($masterId);
        }

        $jadwalAktif = $this->ssh_model->getJadwalAktif($this->tipe, $thAktif);

        $data = [
            'title'       => 'Tambah Usulan ' . $this->moduleTitle,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'kategori'    => $this->ssh_model->getKategoriList($this->tipe),
            'satuan'      => $this->ssh_model->getSatuanSbu(),
            'masterItem'  => $masterItem,
            'jadwalAktif' => $jadwalAktif
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('ssh/usulan_form', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Form CRUD: Edit Usulan SBU
     */
    public function edit($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);

        $item = $this->ssh_model->findWithRls($id, $this->currentUser);
        if (!$item || $item->tipe !== 'SBU') {
            $this->session->set_flashdata('danger', 'Data usulan SBU tidak ditemukan atau Anda tidak memiliki hak akses.');
            redirect('sbu/usulan');
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
            redirect('sbu/usulan');
        }

        if ($this->input->method() === 'post') {
            $post = $this->input->post();
            $post['tipe'] = 'SBU';

            $validation = $this->ssh_service->validateInput($post);
            if (!$validation['isValid']) {
                $this->session->set_flashdata('danger', implode('<br>', $validation['errors']));
                redirect("sbu/edit/{$id}");
            }

            $updateData = $validation['cleanData'];
            $updateData['tipe'] = 'SBU';

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
                redirect("sbu/edit/{$id}");
            }

            foreach ($fileSlots as $slotField => $cfg) {
                if (!empty($_FILES[$slotField]['name'])) {
                    $uploadResult = $this->ssh_service->handleFileUpload($slotField);
                    if (isset($uploadResult['error'])) {
                        $this->session->set_flashdata('danger', "Gagal upload {$cfg['label']}: " . $uploadResult['error']);
                        redirect("sbu/edit/{$id}");
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
                redirect('sbu/usulan');
            } else {
                $this->session->set_flashdata('danger', $res['message']);
                redirect("sbu/edit/{$id}");
            }
        }

        $data = [
            'title'       => 'Edit Usulan SBU - ' . $item->kode_usulan,
            'tipe'        => $this->tipe,
            'prefixUrl'   => $this->prefixUrl,
            'moduleTitle' => $this->moduleTitle,
            'item'        => $item,
            'kategori'    => $this->ssh_model->getKategoriList($this->tipe),
            'satuan'      => $this->ssh_model->getSatuanSbu()
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
        redirect('sbu/usulan');
    }

    public function hapus($id)
    {
        $this->_restrictRoles(['operator_skpd', 'skpd', 'admin']);
        $res = $this->ssh_model->deleteUsulan($id, $this->currentUser);
        $this->session->set_flashdata($res['success'] ? 'success' : 'danger', $res['message']);
        redirect('sbu/usulan');
    }

    // =========================================================================
    // 2. MENU: VERIFIKASI USULAN SBU (Role: verifikator, admin)
    // =========================================================================

    public function verifikasi()
    {
        $this->_restrictRoles(['verifikator', 'admin']);

        $rawStatus = $this->input->get('status', TRUE);
        $statusProses = ($rawStatus !== NULL) ? $rawStatus : 'Diajukan';

        $filter = [
            'tipe'          => 'SBU',
            'status_proses' => $statusProses,
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
            'summary'     => $this->ssh_model->getSummaryCounts($this->currentUser, $this->tipe),
            'filter'      => $filter,
            'kategori'    => $this->ssh_model->getKategoriList($this->tipe),
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
        redirect('sbu/verifikasi');
    }

    // =========================================================================
    // 3. MENU: PENETAPAN HARGA SBU (Role: penetap, pimpinan, admin)
    // =========================================================================

    public function penetapan()
    {
        $this->_restrictRoles(['penetap', 'pimpinan', 'admin']);

        $filter = [
            'tipe'          => 'SBU',
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
        redirect('sbu/penetapan');
    }

    // =========================================================================
    // 4. MENU: MASTER DATA SBU (Role: SEMUA PENGGUNA)
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
            redirect($this->agent->referrer() ?: 'sbu/usulan');
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
