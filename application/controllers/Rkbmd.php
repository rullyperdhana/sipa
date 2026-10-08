<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller utama RKBMD - menangani 5 jenis: pengadaan, pemeliharaan,
 * pemanfaatan, pemindahtanganan, penghapusan.
 */
class Rkbmd extends Auth_Controller
{
    protected $allowedJenis = ['pengadaan', 'pemeliharaan', 'pemanfaatan', 'pemindahtanganan', 'penghapusan'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['rkbmd_model', 'master_model']);
    }

    /**
     * Validasi parameter jenis usulan dari URL.
     */
    protected function validateJenis($jenis)
    {
        if (!in_array($jenis, $this->allowedJenis, TRUE)) {
            show_error('Jenis usulan tidak dikenali.', 404);
        }
        return $jenis;
    }

    /**
     * Cek apakah user boleh mengakses usulan ini.
     */
    protected function authorizeUsulan($usulan, $forEdit = FALSE)
    {
        if (!$usulan) show_404();

        $user = $this->currentUser;

        // Admin & verifikator boleh lihat semua
        if (in_array($user->role, ['admin', 'verifikator'], TRUE)) {
            if ($forEdit && $user->role === 'verifikator') {
                show_error('Verifikator tidak dapat mengedit usulan SKPD.', 403);
            }
            return;
        }

        // SKPD hanya boleh akses miliknya sendiri
        if ($user->role === 'skpd' && (int) $usulan->skpd_id !== (int) $user->skpd_id) {
            show_error('Anda tidak memiliki hak akses ke usulan ini.', 403);
        }

        // Edit hanya saat draft / revisi
        if ($forEdit && !in_array($usulan->status, ['draft', 'revisi'], TRUE)) {
            show_error('Usulan dengan status "' . $usulan->status . '" tidak dapat diedit lagi.', 403);
        }
    }

    /**
     * Daftar usulan per jenis.
     */
    public function index($jenis)
    {
        $jenis = $this->validateJenis($jenis);
        $user = $this->currentUser;

        $filter = ['jenis' => $jenis];
        if ($user->role === 'skpd') $filter['skpd_id'] = $user->skpd_id;
        if ($q = $this->input->get('q', TRUE))           $filter['q'] = $q;
        if ($s = $this->input->get('status', TRUE))      $filter['status'] = $s;
        if ($t = (int) $this->input->get('tahun'))       $filter['tahun'] = $t;

        $data = [
            'title'   => 'Daftar Usulan ' . label_jenis($jenis),
            'jenis'   => $jenis,
            'usulan'  => $this->rkbmd_model->getUsulan($filter),
            'filter'  => $filter,
            'periode' => $this->master_model->getAllPeriode()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('rkbmd/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Form buat usulan baru.
     */
    public function create($jenis)
    {
        $jenis = $this->validateJenis($jenis);
        $user = $this->currentUser;

        if ($user->role === 'verifikator') {
            show_error('Verifikator tidak dapat membuat usulan.', 403);
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('skpd_id', 'SKPD', 'required|integer');
            $this->form_validation->set_rules('periode_id', 'Periode', 'required|integer');
            $this->form_validation->set_rules('tahun_anggaran', 'Tahun Anggaran', 'required|integer|exact_length[4]');
            $this->form_validation->set_rules('tanggal_usulan', 'Tanggal Usulan', 'required|regex_match[/^\d{4}-\d{2}-\d{2}$/]');
            $this->form_validation->set_rules('keterangan', 'Keterangan', 'trim|max_length[500]');

            if ($this->form_validation->run() === TRUE) {
                $skpdId = (int) $this->input->post('skpd_id');
                if ($user->role === 'skpd') $skpdId = (int) $user->skpd_id;

                $usulanId = $this->rkbmd_model->createUsulan([
                    'jenis_usulan'   => $jenis,
                    'skpd_id'        => $skpdId,
                    'periode_id'     => (int) $this->input->post('periode_id'),
                    'tahun_anggaran' => (int) $this->input->post('tahun_anggaran'),
                    'tanggal_usulan' => $this->input->post('tanggal_usulan', TRUE),
                    'keterangan'     => $this->input->post('keterangan', TRUE),
                    'is_nihil'       => $this->input->post('is_nihil') ? 1 : 0
                ]);

                if ($usulanId) {
                    $this->session->set_flashdata('success', 'Usulan berhasil dibuat. Silakan tambahkan item.');
                    redirect("rkbmd/{$jenis}/edit/{$usulanId}");
                }
                $this->session->set_flashdata('danger', 'Gagal membuat usulan.');
            } else {
                $this->session->set_flashdata('danger', validation_errors());
            }
        }

        $data = [
            'title'   => 'Buat Usulan ' . label_jenis($jenis),
            'jenis'   => $jenis,
            'skpd'    => $this->master_model->getAllSkpd(),
            'periode' => $this->master_model->getActivePeriode()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('rkbmd/create', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Edit usulan + manage detail items.
     */
    public function edit($jenis, $id)
    {
        $jenis = $this->validateJenis($jenis);
        $usulan = $this->rkbmd_model->findUsulan($id);
        $this->authorizeUsulan($usulan, TRUE);

        if ($usulan->jenis_usulan !== $jenis) {
            redirect("rkbmd/{$usulan->jenis_usulan}/edit/{$id}");
        }

        // Handle aksi: tambah/update/hapus detail, submit, update header
        $action = $this->input->post('action', TRUE);

        if ($this->input->method() === 'post') {
            switch ($action) {
                case 'add_detail':
                    $this->_handleAddDetail($jenis, $id);
                    break;
                case 'update_detail':
                    $this->_handleUpdateDetail($jenis, $id);
                    break;
                case 'delete_detail':
                    $this->_handleDeleteDetail($jenis, $id);
                    break;
                case 'update_header':
                    $this->_handleUpdateHeader($id);
                    break;
                case 'submit':
                    $this->_handleSubmit($id);
                    break;
            }
            redirect("rkbmd/{$jenis}/edit/{$id}");
        }

        $data = [
            'title'   => 'Edit Usulan ' . label_jenis($jenis) . ' - ' . $usulan->nomor_usulan,
            'jenis'   => $jenis,
            'usulan'  => $usulan,
            'detail'  => $this->rkbmd_model->getDetail($jenis, $id),
            'barang'  => $this->master_model->getAllBarang(['active_only' => TRUE]),
            'periode' => $this->master_model->getAllPeriode()
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('rkbmd/edit_' . $jenis, $data);
        $this->load->view('templates/footer');
    }

    /**
     * Upload Excel untuk tambah detail penghapusan
     */
    public function upload($jenis, $id)
    {
        $jenis = $this->validateJenis($jenis);
        $usulan = $this->rkbmd_model->findUsulan($id);
        $this->authorizeUsulan($usulan, TRUE);

        // Check if can edit
        if (!in_array($usulan->status, ['draft', 'revisi']) ||
            !in_array($this->auth->user()->role, ['admin', 'skpd'])) {
            $this->session->set_flashdata('danger', 'Tidak dapat mengupload data pada usulan ini.');
            redirect("rkbmd/{$jenis}/edit/{$id}");
        }

        // Check jenis is penghapusan
        if ($jenis !== 'penghapusan') {
            $this->session->set_flashdata('danger', 'Upload Excel hanya untuk penghapusan.');
            redirect("rkbmd/{$jenis}/edit/{$id}");
        }

        // Check file upload
        if (empty($_FILES['excel_file']['name'])) {
            $this->session->set_flashdata('danger', 'Pilih file Excel untuk diupload.');
            redirect("rkbmd/{$jenis}/edit/{$id}");
        }

        // Load Excel reader library
        $this->load->library('excel_reader');

        if (!$this->excel_reader->isAvailable()) {
            $this->session->set_flashdata('danger', 'Library PhpSpreadsheet belum diinstall. Jalankan: composer require phpoffice/phpspreadsheet');
            redirect("rkbmd/{$jenis}/edit/{$id}");
        }

        // Upload config
        $config['upload_path']   = FCPATH . 'uploads/temp/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size']      = 5120; // 5MB
        $config['file_name']     = 'upload_' . $id . '_' . time();

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel_file')) {
            $this->session->set_flashdata('danger', 'Upload gagal: ' . $this->upload->display_errors('', ''));
            redirect("rkbmd/{$jenis}/edit/{$id}");
        }

        $uploadData = $this->upload->data();
        $filePath   = $uploadData['full_path'];

        // Process Excel file
        $result = $this->_processExcelUpload($filePath, $id);

        // Delete temp file
        @unlink($filePath);

        // Show result
        $success = count($result['success']);
        $failed  = count($result['failed']);
        $total   = $success + $failed;

        if ($success > 0) {
            $msg = "Upload berhasil: {$success} dari {$total} barang ditambahkan.";
            if ($failed > 0) {
                $msg .= " {$failed} gagal (lihat detail di log).";
            }
            $this->session->set_flashdata('success', $msg);
        } else {
            // Build detailed error message
            $errorDetails = [];
            foreach (array_slice($result['failed'], 0, 5) as $fail) {
                $errorDetails[] = "Row {$fail['row']}: {$fail['reason']}";
            }
            if (count($result['failed']) > 5) {
                $errorDetails[] = "... dan " . (count($result['failed']) - 5) . " baris lainnya.";
            }

            $errorMsg = "Tidak ada barang yang ditambahkan. ";
            if (!empty($errorDetails)) {
                $errorMsg .= "Detail: " . implode('; ', $errorDetails);
            } else {
                $errorMsg .= "Pastikan format Excel sesuai dan data dimulai dari baris 5.";
            }

            $this->session->set_flashdata('danger', $errorMsg);
        }

        redirect("rkbmd/{$jenis}/edit/{$id}");
    }

    /**
     * Process Excel file and insert to database
     * Data structure (columns A-Y):
     * A-G: kode barang segments
     * H: nibar, I: no register, K: nama barang, L: spesifikasi
     * M: merek, N: lokasi, O: nopol, P: norangka, Q: nobpkb
     * R: nilai, S: satuan, T: nilai satuan perolehan
     * U: nilai perolehan, V: cara perolehan, W: tanggal perolehan
     * X: status penggunaan, Y: keterangan
     */
    private function _processExcelUpload($filePath, $usulanId)
    {
        log_message('info', "Memproses file Excel: {$filePath}");

        $this->excel_reader->load($filePath);

        // Get highest row untuk debugging
        $highestRow = $this->excel_reader->getHighestRow();
        log_message('info', "Highest row di Excel: {$highestRow}");

        // Get all barang from database for validation
        $barangList = $this->master_model->getAllBarang(['active_only' => FALSE]);
        $barangMap  = [];
        foreach ($barangList as $b) {
            $barangMap[$b->kode_barang] = $b->id;
        }

        // Log untuk debugging
        log_message('info', 'Total barang di database: ' . count($barangMap));

        $result = [
            'success' => [],
            'failed'  => []
        ];

        // Read data from row 5 onwards (skip header rows 1-4)
        $data = $this->excel_reader->getSheetData(5, null, true);

        log_message('info', 'Total data rows dari Excel (mulai row 5): ' . count($data));

        foreach ($data as $rowIndex => $row) {
            // Build kode_barang from columns A-G
            // Format: A-C (1 digit), D-F (2 digit), G (3 digit)
            // Contoh: 1.3.2.01.03.04.002
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            $kodeSegmen = [];

            foreach ($columns as $idx => $col) {
                $val = $row[$col] ?? '';
                if (trim($val) === '' || is_null($val)) {
                    $result['failed'][] = ['row' => $excelRow, 'reason' => "Kolom {$col} kosong"];
                    continue 2; // skip to next row
                }

                // Convert to string and handle numeric values
                $val = trim((string) $val);

                // Format sesuai posisi kolom
                if ($idx < 3) {
                    // Kolom A-C: 1 digit (tanpa leading zero)
                    $kodeSegmen[] = $val;
                } elseif ($idx < 6) {
                    // Kolom D-F: 2 digit (dengan leading zero)
                    $kodeSegmen[] = str_pad($val, 2, '0', STR_PAD_LEFT);
                } else {
                    // Kolom G: 3 digit (dengan leading zero)
                    $kodeSegmen[] = str_pad($val, 3, '0', STR_PAD_LEFT);
                }
            }

            $excelRow = $rowIndex + 5;
            $kodeBarang = implode('.', $kodeSegmen);
            log_message('info', "Row {$excelRow}: Raw values: " . json_encode(array_combine($columns, array_map(fn($c) => $row[$c] ?? '', $columns))));
            log_message('info', "Row {$excelRow}: Kode Barang = {$kodeBarang}");

            // Check if barang exists
            if (!isset($barangMap[$kodeBarang])) {
                $result['failed'][] = ['row' => $excelRow, 'kode' => $kodeBarang, 'reason' => 'Barang tidak ditemukan'];
                log_message('info', "Row {$excelRow}: Gagal - barang {$kodeBarang} tidak ditemukan di database");
                continue;
            }
            log_message('info', "Row {$excelRow}: Barang ditemukan - ID = {$barangMap[$kodeBarang]}");

            // Extract year from tanggal perolehan (column W)
            $tglPerolehan = $row['W'] ?? '';
            $tahunPerolehan = 0;
            if (!empty($tglPerolehan)) {
                if (is_numeric($tglPerolehan)) {
                    // Excel date serial
                    $tahunPerolehan = (int) date('Y', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimeStamp($tglPerolehan));
                } elseif (preg_match('/(\d{4})/', $tglPerolehan, $m)) {
                    $tahunPerolehan = (int) $m[1];
                }
            }

            // Prepare data for insert
            $insertData = [
                'barang_id'            => $barangMap[$kodeBarang],
                'kategori_penghapusan' => 'undang_undang', // Default per user request
                'no_register'          => trim($row['I'] ?? ''),
                'spesifikasi'          => trim($row['L'] ?? ''),
                'tahun_perolehan'      => $tahunPerolehan,
                'harga_perolehan'      => (float) ($row['U'] ?? 0),
                'keterangan'           => trim($row['Y'] ?? '')
            ];

            // Validate required fields
            if (empty($insertData['no_register'])) {
                $result['failed'][] = ['row' => $rowIndex + 5, 'kode' => $kodeBarang, 'reason' => 'No Register kosong'];
                continue;
            }

            // Insert to database
            log_message('info', "Row {$excelRow}: Inserting detail - " . json_encode($insertData));
            $detailId = $this->rkbmd_model->addDetail('penghapusan', $usulanId, $insertData);

            if ($detailId) {
                $result['success'][] = ['row' => $excelRow, 'kode' => $kodeBarang, 'id' => $detailId];
                log_message('info', "Row {$excelRow}: Berhasil insert - ID {$detailId}");
            } else {
                $result['failed'][] = ['row' => $excelRow, 'kode' => $kodeBarang, 'reason' => 'Gagal insert ke database'];
                log_message('error', "Row {$excelRow}: Gagal insert ke database");
            }
        }

        return $result;
    }

    public function detail($jenis, $id)
    {
        $jenis = $this->validateJenis($jenis);
        $usulan = $this->rkbmd_model->findUsulan($id);
        $this->authorizeUsulan($usulan, FALSE);

        $data = [
            'title'  => 'Detail Usulan ' . $usulan->nomor_usulan,
            'jenis'  => $jenis,
            'usulan' => $usulan,
            'detail' => $this->rkbmd_model->getDetail($jenis, $id)
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('rkbmd/detail', $data);
        $this->load->view('templates/footer');
    }

    public function cetak($jenis, $id)
    {
        $jenis = $this->validateJenis($jenis);
        $usulan = $this->rkbmd_model->findUsulan($id);
        $this->authorizeUsulan($usulan, FALSE);

        $data = [
            'title'     => 'Cetak ' . $usulan->nomor_usulan,
            'jenis'     => $jenis,
            'usulan'    => $usulan,
            'detail'    => $this->rkbmd_model->getDetail($jenis, $id),
            'app_owner' => $this->config->item('app_owner'),
            'app_kabupaten' => $this->config->item('app_kabupaten'),
            'skpd_info' => $this->master_model->findSkpd($usulan->skpd_id)
        ];
        $this->load->view('rkbmd/cetak_' . $jenis, $data);
    }

    public function excel($jenis, $id)
    {
        $jenis = $this->validateJenis($jenis);
        $usulan = $this->rkbmd_model->findUsulan($id);
        $this->authorizeUsulan($usulan, FALSE);

        $detail = $this->rkbmd_model->getDetail($jenis, $id);
        $filename = clean_filename('RKBMD_' . $jenis . '_' . $usulan->nomor_usulan) . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF"; // BOM UTF-8

        $this->load->view('rkbmd/excel_' . $jenis, [
            'usulan'        => $usulan,
            'detail'        => $detail,
            'app_owner'     => $this->config->item('app_owner'),
            'app_kabupaten' => $this->config->item('app_kabupaten')
        ]);
        // Note: SKPD address and phone are not typically needed in Excel exports,
        // so we don't fetch skpd_info for excel views.
    }

    public function delete($jenis, $id)
    {
        $jenis = $this->validateJenis($jenis);
        $usulan = $this->rkbmd_model->findUsulan($id);
        $this->authorizeUsulan($usulan, TRUE);

        if ($this->rkbmd_model->deleteUsulan($id)) {
            $this->session->set_flashdata('success', 'Usulan berhasil dihapus.');
        } else {
            $this->session->set_flashdata('danger', 'Usulan tidak dapat dihapus.');
        }
        redirect("rkbmd/{$jenis}");
    }

    // ============================================================
    // PRIVATE HANDLERS
    // ============================================================

    private function _handleAddDetail($jenis, $usulanId)
    {
        $data = ['barang_id' => (int) $this->input->post('barang_id')];
        if (!$data['barang_id']) {
            $this->session->set_flashdata('danger', 'Pilih barang terlebih dahulu.');
            return;
        }

        // Mapping field per jenis
        switch ($jenis) {
            case 'pengadaan':
                $data += [
                    'program_kegiatan'      => $this->input->post('program_kegiatan', TRUE),
                    'sub_kegiatan'          => $this->input->post('sub_kegiatan', TRUE),
                    'output_kegiatan'       => $this->input->post('output_kegiatan', TRUE),
                    'usulan_jumlah'         => (int) $this->input->post('usulan_jumlah'),
                    'usulan_satuan'         => $this->input->post('usulan_satuan', TRUE) ?: 'Unit',
                    'kebutuhan_maks_jumlah' => (int) $this->input->post('kebutuhan_maks_jumlah'),
                    'kebutuhan_maks_satuan' => $this->input->post('kebutuhan_maks_satuan', TRUE) ?: 'Unit',
                    'optimalisasi_jumlah'   => (int) $this->input->post('optimalisasi_jumlah'),
                    'optimalisasi_satuan'   => $this->input->post('optimalisasi_satuan', TRUE) ?: 'Unit',
                    'kebutuhan_riil_jumlah' => (int) $this->input->post('kebutuhan_riil_jumlah'),
                    'kebutuhan_riil_satuan' => $this->input->post('kebutuhan_riil_satuan', TRUE) ?: 'Unit',
                    'harga_satuan'          => (float) $this->input->post('harga_satuan'),
                    'keterangan'            => $this->input->post('keterangan', TRUE)
                ];
                break;
            case 'pemeliharaan':
                $data += [
                    'program_kegiatan'    => $this->input->post('program_kegiatan', TRUE),
                    'sub_kegiatan'        => $this->input->post('sub_kegiatan', TRUE),
                    'output_kegiatan'     => $this->input->post('output_kegiatan', TRUE),
                    'jumlah_barang'       => (int) $this->input->post('jumlah_barang'),
                    'satuan_barang'       => $this->input->post('satuan_barang', TRUE) ?: 'Unit',
                    'status_barang'       => $this->input->post('status_barang', TRUE) ?: 'Digunakan Sendiri',
                    'kondisi_b'           => (int) $this->input->post('kondisi_b'),
                    'kondisi_rr'          => (int) $this->input->post('kondisi_rr'),
                    'kondisi_rb'          => (int) $this->input->post('kondisi_rb'),
                    'nama_pemeliharaan'   => $this->input->post('nama_pemeliharaan', TRUE),
                    'jumlah_pemeliharaan' => (int) $this->input->post('jumlah_pemeliharaan'),
                    'satuan_pemeliharaan' => $this->input->post('satuan_pemeliharaan', TRUE) ?: 'Unit',
                    'harga_satuan'        => (float) $this->input->post('harga_satuan'),
                    'keterangan'          => $this->input->post('keterangan', TRUE)
                ];
                break;
            case 'pemanfaatan':
                $hargaPerolehan = str_replace(',', '.', $this->input->post('harga_perolehan'));
                $data += [
                    'bentuk_pemanfaatan' => $this->input->post('bentuk_pemanfaatan', TRUE),
                    'no_register'        => $this->input->post('no_register', TRUE),
                    'tahun_perolehan'    => (int) $this->input->post('tahun_perolehan') ?: NULL,
                    'harga_perolehan'    => (float) $hargaPerolehan,
                    'spesifikasi'        => $this->input->post('spesifikasi', TRUE),
                    'kondisi_b'          => (int) $this->input->post('kondisi_b'),
                    'kondisi_rr'         => (int) $this->input->post('kondisi_rr'),
                    'kondisi_rb'         => (int) $this->input->post('kondisi_rb'),
                    'keterangan'         => $this->input->post('keterangan', TRUE)
                ];
                break;
            case 'pemindahtanganan':
                $data += [
                    'bentuk_pemindahtanganan' => $this->input->post('bentuk_pemindahtanganan', TRUE) ?: 'penjualan',
                    'no_register'             => $this->input->post('no_register', TRUE),
                    'spesifikasi'             => $this->input->post('spesifikasi', TRUE),
                    'tahun_perolehan'         => (int) $this->input->post('tahun_perolehan'),
                    'harga_perolehan'         => (float) $this->input->post('harga_perolehan'),
                    'keterangan'              => $this->input->post('keterangan', TRUE)
                ];
                break;
            case 'penghapusan':
                $data += [
                    'kategori_penghapusan' => $this->input->post('kategori_penghapusan', TRUE) ?: 'dipindahtangankan',
                    'no_register'          => $this->input->post('no_register', TRUE),
                    'spesifikasi'          => $this->input->post('spesifikasi', TRUE),
                    'tahun_perolehan'      => (int) $this->input->post('tahun_perolehan'),
                    'harga_perolehan'      => (float) $this->input->post('harga_perolehan'),
                    'keterangan'           => $this->input->post('keterangan', TRUE)
                ];
                break;
        }

        if ($this->rkbmd_model->addDetail($jenis, $usulanId, $data)) {
            $this->session->set_flashdata('success', 'Item berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('danger', 'Gagal menambahkan item.');
        }
    }

    private function _handleUpdateDetail($jenis, $usulanId)
    {
        $detailId = (int) $this->input->post('detail_id');
        if (!$detailId) return;

        // Pastikan detail milik usulan ini
        $detail = $this->rkbmd_model->findDetail($jenis, $detailId);
        if (!$detail || (int) $detail->usulan_id !== (int) $usulanId) {
            $this->session->set_flashdata('danger', 'Item tidak ditemukan.');
            return;
        }

        $post = $this->input->post(NULL, TRUE);
        unset($post['action'], $post['detail_id'], $post[$this->security->get_csrf_token_name()]);

        if ($this->rkbmd_model->updateDetail($jenis, $detailId, $post)) {
            $this->session->set_flashdata('success', 'Item berhasil diperbarui.');
        }
    }

    private function _handleDeleteDetail($jenis, $usulanId)
    {
        $detailId = (int) $this->input->post('detail_id');
        $detail = $this->rkbmd_model->findDetail($jenis, $detailId);
        if (!$detail || (int) $detail->usulan_id !== (int) $usulanId) {
            $this->session->set_flashdata('danger', 'Item tidak ditemukan.');
            return;
        }
        if ($this->rkbmd_model->deleteDetail($jenis, $detailId)) {
            $this->session->set_flashdata('success', 'Item berhasil dihapus.');
        }
    }

    private function _handleUpdateHeader($usulanId)
    {
        $data = [
            'tanggal_usulan' => $this->input->post('tanggal_usulan', TRUE),
            'tahun_anggaran' => (int) $this->input->post('tahun_anggaran'),
            'periode_id'     => (int) $this->input->post('periode_id'),
            'keterangan'     => $this->input->post('keterangan', TRUE),
            'is_nihil'       => $this->input->post('is_nihil') ? 1 : 0
        ];

        $usulan = $this->rkbmd_model->findUsulan($usulanId);
        if ($data['is_nihil']) {
            $detailCount = (int) count($this->rkbmd_model->getDetail($usulan->jenis_usulan, $usulanId));
            if ($detailCount > 0) {
                $this->session->set_flashdata('danger', 'Laporan nihil tidak dapat dipilih ketika usulan sudah memiliki item.');
                return;
            }
        }

        if ($this->rkbmd_model->updateUsulan($usulanId, $data)) {
            $this->session->set_flashdata('success', 'Header usulan berhasil diperbarui.');
        }
    }

    private function _handleSubmit($usulanId)
    {
        $result = $this->rkbmd_model->submit($usulanId);
        $this->session->set_flashdata($result['success'] ? 'success' : 'danger', $result['message']);
    }
}
