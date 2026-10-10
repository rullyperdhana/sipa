<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="page-title"><i class="bi bi-whatsapp text-success me-2"></i>Pengaturan Integrasi WhatsApp</h1>
            <p class="page-subtitle">Konfigurasi notifikasi WhatsApp untuk mempercepat penyampaian informasi usulan ke operator SKPD.</p>
        </div>
        <a href="<?= site_url('master/user') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-people-fill me-1"></i>Data Pengguna
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Form Pengaturan -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-sliders me-2 text-primary"></i>Parameter Konfigurasi WhatsApp
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="post" action="<?= site_url('wa/settings') ?>">
                    <?= csrf_input() ?>

                    <!-- Mode Operasional -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Mode Pengiriman Notifikasi</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light-subtle">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="wa_mode" id="mode_direct" value="direct" <?= ($settings['wa_mode'] ?? 'direct') === 'direct' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="mode_direct">
                                            <i class="bi bi-chat-dots-fill text-success me-1"></i>Direct Click-to-Chat (Rekomendasi)
                                        </label>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0">
                                        <strong>100% Gratis & Praktis.</strong> Verifikator mengklik tombol WhatsApp untuk membuka tab WhatsApp Web/App dengan nomor operator dan draf pesan resmi yang telah terisi otomatis.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light-subtle">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="wa_mode" id="mode_gateway" value="gateway" <?= ($settings['wa_mode'] ?? '') === 'gateway' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer" for="mode_gateway">
                                            <i class="bi bi-broadcast text-primary me-1"></i>WhatsApp Gateway API
                                        </label>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0">
                                        Pengiriman otomatis di latar belakang (*headless background*) menggunakan server API pihak ketiga (seperti Fonnte, WABlas, atau WAGW instansi).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Konfigurasi Gateway API (Opsional) -->
                    <div id="sectionGatewaySettings" class="<?= ($settings['wa_mode'] ?? 'direct') === 'direct' ? 'opacity-75' : '' ?>">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-hdd-network me-2 text-info"></i>Konfigurasi API Gateway (Jika Menggunakan Server Gateway)</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Penyedia / Format Gateway</label>
                                <select name="wa_gateway_provider" class="form-select">
                                    <option value="fonnte" <?= ($settings['wa_gateway_provider'] ?? '') === 'fonnte' ? 'selected' : '' ?>>Fonnte (fonnte.com)</option>
                                    <option value="generic" <?= ($settings['wa_gateway_provider'] ?? '') === 'generic' ? 'selected' : '' ?>>Generic HTTP POST API</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">API Endpoint URL</label>
                                <input type="url" name="wa_gateway_url" class="form-control" value="<?= e($settings['wa_gateway_url'] ?? 'https://api.fonnte.com/send') ?>" placeholder="https://api.fonnte.com/send">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">API Token / Secret Key</label>
                                <input type="text" name="wa_gateway_token" class="form-control font-monospace" value="<?= e($settings['wa_gateway_token'] ?? '') ?>" placeholder="Masukkan Token API jika Anda memiliki akun Gateway">
                                <div class="form-text text-muted">Biarkan kosong jika hanya menggunakan tombol Direct WhatsApp Web.</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Footer Pesan -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Teks Kaki Pengirim Resmi (Footer)</label>
                        <input type="text" name="wa_sender_footer" class="form-control" value="<?= e($settings['wa_sender_footer'] ?? 'BPKAD Kabupaten Tapin') ?>">
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i>Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel Uji Coba Cepat -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm border-top border-4 border-success">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-success">
                    <i class="bi bi-send-check me-2"></i>Uji Coba Kirim Notifikasi WA
                </h6>
            </div>
            <div class="card-body p-3">
                <p class="text-muted small">
                    Uji coba format pesan dan pengiriman ke nomor ponsel / WhatsApp Anda untuk memastikan pesan tampil sempurna.
                </p>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nomor WhatsApp Anda</label>
                    <input type="text" id="testWaPhone" class="form-control form-control-sm" placeholder="08xxxxxxxxxx">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Contoh Kasus</label>
                    <select id="testWaScenario" class="form-select form-select-sm">
                        <option value="revisi">⚠️ Usulan Perlu Revisi / Perbaikan</option>
                        <option value="setuju">✅ Usulan Disetujui / Selesai</option>
                        <option value="penetapan">🛡️ Standar Harga Ditetapkan Resmi (SK)</option>
                    </select>
                </div>

                <button type="button" class="btn btn-success btn-sm w-100 fw-semibold" id="btnTestWaDirect">
                    <i class="bi bi-whatsapp me-1"></i>Tes Buka WhatsApp Langsung
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3 bg-light">
            <div class="card-body p-3 small text-muted">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1 text-primary"></i>Catatan Operasional</h6>
                <ul class="ps-3 mb-0 vstack gap-1">
                    <li>Nomor WhatsApp operator SKPD dapat didaftarkan pada menu <a href="<?= site_url('master/user') ?>" class="fw-semibold">Manajemen Pengguna</a>.</li>
                    <li>Jika user belum mengisi nomor WA pribadi, sistem otomatis menggunakan nomor telepon dinas dari tabel SKPD.</li>
                    <li>Format pesan menyertakan nomor usulan, catatan perbaikan BPKAD, serta tautan langsung untuk mempercepat respon SKPD.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    $('input[name="wa_mode"]').on('change', function() {
        if ($(this).val() === 'gateway') {
            $('#sectionGatewaySettings').removeClass('opacity-75');
        } else {
            $('#sectionGatewaySettings').addClass('opacity-75');
        }
    });

    $('#btnTestWaDirect').on('click', function() {
        let phone = ($('#testWaPhone').val() || '').replace(/[^0-9]/g, '');
        if (!phone) {
            alert('Masukkan nomor WhatsApp terlebih dahulu.');
            $('#testWaPhone').focus();
            return;
        }
        if (phone.startsWith('08')) phone = '62' + phone.substring(1);
        if (phone.startsWith('8')) phone = '62' + phone;

        const scn = $('#testWaScenario').val();
        let msg = "🏛️ *SIPA - PEMERINTAH KABUPATEN TAPIN*\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        msg += "Yth. *Bapak/Ibu Operator SKPD*\n\n";

        if (scn === 'revisi') {
            msg += "⚠️ *PEMBERITAHUAN PERBAIKAN USULAN (REVISI)*\n\n";
            msg += "Terdapat usulan yang memerlukan perbaikan/revisi:\n";
            msg += "• *Nomor Usulan:* TEST-2026-0001\n";
            msg += "• *Status:* ⚠️ *PERLU REVISI SKPD*\n\n";
            msg += "📝 *Catatan Verifikator BPKAD:*\n";
            msg += "_Mohon lengkapi berkas survey harga pasar dan perbaiki spesifikasi teknis._\n\n";
        } else if (scn === 'setuju') {
            msg += "✅ *PEMBERITAHUAN USULAN DISETUJUI*\n\n";
            msg += "Usulan *TEST-2026-0001* telah selesai diverifikasi dan *DISETUJUI* oleh BPKAD.\n\n";
        } else {
            msg += "🎉 *PENETAPAN RESMI STANDAR HARGA*\n\n";
            msg += "Usulan Standar Harga telah *RESMI DITETAPKAN* menjadi Master Katalog Daerah Kab. Tapin.\n\n";
        }

        msg += "Silakan login ke aplikasi SIPA:\n👉 <?= site_url('login') ?>\n\n";
        msg += "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        msg += "Bidang Pengelolaan Aset Daerah - BPKAD Kab. Tapin";

        const url = 'https://api.whatsapp.com/send?phone=' + phone + '&text=' + encodeURIComponent(msg);
        window.open(url, '_blank');
    });
});
</script>
