/**
 * ============================================================================
 * MODUL STANDAR SATUAN HARGA (SSH) & STANDAR BIAYA UMUM (SBU)
 * Frontend State Management, Form Validation, & API Transition Hooks
 * SIPA (Sistem Informasi Pengelolaan Aset) - BPKAD Kabupaten Tapin
 * ============================================================================
 */

(function (window, $, Swal) {
    'use strict';

    // Global namespace modul SSH
    const SshModule = {};

    // =========================================================================
    // 1. STATE MANAGER & API CLIENT HOOKS
    // =========================================================================

    /**
     * API Handler untuk transisi perubahan status:
     * Draft -> Diajukan -> Direvisi -> Diverifikasi -> Ditetapkan
     */
    SshModule.api = {
        /**
         * Update status usulan via AJAX API endpoint
         * @param {number} id - ID usulan
         * @param {string} targetStatus - Status tujuan
         * @param {object} extraData - { catatan, harga_ditetapkan }
         * @returns {Promise}
         */
        changeStatus: function (id, targetStatus, extraData = {}) {
            const prefix = extraData.prefix || 'ssh';
            const url = (window.appConfig ? window.appConfig.baseUrl : '/') + prefix + '/api/transisi';
            const csrfName = window.appConfig ? window.appConfig.csrfName : 'csrf_test_name';
            const csrfHash = window.appConfig ? window.appConfig.csrfHash : '';

            const payload = {
                id: id,
                target_status: targetStatus,
                catatan: extraData.catatan || '',
                harga_ditetapkan: extraData.harga_ditetapkan || null,
                [csrfName]: csrfHash
            };

            return $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: payload,
                beforeSend: function () {
                    if (Swal) {
                        Swal.showLoading();
                    }
                }
            }).done(function (res) {
                // Perbarui CSRF Token di memory untuk request berikutnya
                if (res.csrfName && res.csrfHash && window.appConfig) {
                    window.appConfig.csrfName = res.csrfName;
                    window.appConfig.csrfHash = res.csrfHash;
                    $('meta[name="csrf-token"]').attr('content', res.csrfHash);
                }
            });
        },

        /**
         * Ambil detail data usulan & riwayat audit log
         * @param {number} id
         * @param {string} prefix
         * @returns {Promise}
         */
        getDetail: function (id, prefix = 'ssh') {
            const url = (window.appConfig ? window.appConfig.baseUrl : '/') + prefix + '/detail/' + id;
            return $.getJSON(url);
        }
    };

    // =========================================================================
    // 2. HELPER FORMATTING & VALIDASI DASAR FORM
    // =========================================================================

    SshModule.helpers = {
        /**
         * Format angka ke string rupiah (Contoh: 1500000 -> 1.500.000)
         */
        formatRupiah: function (angka) {
            let numberString = angka.toString().replace(/[^,\d]/g, '');
            let split = numberString.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        },

        /**
         * Parse string Rupiah ke float murni
         */
        parseRupiah: function (rupiahStr) {
            if (!rupiahStr) return 0;
            let cleaned = rupiahStr.toString().replace(/\./g, '').replace(/,/g, '.').replace(/[^\d.]/g, '');
            let val = parseFloat(cleaned);
            return isNaN(val) ? 0 : val;
        },

        /**
         * Validasi input dasar form (Mencegah harga kosong, nol, atau minus)
         */
        validateForm: function ($form) {
            let isValid = true;
            let errors = [];

            // 1. Validasi Uraian
            const $uraian = $form.find('#uraian');
            if ($uraian.length) {
                const valUraian = $.trim($uraian.val());
                if (!valUraian || valUraian.length < 3) {
                    $uraian.addClass('is-invalid');
                    errors.push('Uraian item wajib diisi minimal 3 karakter.');
                    isValid = false;
                } else {
                    $uraian.removeClass('is-invalid').addClass('is-valid');
                }
            }

            // 2. Validasi Kategori
            const $kategori = $form.find('#kategori');
            if ($kategori.length) {
                if (!$kategori.val()) {
                    $kategori.addClass('is-invalid');
                    errors.push('Kategori item wajib dipilih.');
                    isValid = false;
                } else {
                    $kategori.removeClass('is-invalid').addClass('is-valid');
                }
            }

            // 3. Validasi Harga Usulan (Tidak boleh kosong, tidak boleh 0, tidak boleh minus)
            const $harga = $form.find('#harga_usulan');
            if ($harga.length) {
                const hargaVal = SshModule.helpers.parseRupiah($harga.val());
                if (hargaVal <= 0) {
                    $harga.addClass('is-invalid');
                    $form.find('#hargaFeedback').text('Harga usulan tidak boleh kosong, nol, atau minus (harus > Rp 0).');
                    errors.push('Harga usulan harus bernilai lebih dari Rp 0.');
                    isValid = false;
                } else {
                    $harga.removeClass('is-invalid').addClass('is-valid');
                }
            }

            // 4. Validasi Satuan
            const $satuan = $form.find('#satuan');
            if ($satuan.length) {
                if (!$.trim($satuan.val())) {
                    $satuan.addClass('is-invalid');
                    errors.push('Satuan item wajib diisi.');
                    isValid = false;
                } else {
                    $satuan.removeClass('is-invalid').addClass('is-valid');
                }
            }

            // 5. Validasi Spesifikasi
            const $spek = $form.find('#spesifikasi');
            if ($spek.length) {
                if (!$.trim($spek.val())) {
                    $spek.addClass('is-invalid');
                    errors.push('Spesifikasi teknis wajib diisi.');
                    isValid = false;
                } else {
                    $spek.removeClass('is-invalid').addClass('is-valid');
                }
            }

            // 6. Validasi 3 Berkas Bukti Survey Harga Pasar / Brosur Resmi (Wajib Terisi & Maks 5MB)
            const fileSlots = [
                { id: 'file_lampiran', label: 'Bukti Survey 1 / Brosur Toko 1' },
                { id: 'file_lampiran_2', label: 'Bukti Survey 2 / Brosur Toko 2' },
                { id: 'file_lampiran_3', label: 'Bukti Survey 3 / Brosur Toko 3' }
            ];

            fileSlots.forEach(function (slot) {
                const $file = $form.find('#' + slot.id);
                if ($file.length) {
                    const hasExisting = $file.attr('data-has-existing') === '1';
                    const hasSelected = $file[0].files && $file[0].files.length > 0;

                    if (!hasExisting && !hasSelected) {
                        $file.addClass('is-invalid');
                        errors.push(slot.label + ' wajib diunggah.');
                        isValid = false;
                    } else if (hasSelected) {
                        const file = $file[0].files[0];
                        const maxSize = 5 * 1024 * 1024; // 5MB
                        if (file.size > maxSize) {
                            $file.addClass('is-invalid');
                            errors.push('Ukuran ' + slot.label + ' melebihi batas maksimal 5MB.');
                            isValid = false;
                        } else {
                            $file.removeClass('is-invalid').addClass('is-valid');
                        }
                    } else {
                        $file.removeClass('is-invalid');
                    }
                }
            });

            return { isValid: isValid, errors: errors };
        }
    };

    // =========================================================================
    // 3. EVENT HANDLERS & USER INTERFACES
    // =========================================================================

    $(function () {

        // Auto formatting input dengan atribut [data-format-rupiah]
        $(document).on('keyup input', '[data-format-rupiah]', function () {
            let raw = $(this).val();
            let parsed = SshModule.helpers.parseRupiah(raw);
            if (parsed > 0) {
                $(this).val(SshModule.helpers.formatRupiah(parsed));
            }
        });

        // Realtime removal of is-invalid on selecting survey file
        $(document).on('change', '.input-survey-file', function () {
            if (this.files && this.files.length > 0) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            }
        });

        // Form Submit Handler dengan Validasi
        $('#formSshUsulan').on('submit', function (e) {
            const validation = SshModule.helpers.validateForm($(this));
            if (!validation.isValid) {
                e.preventDefault();
                if (Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Formulir Gagal',
                        html: '<ul class="text-start mb-0"><li>' + validation.errors.join('</li><li>') + '</li></ul>',
                        confirmButtonColor: '#1e6091'
                    });
                } else {
                    alert('Validasi Gagal:\n- ' + validation.errors.join('\n- '));
                }
                return false;
            }
        });

        // ---------------------------------------------------------------------
        // AKSI 1: KIRIM USULAN (Role: operator_skpd) -> Draft / Direvisi -> Diajukan
        // ---------------------------------------------------------------------
        $(document).on('click', '.btn-kirim-usulan', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const kode = $(this).data('kode');
            const uraian = $(this).data('uraian');
            const prefix = $(this).data('prefix') || 'ssh';

            const confirmAction = function () {
                SshModule.api.changeStatus(id, 'Diajukan', { prefix: prefix })
                    .done(function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Dikirim!',
                                text: res.message || 'Usulan telah dikirim ke BPKAD untuk diverifikasi.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function () {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal Mengirim Usulan', text: res.message });
                        }
                    })
                    .fail(function (xhr) {
                        const errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: errMsg });
                    });
            };

            if (Swal) {
                Swal.fire({
                    title: 'Kirim Usulan ke BPKAD?',
                    html: `Usulan <strong>${kode}</strong> (${uraian}) akan dikirim ke verifikator BPKAD.<br><small class="text-muted">Setelah dikirim, data akan berstatus <strong>Diajukan</strong> dan terkunci dari perubahan.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ya, Kirim Sekarang',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        confirmAction();
                    }
                });
            } else if (confirm(`Kirim usulan ${kode} ke BPKAD?`)) {
                confirmAction();
            }
        });

        // ---------------------------------------------------------------------
        // AKSI 2: VERIFIKASI (Role: verifikator) -> Diajukan -> Diverifikasi / Direvisi
        // ---------------------------------------------------------------------

        // Modal Setujui
        $(document).on('click', '.btn-setujui-modal', function () {
            const id = $(this).data('id');
            const kode = $(this).data('kode');
            const uraian = $(this).data('uraian');
            const harga = $(this).data('harga');
            const prefix = $(this).data('prefix') || 'ssh';

            $('#formSetujuiUsulan').attr('action', (window.appConfig ? window.appConfig.baseUrl : '/') + prefix + '/proses-verifikasi/' + id);
            $('#setujuiKode').text(kode);
            $('#setujuiUraian').text(uraian);
            $('#setujuiHarga').val(SshModule.helpers.formatRupiah(harga));
            $('#setujuiCatatan').val('');

            const modal = new bootstrap.Modal(document.getElementById('modalSetujui'));
            modal.show();
        });

        // Modal Revisi
        $(document).on('click', '.btn-revisi-modal', function () {
            const id = $(this).data('id');
            const kode = $(this).data('kode');
            const uraian = $(this).data('uraian');
            const prefix = $(this).data('prefix') || 'ssh';

            $('#formRevisiUsulan').attr('action', (window.appConfig ? window.appConfig.baseUrl : '/') + prefix + '/proses-verifikasi/' + id);
            $('#revisiKode').text(kode);
            $('#revisiUraian').text(uraian);
            $('#catatan_verifikator').val('');

            const modal = new bootstrap.Modal(document.getElementById('modalRevisi'));
            modal.show();
        });

        // ---------------------------------------------------------------------
        // AKSI 3: PENETAPAN HARGA (Role: penetap) -> Diverifikasi -> Ditetapkan
        // ---------------------------------------------------------------------
        $(document).on('submit', '.form-tetapkan-usulan', function (e) {
            const $form = this;
            const kode = $(this).data('kode');
            const uraian = $(this).data('uraian');

            if (Swal) {
                e.preventDefault();
                Swal.fire({
                    title: 'Tetapkan Standar Harga?',
                    html: `Item <strong>${kode}</strong> (${uraian}) akan disahkan sebagai Master Data resmi.<br><small class="text-muted">Status akan menjadi <strong>Ditetapkan</strong> dan data akan dikunci secara permanen.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1e6091',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-shield-check me-1"></i> Ya, Tetapkan Sekarang',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $form.submit();
                    }
                });
            }
        });

        // ---------------------------------------------------------------------
        // AKSI 4: MODAL DETAIL & AUDIT LOGS
        // ---------------------------------------------------------------------
        $(document).on('click', '.btn-detail-ssh', function () {
            const id = $(this).data('id');
            const prefix = $(this).data('prefix') || 'ssh';
            const modalEl = document.getElementById('modalDetailSsh');
            const $modalBody = $('#modalDetailBody');

            if (!modalEl) return;
            const modal = new bootstrap.Modal(modalEl);
            $modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
            modal.show();

            SshModule.api.getDetail(id, prefix).done(function (data) {
                const item = data.item;
                const logs = data.logs || [];

                let html = `
                    <div class="row g-3">
                        <div class="col-md-7">
                            <h6 class="fw-bold text-primary mb-3">Informasi Standar Harga</h6>
                            <table class="table table-sm table-borderless">
                                <tr><th width="35%" class="text-muted">Kode Usulan</th><td class="fw-bold font-monospace">${item.kode_usulan}</td></tr>
                                <tr><th class="text-muted">Tipe / Kategori</th><td><span class="badge bg-secondary">${item.tipe}</span> ${item.kategori}</td></tr>
                                <tr><th class="text-muted">SKPD Pengusul</th><td><strong>${item.nama_skpd || '-'}</strong></td></tr>
                                <tr><th class="text-muted">Uraian Item</th><td class="fw-bold">${item.uraian}</td></tr>
                                <tr><th class="text-muted align-top">Spesifikasi</th><td class="bg-light p-2 rounded small text-secondary" style="white-space: pre-line;">${item.spesifikasi}</td></tr>
                                <tr><th class="text-muted">Satuan</th><td><span class="badge bg-light text-dark border">${item.satuan}</span></td></tr>
                                <tr><th class="text-muted">Harga Usulan</th><td class="fw-bold text-dark">Rp ${SshModule.helpers.formatRupiah(item.harga_usulan)}</td></tr>
                                ${item.harga_ditetapkan ? `<tr><th class="text-success">Harga Ditetapkan</th><td class="fw-bold text-success fs-6">Rp ${SshModule.helpers.formatRupiah(item.harga_ditetapkan)}</td></tr>` : ''}
                                <tr><th class="text-muted">Status Proses</th><td><span class="badge bg-primary">${item.status_proses}</span></td></tr>
                                ${item.catatan_verifikator ? `<tr><th class="text-warning align-top">Catatan</th><td><div class="alert alert-warning py-1 px-2 small mb-0">${item.catatan_verifikator}</div></td></tr>` : ''}
                            </table>
                        </div>
                        <div class="col-md-5 border-start">
                            <h6 class="fw-bold text-primary mb-3">Riwayat Status (Audit Trail)</h6>
                            <div class="small timeline-container" style="max-height: 340px; overflow-y: auto;">
                `;

                if (logs.length === 0) {
                    html += '<p class="text-muted small">Belum ada riwayat aktivitas.</p>';
                } else {
                    logs.forEach(function (log) {
                        html += `
                            <div class="mb-3 ps-2 border-start border-3 border-primary">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-dark">${log.status_sesudah}</strong>
                                    <span class="text-muted small">${log.created_at.substr(0, 16)}</span>
                                </div>
                                <div class="text-muted small">${log.nama_lengkap || log.username || log.role} (${log.role})</div>
                                ${log.catatan ? `<div class="bg-light p-1 rounded mt-1 text-secondary small">${log.catatan}</div>` : ''}
                            </div>
                        `;
                    });
                }

                html += `
                            </div>
                        </div>
                    </div>
                `;

                $modalBody.html(html);
            }).fail(function () {
                $modalBody.html('<div class="alert alert-danger mb-0">Gagal memuat detail usulan.</div>');
            });
        });

        // ---------------------------------------------------------------------
        // AKSI 5: INTEGRASI NOTIFIKASI WHATSAPP KE OPERATOR SKPD
        // ---------------------------------------------------------------------
        $(document).on('click', '.btn-kirim-wa-ssh', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const prefix = $(this).data('prefix') || 'ssh';
            const status = $(this).data('status') || '';
            let actionType = 'revisi';
            if (status === 'Diverifikasi') actionType = 'setuju';
            if (status === 'Ditetapkan') actionType = 'penetapan';

            if (window.SipaWa) {
                window.SipaWa.open({
                    module: prefix,
                    id: id,
                    action_type: actionType
                });
            }
        });

        $(document).on('click', '#btnWaFromModalRevisi', function (e) {
            e.preventDefault();
            const actionUrl = $('#formRevisiUsulan').attr('action') || '';
            const parts = actionUrl.split('/');
            const id = parts[parts.length - 1];
            const prefix = actionUrl.indexOf('/sbu/') !== -1 ? 'sbu' : 'ssh';
            const catatan = $('#catatan_verifikator').val() || '';

            if (window.SipaWa) {
                window.SipaWa.open({
                    module: prefix,
                    id: id,
                    action_type: 'revisi',
                    catatan: catatan
                });
            }
        });

        // ---------------------------------------------------------------------
        // AKSI 6: MODAL PRATINJAU BUKTI DUKUNG (INLINE VIEWER TANPA DOWNLOAD)
        // ---------------------------------------------------------------------
        let currentPreviewData = null;

        function loadPreviewSlot(slotNumber) {
            slotNumber = parseInt(slotNumber, 10) || 1;
            if (!currentPreviewData || !currentPreviewData.lampiran) return;

            const $loader = $('#previewLoader');
            const $iframe = $('#previewIframe');
            const $imgWrap = $('#previewImageWrapper');
            const $img = $('#previewImage');
            const $fallback = $('#previewFallbackOffice');
            const $error = $('#previewError');
            const $newTabBtn = $('#btnPreviewNewTab');
            const $dlBtn = $('#btnPreviewDownload');
            const $namaBerkas = $('#textNamaBerkasAktif');

            // Set active button
            $('.btn-slot-switch').removeClass('active');
            $('#btnSlot' + slotNumber).addClass('active');

            // Sembunyikan semua viewer
            $iframe.addClass('d-none').attr('src', 'about:blank');
            $imgWrap.addClass('d-none').removeClass('d-flex');
            $img.attr('src', '');
            $fallback.addClass('d-none');
            $error.addClass('d-none');
            $loader.removeClass('d-none');

            // Cari berkas pada slot terkait
            const item = currentPreviewData.lampiran.find(l => parseInt(l.slot, 10) === slotNumber);

            if (!item) {
                $loader.addClass('d-none');
                $('#previewErrorMessage').text(`Berkas Bukti Survey ${slotNumber} tidak tersedia.`);
                $error.removeClass('d-none');
                $newTabBtn.addClass('disabled').attr('href', '#');
                $dlBtn.addClass('disabled').attr('href', '#');
                $namaBerkas.text('');
                return;
            }

            $namaBerkas.html(`<i class="bi bi-file-earmark-check text-success me-1"></i><strong>${item.nama_asli}</strong> <span class="badge bg-secondary-subtle text-dark border ms-1">${item.size}</span>`);
            $newTabBtn.removeClass('disabled').attr('href', item.preview_url);
            $dlBtn.removeClass('disabled').attr('href', item.download_url);

            const ext = (item.ext || '').toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext);
            const isPdf = (ext === 'pdf');

            if (isPdf) {
                // PDF langsung dirender di iframe; plugin browser PDF tidak selalu mentrigger event onload iframe
                $iframe.attr('src', item.preview_url);
                $iframe.removeClass('d-none');
                $loader.addClass('d-none');
            } else if (isImage) {
                $img.attr('src', item.preview_url);
                $img.off('load error').on('load', function () {
                    $loader.addClass('d-none');
                    $imgWrap.removeClass('d-none').addClass('d-flex');
                }).on('error', function () {
                    $loader.addClass('d-none');
                    $('#previewErrorMessage').text('Gagal memuat gambar bukti survey.');
                    $error.removeClass('d-none');
                });
            } else {
                // Dokumen Non-Previewable (Word / Excel)
                $loader.addClass('d-none');
                $('#fallbackExt').text('.' + ext);
                $('#btnFallbackDownload').attr('href', item.download_url);
                $fallback.removeClass('d-none');
            }
        }

        // Expose openPreview method
        SshModule.openPreview = function(id, prefix, defaultSlot) {
            id = parseInt(id, 10);
            prefix = prefix || 'ssh';
            defaultSlot = parseInt(defaultSlot, 10) || 1;

            const modalEl = document.getElementById('modalPreviewBukti');
            const $modal = $('#modalPreviewBukti');
            if (!modalEl && !$modal.length) return;

            let base = '/';
            if (window.appConfig && window.appConfig.baseUrl) {
                base = window.appConfig.baseUrl;
            }
            base = base.replace(/\/+$/, '') + '/';
            const cleanPrefix = String(prefix || 'ssh').replace(/^\/+|\/+$/g, '');
            const url = base + cleanPrefix + '/api/lampiran/' + id;

            // Reset tampilan modal awal
            $('#badgePreviewKode').text('...');
            $('#textPreviewSkpd').text('Memuat berkas...');
            $('#textPreviewUraian').text('');
            $('#textNamaBerkasAktif').text('');
            $('#previewLoader').removeClass('d-none');
            $('#previewIframe').addClass('d-none').attr('src', 'about:blank');
            $('#previewImageWrapper').addClass('d-none').removeClass('d-flex');
            $('#previewFallbackOffice').addClass('d-none');
            $('#previewError').addClass('d-none');

            // Buka modal secara aman
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = (typeof bootstrap.Modal.getOrCreateInstance === 'function') 
                        ? bootstrap.Modal.getOrCreateInstance(modalEl) 
                        : new bootstrap.Modal(modalEl);
                    bsModal.show();
                } else if (window.jQuery && typeof $modal.modal === 'function') {
                    $modal.modal('show');
                }
            } catch (err) {
                console.warn('Bootstrap modal open fallback:', err);
                if (window.jQuery && typeof $modal.modal === 'function') {
                    $modal.modal('show');
                }
            }

            $.getJSON(url).done(function(res) {
                if (!res.success) {
                    $('#previewLoader').addClass('d-none');
                    $('#previewErrorMessage').text(res.message || 'Gagal memuat data berkas.');
                    $('#previewError').removeClass('d-none');
                    return;
                }

                currentPreviewData = res;
                $('#badgePreviewKode').text(res.kode_usulan || '-');
                $('#textPreviewSkpd').text(res.nama_skpd || '-');
                $('#textPreviewUraian').text(res.uraian || '-');

                // Update status badge di tombol tab slot
                for (let i = 1; i <= 3; i++) {
                    const l = (res.lampiran || []).find(x => parseInt(x.slot, 10) === i);
                    const $btn = $('#btnSlot' + i);
                    if (l) {
                        $btn.removeClass('disabled text-muted').removeAttr('disabled');
                        $btn.find('.size-badge').text(l.size).removeClass('bg-secondary text-white').addClass('bg-light text-dark');
                    } else {
                        $btn.addClass('disabled text-muted').attr('disabled', 'disabled');
                        $btn.find('.size-badge').text('Kosong').addClass('bg-secondary text-white').removeClass('bg-light text-dark');
                    }
                }

                // Muat slot yang dipilih
                loadPreviewSlot(defaultSlot);
            }).fail(function(jqXHR, textStatus) {
                $('#previewLoader').addClass('d-none');
                $('#previewErrorMessage').text('Terjadi kendala saat memuat berkas dari server (' + textStatus + ').');
                $('#previewError').removeClass('d-none');
            });
        };

        // Event switcher tab slot di dalam modal
        $(document).on('click', '.btn-slot-switch', function (e) {
            e.preventDefault();
            const slot = $(this).data('slot') || $(this).attr('data-slot');
            loadPreviewSlot(slot);
        });

        // Event tombol trigger preview di tabel / detail usulan
        $(document).on('click', '.btn-preview-lampiran', function (e) {
            e.preventDefault();
            const id = $(this).attr('data-id') || $(this).data('id');
            const prefix = $(this).attr('data-prefix') || $(this).data('prefix') || 'ssh';
            const slot = $(this).attr('data-slot') || $(this).data('slot') || 1;
            SshModule.openPreview(id, prefix, slot);
        });

    });

    // Ekspos ke global window
    window.SshModule = SshModule;

})(window, jQuery, window.Swal);
