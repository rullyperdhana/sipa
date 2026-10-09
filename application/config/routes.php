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

// ===== Modul Standar Satuan Harga (SSH) & Standar Biaya Umum (SBU) =====
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
$route['ssh/detail/(:num)']                 = 'ssh/detail/$1';
$route['ssh/download/(:num)']               = 'ssh/download_lampiran/$1';
$route['ssh/api/transisi']                  = 'ssh/api_transisi_status';

// ===== Master Data =====
$route['master/skpd']    = 'master/skpd';
$route['master/barang']  = 'master/barang';
$route['master/periode'] = 'master/periode';
$route['master/user']    = 'master/user';

// ===== Laporan =====
$route['laporan']                     = 'laporan/index';
$route['laporan/rekap/(:any)']        = 'laporan/rekap/$1';
$route['laporan/export/(:any)/(:any)'] = 'laporan/export/$1/$2';

// ===== Profile & Settings =====
$route['profile']           = 'user/profile';
$route['profile/update']    = 'user/update_profile';
$route['profile/password']  = 'user/change_password';

// ===== AJAX Endpoints =====
$route['ajax/barang/search']    = 'ajax/search_barang';
$route['ajax/bmd/search']       = 'ajax/search_bmd';
$route['ajax/notif/list']       = 'ajax/notifikasi';
$route['ajax/notif/read/(:num)']= 'ajax/baca_notif/$1';
