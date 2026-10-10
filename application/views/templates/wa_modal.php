<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Modal Dialog Notifikasi WhatsApp SIPA -->
<div class="modal fade" id="modalSipaWa" tabindex="-1" aria-labelledby="modalSipaWaLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:20px;">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalSipaWaLabel">Kirim Pemberitahuan WhatsApp ke Operator SKPD</h5>
                        <small class="opacity-75" id="waSubTitle">Percepat koordinasi verifikasi dan perbaikan usulan aset</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="waLoadingState" class="text-center py-4 d-none">
                    <div class="spinner-border text-success mb-2" role="status"></div>
                    <p class="text-muted small mb-0">Menyiapkan kontak operator dan draf pesan resmi...</p>
                </div>

                <div id="waFormState">
                    <!-- Alert Penerima -->
                    <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-3">
                        <div>
                            <span class="text-muted small d-block">SKPD Pengusul & Operator:</span>
                            <strong class="text-dark" id="waPenerimaNama">-</strong>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border" id="waModulBadge">-</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                <i class="bi bi-telephone-outbound text-success me-1"></i>Nomor WhatsApp Tujuan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted small"><i class="bi bi-whatsapp text-success"></i></span>
                                <input type="text" class="form-control" id="waTargetPhone" placeholder="Contoh: 08123456789 atau 628123456789">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.75rem;">
                                Nomor diambil dari profil user operator atau nomor dinas SKPD. Anda dapat mengubahnya jika perlu.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">
                                <i class="bi bi-card-text text-primary me-1"></i>Nomor / Kode Usulan
                            </label>
                            <input type="text" class="form-control bg-light" id="waKodeUsulan" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-dark mb-0">
                                <i class="bi bi-chat-text text-success me-1"></i>Isi Pesan Pemberitahuan WhatsApp
                            </label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btnSalinPesan" style="font-size: 0.8rem;">
                                <i class="bi bi-clipboard me-1"></i>Salin Teks
                            </button>
                        </div>
                        <textarea class="form-control font-monospace" id="waMessageContent" rows="8" style="font-size: 0.83rem; line-height: 1.5;"></textarea>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">
                            Format mendukung gaya WhatsApp (*tebal*, _miring_, baris baru). Anda dapat mengedit teks sebelum dikirim.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm d-none" id="btnKirimGateway">
                        <i class="bi bi-send-check me-1"></i>Kirim via Gateway Otomatis
                    </button>
                    <button type="button" class="btn btn-success btn-sm fw-bold px-3" id="btnBukaWhatsApp">
                        <i class="bi bi-whatsapp me-1"></i>Buka WhatsApp Web / App
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.SipaWa = {
    modalEl: null,
    bootstrapModal: null,

    init: function() {
        this.modalEl = document.getElementById('modalSipaWa');
        if (this.modalEl && typeof bootstrap !== 'undefined') {
            this.bootstrapModal = new bootstrap.Modal(this.modalEl);
        }

        // Event Salin Teks
        $('#btnSalinPesan').off('click').on('click', function() {
            const text = $('#waMessageContent').val();
            navigator.clipboard.writeText(text).then(function() {
                const btn = $('#btnSalinPesan');
                const orig = btn.html();
                btn.html('<i class="bi bi-check2 text-success me-1"></i>Tersalin!');
                setTimeout(function() { btn.html(orig); }, 2000);
            });
        });

        // Event Buka WhatsApp Web / App
        $('#btnBukaWhatsApp').off('click').on('click', function() {
            let phone = ($('#waTargetPhone').val() || '').replace(/[^0-9]/g, '');
            if (!phone) {
                alert('Silakan masukkan nomor WhatsApp tujuan terlebih dahulu.');
                $('#waTargetPhone').focus();
                return;
            }
            if (phone.startsWith('08')) phone = '62' + phone.substring(1);
            if (phone.startsWith('8')) phone = '62' + phone;

            const message = $('#waMessageContent').val();
            const waUrl = 'https://api.whatsapp.com/send?phone=' + phone + '&text=' + encodeURIComponent(message);
            window.open(waUrl, '_blank');
        });

        // Event Kirim via Gateway
        $('#btnKirimGateway').off('click').on('click', function() {
            const phone = $('#waTargetPhone').val();
            const message = $('#waMessageContent').val();
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...');

            $.ajax({
                url: '<?= site_url("wa/ajax_send_gateway") ?>',
                type: 'POST',
                data: {
                    phone: phone,
                    message: message,
                    rkbmd_csrf_token: '<?= $this->security->get_csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-send-check me-1"></i>Kirim via Gateway Otomatis');
                    if (res.success) {
                        alert('Berhasil: ' + res.message);
                    } else {
                        alert('Gagal: ' + res.message + '\n\nSilakan gunakan tombol "Buka WhatsApp Web / App".');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-send-check me-1"></i>Kirim via Gateway Otomatis');
                    alert('Terjadi kesalahan menghubungi server gateway.');
                }
            });
        });
    },

    /**
     * Buka modal WhatsApp dengan parameter otomatis
     * @param {Object} opts { module: 'rkbmd'|'ssh'|'sbu', id: 12, action_type: 'revisi'|'setuju'|'tolak', catatan: '...' }
     */
    open: function(opts) {
        if (!this.bootstrapModal) this.init();

        $('#waLoadingState').removeClass('d-none');
        $('#waFormState').addClass('d-none');
        this.bootstrapModal.show();

        const self = this;
        $.ajax({
            url: '<?= site_url("wa/ajax_prepare") ?>',
            type: 'POST',
            data: {
                module: opts.module || 'rkbmd',
                id: opts.id,
                action_type: opts.action_type || '',
                catatan: opts.catatan || '',
                rkbmd_csrf_token: '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(res) {
                $('#waLoadingState').addClass('d-none');
                $('#waFormState').removeClass('d-none');

                if (res.success) {
                    $('#waPenerimaNama').text(res.nama_skpd + ' (' + res.nama_operator + ')');
                    $('#waModulBadge').text(res.modul);
                    $('#waTargetPhone').val(res.no_wa);
                    $('#waKodeUsulan').val(res.nomor_usulan);
                    $('#waMessageContent').val(res.pesan);

                    if (res.gateway_enabled) {
                        $('#btnKirimGateway').removeClass('d-none');
                    } else {
                        $('#btnKirimGateway').addClass('d-none');
                    }
                } else {
                    alert(res.message || 'Gagal menyiapkan data notifikasi WhatsApp.');
                    self.bootstrapModal.hide();
                }
            },
            error: function() {
                $('#waLoadingState').addClass('d-none');
                alert('Terjadi kesalahan memuat data.');
                self.bootstrapModal.hide();
            }
        });
    }
};

window.addEventListener('load', function() {
    window.SipaWa.init();
});
</script>
