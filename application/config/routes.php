<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ===== Default & Error Routes =====
$route['default_controller'] = 'login';
$route['404_override']       = 'errors/page_not_found';
$route['translate_uri_dashes'] = FALSE;

// ===== Authentication =====
$route['login']  = 'login/index';
$route['logout'] = 'login/logout';

// ===== Dashboard =====
$route['dashboard'] = 'dashboard/index';

// ===== Modul RKBMD (5 jenis) =====
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)']               = 'rkbmd/index/$1';
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)/create']        = 'rkbmd/create/$1';
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)/edit/(:num)']   = 'rkbmd/edit/$1/$2';
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)/detail/(:num)'] = 'rkbmd/detail/$1/$2';
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)/cetak/(:num)']  = 'rkbmd/cetak/$1/$2';
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)/excel/(:num)']  = 'rkbmd/excel/$1/$2';
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)/delete/(:num)'] = 'rkbmd/delete/$1/$2';
$route['rkbmd/(pengadaan|pemeliharaan|pemanfaatan|pemindahtanganan|penghapusan)/upload/(:num)'] = 'rkbmd/upload/$1/$2';

// ===== Verifikasi BPKAD =====
$route['verifikasi']                       = 'verifikasi/index';
$route['verifikasi/detail/(:num)']         = 'verifikasi/detail/$1';
$route['verifikasi/proses/(:num)']         = 'verifikasi/proses/$1';

// ===== Modul Standar Satuan Harga (SSH) =====
$route['ssh']                               = 'ssh/index';
$route['ssh/usulan']                        = 'ssh/usulan';
$route['ssh/tambah']                        = 'ssh/tambah';
$route['ssh/edit/(:num)']                   = 'ssh/edit/$1';
$route['ssh/kirim/(:num)']                  = 'ssh/kirim/$1';
$route['ssh/hapus/(:num)']                  = 'ssh/hapus/$1';
$route['ssh/verifikasi']                    = 'ssh/verifikasi';
$route['ssh/proses-verifikasi/(:num)']      = 'ssh/proses_verifikasi/$1';
$route['ssh/penetapan']                     = 'ssh/penetapan';
$route['ssh/proses-penetapan/(:num)']       = 'ssh/proses_penetapan/$1';
$route['ssh/master_data']                   = 'ssh/master_data';
$route['ssh/jadwal']                        = 'ssh/jadwal';
$route['ssh/jadwal/simpan']                 = 'ssh/simpan_jadwal';
$route['ssh/jadwal/toggle/(:num)']          = 'ssh/toggle_jadwal/$1';
$route['ssh/jadwal/hapus/(:num)']           = 'ssh/hapus_jadwal/$1';
$route['ssh/detail/(:num)']                 = 'ssh/detail/$1';
$route['ssh/download/(:num)']               = 'ssh/download_lampiran/$1';
$route['ssh/download/(:num)/(:num)']        = 'ssh/download_lampiran/$1/$2';
$route['ssh/api/transisi']                  = 'ssh/api_transisi_status';

// ===== Modul Standar Biaya Umum (SBU) =====
$route['sbu']                               = 'sbu/index';
$route['sbu/usulan']                        = 'sbu/usulan';
$route['sbu/tambah']                        = 'sbu/tambah';
$route['sbu/edit/(:num)']                   = 'sbu/edit/$1';
$route['sbu/kirim/(:num)']                  = 'sbu/kirim/$1';
$route['sbu/hapus/(:num)']                  = 'sbu/hapus/$1';
$route['sbu/verifikasi']                    = 'sbu/verifikasi';
$route['sbu/proses-verifikasi/(:num)']      = 'sbu/proses_verifikasi/$1';
$route['sbu/penetapan']                     = 'sbu/penetapan';
$route['sbu/proses-penetapan/(:num)']       = 'sbu/proses_penetapan/$1';
$route['sbu/master_data']                   = 'sbu/master_data';
$route['sbu/jadwal']                        = 'sbu/jadwal';
$route['sbu/jadwal/simpan']                 = 'sbu/simpan_jadwal';
$route['sbu/jadwal/toggle/(:num)']          = 'sbu/toggle_jadwal/$1';
$route['sbu/jadwal/hapus/(:num)']           = 'sbu/hapus_jadwal/$1';
$route['sbu/detail/(:num)']                 = 'sbu/detail/$1';
$route['sbu/download/(:num)']               = 'sbu/download_lampiran/$1';
$route['sbu/download/(:num)/(:num)']        = 'sbu/download_lampiran/$1/$2';
$route['sbu/api/transisi']                  = 'sbu/api_transisi_status';

// ===== Master Data =====
$route['master/skpd']         = 'master/skpd';
$route['master/barang']       = 'master/barang';
$route['master/periode']      = 'master/periode';
$route['master/user']         = 'master/user';
$route['master/akun_belanja'] = 'master/akun_belanja';
$route['master/akun']         = 'master/akun_belanja';

// ===== Laporan =====
$route['laporan']                     = 'laporan/index';
$route['laporan/rekap/(:any)']        = 'laporan/rekap/$1';
$route['laporan/export/(:any)/(:any)'] = 'laporan/export/$1/$2';

// ===== Profile & Settings =====
$route['profile']           = 'user/profile';
$route['profile/update']    = 'user/update_profile';
$route['profile/password']  = 'user/change_password';

// ===== AJAX Endpoints =====
$route['ajax/barang/search']              = 'ajax/search_barang';
$route['ajax/bmd/search']                 = 'ajax/search_bmd';
$route['ajax/akun_belanja/search']        = 'ajax/search_akun_belanja';
$route['ajax/standar_harga/search']       = 'ajax/search_standar_harga';
$route['ajax/standar_harga/detail/(:num)']= 'ajax/detail_standar_harga/$1';
$route['ajax/notif/list']                 = 'ajax/notifikasi';
$route['ajax/notif/read/(:num)']          = 'ajax/baca_notif/$1';
