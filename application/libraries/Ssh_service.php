<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Ssh_service
 * Lapisan Service / Bisnis Logika untuk Modul Standar Satuan Harga (SSH) & SBU.
 * Menangani validasi state machine, validasi input harga & file, serta RLS guard.
 */
class Ssh_service
{
    protected $CI;

    /**
     * Definisi matriks transisi state yang sah
     */
    protected $allowedTransitions = [
        'Draft'        => ['Diajukan'],
        'Diajukan'     => ['Diverifikasi', 'Direvisi'],
        'Direvisi'     => ['Diajukan'],
        'Diverifikasi' => ['Ditetapkan'],
        'Ditetapkan'   => [] // Terminal state (Terkunci secara permanen)
    ];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('ssh_model');
    }

    /**
     * Validasi transisi state
     */
    public function canTransition($fromStatus, $toStatus, $userRole)
    {
        // 1. Cek apakah transisi terdefinisi dalam matriks
        if (!isset($this->allowedTransitions[$fromStatus])) {
            return ['allowed' => FALSE, 'message' => "Status awal '{$fromStatus}' tidak valid."];
        }

        if (!in_array($toStatus, $this->allowedTransitions[$fromStatus], TRUE)) {
            return [
                'allowed' => FALSE, 
                'message' => "Transisi status dari '{$fromStatus}' ke '{$toStatus}' tidak diizinkan oleh sistem."
            ];
        }

        // 2. Cek kewenangan Role (RLS Policy)
        switch ($toStatus) {
            case 'Diajukan':
                if (!in_array($userRole, ['operator_skpd', 'skpd', 'admin'], TRUE)) {
                    return ['allowed' => FALSE, 'message' => 'Hanya operator SKPD yang berhak mengajukan usulan.'];
                }
                break;

            case 'Diverifikasi':
            case 'Direvisi':
                if (!in_array($userRole, ['verifikator', 'admin'], TRUE)) {
                    return ['allowed' => FALSE, 'message' => 'Hanya verifikator yang berhak menyetujui atau meminta revisi usulan.'];
                }
                break;

            case 'Ditetapkan':
                if (!in_array($userRole, ['penetap', 'pimpinan', 'admin'], TRUE)) {
                    return ['allowed' => FALSE, 'message' => 'Hanya pejabat penetap yang berhak menetapkan standar harga resmi.'];
                }
                break;
        }

        return ['allowed' => TRUE, 'message' => 'Transisi valid.'];
    }

    /**
     * Validasi data input form usulan
     */
    public function validateInput($postData)
    {
        $errors = [];

        // Validasi Uraian
        $uraian = trim($postData['uraian'] ?? '');
        if (empty($uraian)) {
            $errors[] = 'Uraian barang/jasa wajib diisi.';
        } elseif (mb_strlen($uraian) < 3) {
            $errors[] = 'Uraian barang/jasa minimal 3 karakter.';
        }

        // Validasi Kategori
        $kategori = trim($postData['kategori'] ?? '');
        if (empty($kategori)) {
            $errors[] = 'Kategori wajib dipilih.';
        }

        // Validasi Satuan
        $satuan = trim($postData['satuan'] ?? '');
        if (empty($satuan)) {
            $errors[] = 'Satuan barang/jasa wajib diisi.';
        }

        // Validasi Harga Usulan (Tidak boleh kosong, tidak boleh minus/nol)
        $rawHarga = str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], $postData['harga_usulan'] ?? '');
        if (!is_numeric($rawHarga)) {
            $errors[] = 'Harga usulan harus berupa angka yang valid.';
        } else {
            $harga = (float) $rawHarga;
            if ($harga <= 0) {
                $errors[] = 'Harga usulan tidak boleh kosong, nol, atau bernilai negatif (harus lebih besar dari Rp 0).';
            }
        }

        // Validasi Spesifikasi
        $spesifikasi = trim($postData['spesifikasi'] ?? '');
        if (empty($spesifikasi)) {
            $errors[] = 'Spesifikasi teknis wajib diisi.';
        }

        $rawAcuan = str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], $postData['harga_acuan_master'] ?? '');
        $hargaAcuan = is_numeric($rawAcuan) && (float)$rawAcuan > 0 ? (float)$rawAcuan : NULL;

        return [
            'isValid'  => empty($errors),
            'errors'   => $errors,
            'cleanData'=> [
                'master_standar_id' => !empty($postData['master_standar_id']) ? (int)$postData['master_standar_id'] : NULL,
                'tipe'              => in_array($postData['tipe'] ?? '', ['SSH', 'SBU']) ? $postData['tipe'] : 'SSH',
                'kategori'          => $kategori,
                'kode_kelompok'     => trim($postData['kode_kelompok'] ?? ''),
                'uraian'            => $uraian,
                'spesifikasi'       => $spesifikasi,
                'satuan'            => $satuan,
                'kode_rekening'     => trim($postData['kode_rekening'] ?? ''),
                'nama_rekening'     => trim($postData['nama_rekening'] ?? ''),
                'harga_usulan'      => isset($harga) ? $harga : 0,
                'harga_acuan_master'=> $hargaAcuan,
                'tahun_anggaran'    => (int) ($postData['tahun_anggaran'] ?? date('Y'))
            ]
        ];
    }

    /**
     * Upload File Lampiran (Katalog / Bukti Survey Harga)
     */
    public function handleFileUpload($fieldName = 'file_lampiran')
    {
        if (empty($_FILES[$fieldName]['name'])) {
            return ['hasFile' => FALSE, 'fileName' => NULL, 'origName' => NULL];
        }

        $uploadPath = FCPATH . 'uploads/ssh_sbu/';
        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0755, TRUE);
        }

        $config = [
            'upload_path'   => $uploadPath,
            'allowed_types' => 'pdf|jpg|jpeg|png|docx|doc|xlsx|xls',
            'max_size'      => 5120, // 5MB
            'encrypt_name'  => TRUE
        ];

        $this->CI->load->library('upload', $config);
        $this->CI->upload->initialize($config);

        if (!$this->CI->upload->do_upload($fieldName)) {
            return [
                'hasFile' => FALSE, 
                'error'   => $this->CI->upload->display_errors('', '')
            ];
        }

        $uploadData = $this->CI->upload->data();
        return [
            'hasFile'  => TRUE,
            'fileName' => $uploadData['file_name'],
            'origName' => $uploadData['client_name']
        ];
    }
}
