<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['master_model', 'user_model', 'akun_model']);
    }

    // ==================== SKPD ====================
    public function skpd()
    {
        $action = $this->input->post('action', TRUE);
        if ($this->input->method() === 'post' && $action) {
            $this->_handleSkpd($action);
            redirect('master/skpd');
        }

        $data = [
            'title' => 'Master Data SKPD',
            'list'  => $this->master_model->getAllSkpd(FALSE)
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('master/skpd', $data);
        $this->load->view('templates/footer');
    }

    private function _handleSkpd($action)
    {
        if ($action === 'save') {
            $this->form_validation->set_rules('kode_skpd', 'Kode SKPD', 'required|max_length[20]');
            $this->form_validation->set_rules('nama_skpd', 'Nama SKPD', 'required|max_length[200]');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
                return;
            }

            $id = (int) $this->input->post('id');
            $data = [
                'kode_skpd'      => $this->input->post('kode_skpd', TRUE),
                'nama_skpd'      => $this->input->post('nama_skpd', TRUE),
                'kepala_skpd'    => $this->input->post('kepala_skpd', TRUE),
                'nip_kepala'     => $this->input->post('nip_kepala', TRUE),
                'jabatan_kepala' => $this->input->post('jabatan_kepala', TRUE),
                'nama_pengurus'  => $this->input->post('nama_pengurus', TRUE),
                'nip_pengurus'  => $this->input->post('nip_pengurus', TRUE),
                'alamat'         => $this->input->post('alamat', TRUE),
                'telepon'        => $this->input->post('telepon', TRUE),
                'is_active'      => (int) $this->input->post('is_active')
            ];
            $this->master_model->saveSkpd($data, $id ?: NULL);
            $this->logger->record($id ? 'update' : 'create', 'skpd', 'SKPD: ' . $data['nama_skpd']);
            $this->session->set_flashdata('success', 'Data SKPD berhasil disimpan.');
        } elseif ($action === 'delete') {
            $id = (int) $this->input->post('id');
            $this->master_model->deleteSkpd($id);
            $this->logger->record('delete', 'skpd', "Hapus SKPD ID #{$id}");
            $this->session->set_flashdata('success', 'SKPD berhasil dihapus.');
        }
    }

    // ==================== BARANG ====================
    public function barang()
    {
        $action = $this->input->post('action', TRUE);
        if ($this->input->method() === 'post' && $action) {
            $this->_handleBarang($action);
            redirect('master/barang');
        }

        $filter = [];
        if ($q = $this->input->get('q', TRUE)) $filter['q'] = $q;
        if ($k = $this->input->get('kategori', TRUE)) $filter['kategori'] = $k;

        $data = [
            'title'  => 'Master Data Barang',
            'list'   => $this->master_model->getAllBarang($filter),
            'filter' => $filter
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('master/barang', $data);
        $this->load->view('templates/footer');
    }

    private function _handleBarang($action)
    {
        if ($action === 'save') {
            $this->form_validation->set_rules('kode_barang', 'Kode Barang', 'required|max_length[50]');
            $this->form_validation->set_rules('nama_barang', 'Nama Barang', 'required|max_length[200]');
            $this->form_validation->set_rules('satuan', 'Satuan', 'required|max_length[30]');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
                return;
            }

            $id = (int) $this->input->post('id');
            $data = [
                'kode_barang'   => $this->input->post('kode_barang', TRUE),
                'nama_barang'   => $this->input->post('nama_barang', TRUE),
                'satuan'        => $this->input->post('satuan', TRUE),
                'kategori'      => $this->input->post('kategori', TRUE),
                'harga_standar' => (float) str_replace(['.', ','], ['', '.'], $this->input->post('harga_standar')),
                'keterangan'    => $this->input->post('keterangan', TRUE),
                'is_active'     => (int) $this->input->post('is_active')
            ];
            $this->master_model->saveBarang($data, $id ?: NULL);
            $this->logger->record($id ? 'update' : 'create', 'barang', $data['nama_barang']);
            $this->session->set_flashdata('success', 'Data barang berhasil disimpan.');
        } elseif ($action === 'delete') {
            $id = (int) $this->input->post('id');
            $this->master_model->deleteBarang($id);
            $this->logger->record('delete', 'barang', "Hapus barang ID #{$id}");
            $this->session->set_flashdata('success', 'Barang berhasil dihapus.');
        } elseif ($action === 'import') {
            $this->_importBarang();
        }
    }

    /**
     * Menangani proses import data barang dari file Excel.
     */
    private function _importBarang()
    {
        // Konfigurasi Upload
        $config['upload_path']      = './uploads/temp/';
        $config['allowed_types']    = 'xlsx|xls';
        $config['max_size']         = 5120; // 5MB
        $config['encrypt_name']     = TRUE;

        // Pastikan folder temporary tersedia
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, TRUE);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file_excel')) {
            $this->session->set_flashdata('danger', 'Gagal Upload: ' . $this->upload->display_errors('', ''));
            return;
        }

        $fileData = $this->upload->data();
        $filePath = $fileData['full_path'];

        try {
            // Membaca file menggunakan PhpSpreadsheet
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(TRUE);
            $spreadsheet = $reader->load($filePath);
            $sheetData   = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $inserted = 0;
            $skipped  = 0;
            $skipDuplicate = (bool) $this->input->post('skip_duplicate');

            foreach ($sheetData as $i => $row) {
                if ($i === 1) continue; // Lewati baris pertama (Header)

                $kode = trim($row['A'] ?? '');
                $nama = trim($row['B'] ?? '');

                if (empty($kode) || empty($nama)) continue;

                // Cek duplikasi jika opsi diaktifkan
                if ($skipDuplicate) {
                    $exists = $this->db->get_where('barang', ['kode_barang' => $kode])->num_rows();
                    if ($exists > 0) {
                        $skipped++;
                        continue;
                    }
                }

                $data = [
                    'kode_barang'   => $kode,
                    'nama_barang'   => $nama,
                    'satuan'        => $row['C'] ?: 'Unit',
                    'kategori'      => $row['D'] ?: 'Peralatan dan Mesin',
                    'harga_standar' => (float) str_replace(['.', ','], ['', '.'], $row['E'] ?? 0),
                    'keterangan'    => $row['F'] ?? NULL,
                    'is_active'     => 1
                ];

                $this->master_model->saveBarang($data);
                $inserted++;
            }

            $this->logger->record('import', 'barang', "Import Barang: Berhasil {$inserted}, Terlewati {$skipped}");
            $this->session->set_flashdata('success', "Proses import selesai. Berhasil: {$inserted} data. Terlewati (duplikat): {$skipped} data.");

        } catch (Exception $e) {
            $this->session->set_flashdata('danger', 'Kesalahan memproses Excel: ' . $e->getMessage());
        } finally {
            // Hapus file temporary setelah selesai
            if (file_exists($filePath)) unlink($filePath);
        }
    }

    // ==================== PERIODE ====================
    public function periode()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('tahun', 'Tahun', 'required|integer');
            $this->form_validation->set_rules('nama_periode', 'Nama Periode', 'required|max_length[100]');
            $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
            $this->form_validation->set_rules('tanggal_selesai', 'Tanggal Selesai', 'required');
            if ($this->form_validation->run() === TRUE) {
                $id = (int) $this->input->post('id');
                $data = [
                    'tahun'           => (int) $this->input->post('tahun'),
                    'nama_periode'    => $this->input->post('nama_periode', TRUE),
                    'tanggal_mulai'   => $this->input->post('tanggal_mulai', TRUE),
                    'tanggal_selesai' => $this->input->post('tanggal_selesai', TRUE),
                    'status'          => $this->input->post('status', TRUE) ?: 'open',
                    'keterangan'      => $this->input->post('keterangan', TRUE)
                ];
                $this->master_model->savePeriode($data, $id ?: NULL);
                $this->session->set_flashdata('success', 'Periode berhasil disimpan.');
            } else {
                $this->session->set_flashdata('danger', validation_errors());
            }
            redirect('master/periode');
        }

        $data = [
            'title' => 'Master Periode RKBMD',
            'list'  => $this->master_model->getAllPeriode()
        ];
        $this->load->view('templates/header', $data);
        $this->load->view('master/periode', $data);
        $this->load->view('templates/footer');
    }

    // ==================== USERS ====================
    public function user()
    {
        $action = $this->input->post('action', TRUE);
        if ($this->input->method() === 'post' && $action) {
            $this->_handleUser($action);
            redirect('master/user');
        }

        $data = [
            'title' => 'Master Data User',
            'list'  => $this->user_model->getAll(),
            'skpd'  => $this->master_model->getAllSkpd()
        ];
        $this->load->view('templates/header', $data);
        $this->load->view('master/user', $data);
        $this->load->view('templates/footer');
    }

    private function _handleUser($action)
    {
        if ($action === 'save') {
            $id = (int) $this->input->post('id');

            $this->form_validation->set_rules('username', 'Username', 'required|min_length[4]|max_length[50]');
            $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|max_length[150]');
            $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,skpd,verifikator,pimpinan]');
            if (!$id) {
                $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[200]');
            }

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
                return;
            }

            $data = [
                'username'     => $this->input->post('username', TRUE),
                'nama_lengkap' => $this->input->post('nama_lengkap', TRUE),
                'nip'          => $this->input->post('nip', TRUE),
                'email'        => $this->input->post('email', TRUE),
                'jabatan'      => $this->input->post('jabatan', TRUE),
                'role'         => $this->input->post('role', TRUE),
                'skpd_id'      => (int) $this->input->post('skpd_id') ?: NULL,
                'is_active'    => (int) $this->input->post('is_active')
            ];

            $password = $this->input->post('password');
            if ($password) $data['password'] = $password;

            if ($id) {
                $this->user_model->update($id, $data);
            } else {
                $this->user_model->create($data);
            }
            $this->logger->record($id ? 'update' : 'create', 'user', $data['username']);
            $this->session->set_flashdata('success', 'Data user berhasil disimpan.');
        } elseif ($action === 'delete') {
            $id = (int) $this->input->post('id');
            // Cegah hapus diri sendiri
            if ($id === (int) $this->session->userdata('user_id')) {
                $this->session->set_flashdata('danger', 'Tidak dapat menghapus akun sendiri.');
                return;
            }
            $this->user_model->delete($id);
            $this->logger->record('delete', 'user', "Hapus user ID #{$id}");
            $this->session->set_flashdata('success', 'User berhasil dihapus.');
        } elseif ($action === 'reset_password') {
            $id = (int) $this->input->post('id');
            $newPassword = $this->input->post('new_password');
            if (strlen($newPassword) < 8) {
                $this->session->set_flashdata('danger', 'Password minimal 8 karakter.');
                return;
            }
            $this->user_model->changePassword($id, $newPassword);
            $this->logger->record('reset_password', 'user', "Reset password user ID #{$id}");
            $this->session->set_flashdata('success', 'Password berhasil direset.');
        }
    }

    // ==================== AKUN BELANJA (SIPD RI) ====================
    public function akun_belanja()
    {
        $action = $this->input->post('action', TRUE);
        if ($this->input->method() === 'post' && $action) {
            $this->_handleAkunBelanja($action);
            redirect('master/akun_belanja');
        }

        $filter = [];
        if ($q = trim($this->input->get('q', TRUE) ?? '')) $filter['q'] = $q;
        if ($kel = trim($this->input->get('kelompok', TRUE) ?? '')) $filter['kelompok'] = $kel;
        if (($leaf = $this->input->get('is_leaf', TRUE)) !== null && $leaf !== '') $filter['is_leaf'] = (int) $leaf;
        if (($lev = $this->input->get('level', TRUE)) !== null && $lev !== '') $filter['level'] = (int) $lev;

        $page = max(1, (int) $this->input->get('page'));
        $perPage = (int) ($this->input->get('per_page') ?: 25);
        if (!in_array($perPage, [25, 50, 100])) $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $totalRows = $this->akun_model->countAkunBelanja($filter);
        $list = $this->akun_model->getAkunBelanja($filter, $perPage, $offset);
        $stats = $this->akun_model->getStatistikBelanja();
        $totalPages = ceil($totalRows / $perPage);

        $data = [
            'title'      => 'Master Data Akun Belanja SIPD RI',
            'list'       => $list,
            'filter'     => $filter,
            'stats'      => $stats,
            'page'       => $page,
            'perPage'    => $perPage,
            'offset'     => $offset,
            'totalRows'  => $totalRows,
            'totalPages' => $totalPages
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('master/akun_belanja', $data);
        $this->load->view('templates/footer');
    }

    public function ajax_akun_belanja()
    {
        $q = $this->input->get('q', TRUE);
        $leafOnly = $this->input->get('all') ? false : true;
        $results = $this->akun_model->searchSelect2($q, $leafOnly, 30);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['results' => $results]));
    }

    private function _handleAkunBelanja($action)
    {
        if ($action === 'save') {
            $this->form_validation->set_rules('kode_akun', 'Kode Akun', 'required|max_length[50]');
            $this->form_validation->set_rules('nama_akun', 'Nama Akun', 'required|max_length[500]');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('danger', validation_errors());
                return;
            }

            $id = (int) $this->input->post('id');
            $kode = trim($this->input->post('kode_akun', TRUE));
            $dots = substr_count($kode, '.');
            $level = $dots + 1;

            $data = [
                'kode_akun' => $kode,
                'nama_akun' => trim($this->input->post('nama_akun', TRUE)),
                'kelompok'  => $this->input->post('kelompok', TRUE) ?: 'Belanja Operasi',
                'level'     => $level,
                'is_leaf'   => (int) $this->input->post('is_leaf'),
                'is_active' => (int) $this->input->post('is_active')
            ];

            $this->akun_model->saveAkunBelanja($data, $id ?: NULL);
            $this->logger->record($id ? 'update' : 'create', 'akun_belanja', "Akun: {$data['kode_akun']} - {$data['nama_akun']}");
            $this->session->set_flashdata('success', 'Data Akun Belanja berhasil disimpan.');
        } elseif ($action === 'delete') {
            $id = (int) $this->input->post('id');
            $item = $this->akun_model->findAkunBelanja($id);
            if ($item) {
                $this->akun_model->deleteAkunBelanja($id);
                $this->logger->record('delete', 'akun_belanja', "Hapus Akun Belanja: {$item->kode_akun}");
                $this->session->set_flashdata('success', 'Akun Belanja berhasil dihapus.');
            }
        }
    }
}
