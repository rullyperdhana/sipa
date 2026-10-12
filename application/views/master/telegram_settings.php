<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="page-title mb-0">
                    <i class="bi bi-telegram text-info me-2"></i>Pengaturan Notifikasi Telegram
                </h1>
                <?php if (($settings['telegram_enabled'] ?? '0') === '1' && !empty($settings['telegram_bot_token']) && !empty($settings['telegram_chat_id'])): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-broadcast me-1"></i>Notifikasi Aktif
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                        <i class="bi bi-pause-circle me-1"></i>Notifikasi Non-Aktif
                    </span>
                <?php endif; ?>
            </div>
            <p class="page-subtitle text-muted mb-0">
                Pemberitahuan otomatis secara *real-time* ke Admin &amp; Verifikator BKAD setiap kali SKPD mengirimkan berkas usulan SSH atau SBU.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('wa/settings') ?>" class="btn btn-outline-success">
                <i class="bi bi-whatsapp me-1"></i>Pengaturan WhatsApp
            </a>
            <a href="<?= site_url('master/user') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-people-fill me-1"></i>Data Pengguna
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Form Pengaturan -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-sliders2 me-2 text-primary"></i>Konfigurasi Bot Telegram
                </h6>
                <small class="text-muted"><i class="bi bi-shield-lock me-1"></i>Tersimpan di Sistem</small>
            </div>
            <div class="card-body p-4">
                <form method="post" action="<?= site_url('telegram/settings') ?>" id="formTelegramSettings">
                    <?= csrf_input() ?>

                    <!-- Switch Aktifkan Notifikasi -->
                    <div class="p-3 rounded-3 bg-light border mb-4">
                        <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-between ps-0">
                            <div>
                                <label class="form-check-label fw-bold text-dark fs-6" for="switchTelegramEnabled" style="cursor: pointer;">
                                    <i class="bi bi-bell-fill text-warning me-1.5"></i>Aktifkan Notifikasi Otomatis Telegram
                                </label>
                                <div class="text-muted small mt-0.5">
                                    Kirim notifikasi instan ke Admin BKAD / Grup Verifikator setiap kali SKPD klik "Kirim ke BKAD".
                                </div>
                            </div>
                            <input class="form-check-input ms-3 flex-shrink-0" type="checkbox" role="switch" 
                                   name="telegram_enabled" id="switchTelegramEnabled" value="1" 
                                   style="width: 2.75em; height: 1.4em; cursor: pointer;"
                                   <?= ($settings['telegram_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- Input Bot Token -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            <i class="bi bi-key-fill text-warning me-1"></i>Telegram Bot Token <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted font-monospace small"><i class="bi bi-robot"></i></span>
                            <input type="password" name="telegram_bot_token" id="inputBotToken" 
                                   class="form-control font-monospace" 
                                   value="<?= e($settings['telegram_bot_token'] ?? '') ?>" 
                                   placeholder="Contoh: 123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="btnToggleTokenVisibility" title="Lihat/Sembunyikan Token">
                                <i class="bi bi-eye" id="iconEyeToken"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.8rem;">
                            Token API resmi yang diperoleh dari <strong class="text-dark">@BotFather</strong> saat membuat bot.
                        </div>
                    </div>

                    <!-- Input Chat ID -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold text-dark small mb-0">
                                <i class="bi bi-chat-square-text-fill text-info me-1"></i>Chat ID Penerima (Pribadi / Grup) <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-outline-primary py-0 px-2 fw-semibold" id="btnDetectChat" style="font-size: 0.78rem;">
                                <i class="bi bi-radar me-1"></i>Deteksi Chat ID Otomatis
                            </button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted font-monospace small"><i class="bi bi-hash"></i></span>
                            <input type="text" name="telegram_chat_id" id="inputChatId" 
                                   class="form-control font-monospace" 
                                   value="<?= e($settings['telegram_chat_id'] ?? '') ?>" 
                                   placeholder="Contoh: 123456789 atau -1001234567890 (Grup)">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.8rem;">
                            ID Telegram Anda pribadi atau ID Grup Tim Verifikator BKAD. 
                            <span class="d-block text-secondary mt-0.5">
                                <i class="bi bi-info-circle me-1"></i>Mendukung beberapa Chat ID sekaligus: pisahkan dengan tanda koma (contoh: <code>123456789, -100987654321</code>).
                            </span>
                        </div>
                    </div>

                    <input type="hidden" name="telegram_parse_mode" value="HTML">

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <button type="button" class="btn btn-outline-info fw-semibold" id="btnTestTelegram">
                            <i class="bi bi-send-check me-1"></i>Kirim Pesan Uji Coba (Test Bot)
                        </button>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i>Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panduan Praktis Setup Bot -->
    <div class="col-lg-5">
        <!-- Card Panduan 4 Langkah -->
        <div class="card border-0 shadow-sm border-start border-4 border-info mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-info">
                    <i class="bi bi-book-half me-2"></i>Panduan Setup Bot Telegram (4 Langkah)
                </h6>
            </div>
            <div class="card-body p-3">
                <ol class="ps-3 mb-0 small text-secondary vstack gap-2.5">
                    <li>
                        <strong class="text-dark">Buat Bot Telegram:</strong> Buka aplikasi Telegram, cari akun resmi <a href="https://t.me/BotFather" target="_blank" class="fw-bold text-decoration-none text-primary">@BotFather <i class="bi bi-box-arrow-up-right" style="font-size:10px;"></i></a> dan klik <strong>Start</strong>.
                    </li>
                    <li>
                        <strong class="text-dark">Dapatkan Token:</strong> Ketik perintah <code>/newbot</code>. Masukkan nama bot (misal: <em>SIPA Tapin Notifier</em>), lalu tentukan username unik berakhiran 'bot' (misal: <em>SipaTapinBot</em>). Copy <strong>HTTP API Token</strong> yang diberikan.
                    </li>
                    <li>
                        <strong class="text-dark">Dapatkan Chat ID Pribadi / Grup:</strong>
                        <div class="mt-1 ps-2 border-start border-2 border-primary">
                            <div class="p-2 bg-warning-subtle text-warning-emphasis rounded small mb-2 border border-warning-subtle">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>WAJIB:</strong> Buka bot yang Anda buat di Telegram, lalu klik tombol <u>START</u> (atau kirim pesan) terlebih dahulu! Jika belum pernah klik START, Telegram akan menolak pengiriman dengan pesan <em>"Bad Request: chat not found"</em>.
                            </div>
                            <div>• <strong class="text-primary">Deteksi Otomatis (Termudah):</strong> Masukkan Bot Token, klik <strong>START</strong> di bot Anda, lalu klik tombol <strong>Deteksi Chat ID Otomatis</strong> di atas form. ID Anda akan otomatis terdeteksi &amp; terisi!</div>
                            <div class="mt-1">• <strong>Pribadi (Manual):</strong> Chat ke bot <a href="https://t.me/userinfobot" target="_blank" class="fw-semibold text-decoration-none">@userinfobot</a> untuk melihat ID numerik Anda (contoh: <code>123456789</code>).</div>
                            <div class="mt-1">• <strong>Grup BKAD:</strong> Buat Grup di Telegram, undang bot Anda ke dalam grup (sebagai anggota/admin), lalu undang <a href="https://t.me/getidsbot" target="_blank" class="fw-semibold text-decoration-none">@getidsbot</a> ke grup tersebut untuk mendapatkan ID Grup (biasanya diawali tanda minus <code>-100...</code>).</div>
                        </div>
                    </li>
                    <li>
                        <strong class="text-dark">Simpan &amp; Uji Coba:</strong> Masukkan Token dan Chat ID pada form di sebelah kiri, lalu klik tombol <strong>Kirim Pesan Uji Coba</strong>.
                    </li>
                </ol>
            </div>
        </div>

        <!-- Card Manfaat & Fitur Notifikasi -->
        <div class="card border-0 shadow-sm bg-light-subtle border">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">
                    <i class="bi bi-stars text-warning me-1.5"></i>Keunggulan Notifikasi Telegram
                </h6>
                <ul class="ps-3 mb-0 small text-muted vstack gap-1.5">
                    <li><strong class="text-dark">100% Gratis &amp; Bebas Kuota:</strong> Menggunakan API resmi Telegram tanpa biaya bulanan maupun risiko nomor terblokir.</li>
                    <li><strong class="text-dark">Dukungan Grup Tim:</strong> Notifikasi usulan masuk dapat langsung diterima oleh seluruh Tim Verifikator BKAD secara bersamaan dalam 1 grup.</li>
                    <li><strong class="text-dark">Tautan Instan ke Berkas:</strong> Pesan Telegram menyertakan link langsung ke halaman telaah surat usulan terkait, sehingga admin bisa langsung membuka dan memverifikasi.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Visibility Bot Token
    const btnToggleToken = document.getElementById('btnToggleTokenVisibility');
    const inputToken = document.getElementById('inputBotToken');
    const iconEye = document.getElementById('iconEyeToken');

    if (btnToggleToken && inputToken) {
        btnToggleToken.addEventListener('click', function() {
            if (inputToken.type === 'password') {
                inputToken.type = 'text';
                iconEye.classList.remove('bi-eye');
                iconEye.classList.add('bi-eye-slash');
            } else {
                inputToken.type = 'password';
                iconEye.classList.remove('bi-eye-slash');
                iconEye.classList.add('bi-eye');
            }
        });
    }

    // Tombol Uji Coba Kirim Notifikasi via AJAX
    const btnTest = document.getElementById('btnTestTelegram');
    if (btnTest) {
        btnTest.addEventListener('click', function(e) {
            e.preventDefault();
            const token = inputToken ? inputToken.value.trim() : '';
            const chatId = document.getElementById('inputChatId') ? document.getElementById('inputChatId').value.trim() : '';

            if (!token) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Token Belum Diisi',
                        text: 'Silakan masukkan Telegram Bot Token terlebih dahulu sebelum melakukan uji coba.',
                        icon: 'warning',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    alert('Silakan masukkan Telegram Bot Token terlebih dahulu.');
                }
                inputToken.focus();
                return;
            }

            if (!chatId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Chat ID Belum Diisi',
                        text: 'Silakan masukkan Chat ID atau Group ID penerima pesan uji coba.',
                        icon: 'warning',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    alert('Silakan masukkan Chat ID penerima.');
                }
                document.getElementById('inputChatId').focus();
                return;
            }

            const originalHtml = btnTest.innerHTML;
            btnTest.disabled = true;
            btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Mengirim Pesan Uji Coba...';

            const csrfTokenName = (window.appConfig && window.appConfig.csrfName) ? window.appConfig.csrfName : 'rkbmd_csrf_token';
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfHash = csrfMeta ? csrfMeta.getAttribute('content') : '<?= $this->security->get_csrf_hash() ?>';

            const postData = {
                telegram_bot_token: token,
                telegram_chat_id: chatId
            };
            postData[csrfTokenName] = csrfHash;

            $.ajax({
                url: '<?= site_url("telegram/ajax_test") ?>',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(res) {
                    btnTest.disabled = false;
                    btnTest.innerHTML = originalHtml;

                    if (res.csrfHash && csrfMeta) {
                        csrfMeta.setAttribute('content', res.csrfHash);
                    }

                    if (res.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Uji Coba Berhasil! 🎉',
                                html: 'Pesan uji coba telah terkirim ke Telegram.<br><br><small class="text-muted">Periksa aplikasi Telegram Anda untuk memastikan pesan diterima dengan sempurna.</small>',
                                icon: 'success',
                                confirmButtonColor: '#198754'
                            });
                        } else {
                            alert('Uji Coba Berhasil! Pesan telah terkirim ke Telegram.');
                        }
                    } else {
                        const errMsg = res.message || 'Terjadi kesalahan.';
                        const isChatNotFound = errMsg.toLowerCase().includes('chat not found');

                        if (typeof Swal !== 'undefined') {
                            let customHtml = '<div class="text-danger fw-semibold mb-2">' + errMsg + '</div>';
                            if (isChatNotFound) {
                                customHtml += '<div class="alert alert-warning text-start small mb-0 border-warning-subtle mt-3">' +
                                    '<div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-warning me-1"></i>Penyebab "Chat Not Found":</div>' +
                                    '<ol class="ps-3 mb-2 text-secondary">' +
                                        '<li><strong>Belum klik START:</strong> Anda belum pernah membuka bot di aplikasi Telegram dan mengklik tombol <u>START</u>. Telegram melarang bot menghubungi pengguna terlebih dahulu.</li>' +
                                        '<li><strong>Belum diundang ke grup:</strong> Jika menggunakan ID Grup, pastikan bot sudah dimasukkan/diundang ke dalam grup.</li>' +
                                        '<li><strong>Format Chat ID:</strong> Harus berupa angka ID numerik (contoh: <code>123456789</code> atau <code>-100...</code>), bukan username (@...).</li>' +
                                    '</ol>' +
                                    '<div class="text-center pt-1">' +
                                        '<button type="button" class="btn btn-sm btn-primary fw-semibold" id="btnTriggerDetectFromSwal">' +
                                            '<i class="bi bi-radar me-1"></i>Gunakan Fitur Deteksi Chat ID Otomatis' +
                                        '</button>' +
                                    '</div>' +
                                '</div>';
                            } else {
                                customHtml += '<small class="text-muted">Pastikan Token Bot dan Chat ID sudah benar serta bot dalam kondisi aktif.</small>';
                            }

                            Swal.fire({
                                title: isChatNotFound ? 'Chat Tidak Ditemukan' : 'Gagal Mengirim',
                                html: customHtml,
                                icon: 'error',
                                confirmButtonColor: '#dc3545',
                                didOpen: function() {
                                    const btnFromSwal = document.getElementById('btnTriggerDetectFromSwal');
                                    if (btnFromSwal) {
                                        btnFromSwal.addEventListener('click', function() {
                                            Swal.close();
                                            if (btnDetectChat) btnDetectChat.click();
                                        });
                                    }
                                }
                            });
                        } else {
                            alert('Gagal: ' + errMsg);
                        }
                    }
                },
                error: function(xhr) {
                    btnTest.disabled = false;
                    btnTest.innerHTML = originalHtml;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Kesalahan Server',
                            text: 'Gagal menghubungi endpoint pengujian Telegram (' + xhr.status + ').',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert('Terjadi kesalahan menghubungi server.');
                    }
                }
            });
        });
    }

    // Tombol Deteksi Chat ID Otomatis
    const btnDetectChat = document.getElementById('btnDetectChat');
    if (btnDetectChat) {
        btnDetectChat.addEventListener('click', function(e) {
            e.preventDefault();
            const token = inputToken ? inputToken.value.trim() : '';

            if (!token) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Token Belum Diisi',
                        text: 'Silakan masukkan Telegram Bot Token terlebih dahulu.',
                        icon: 'warning',
                        confirmButtonColor: '#0d6efd'
                    });
                } else {
                    alert('Silakan masukkan Telegram Bot Token terlebih dahulu.');
                }
                inputToken.focus();
                return;
            }

            const originalHtml = btnDetectChat.innerHTML;
            btnDetectChat.disabled = true;
            btnDetectChat.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Mendeteksi...';

            const csrfTokenName = (window.appConfig && window.appConfig.csrfName) ? window.appConfig.csrfName : 'rkbmd_csrf_token';
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfHash = csrfMeta ? csrfMeta.getAttribute('content') : '<?= $this->security->get_csrf_hash() ?>';

            const postData = { telegram_bot_token: token };
            postData[csrfTokenName] = csrfHash;

            $.ajax({
                url: '<?= site_url("telegram/ajax_detect_chat") ?>',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(res) {
                    btnDetectChat.disabled = false;
                    btnDetectChat.innerHTML = originalHtml;

                    if (res.csrfHash && csrfMeta) {
                        csrfMeta.setAttribute('content', res.csrfHash);
                    }

                    if (!res.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Gagal Deteksi',
                                text: res.message || 'Token bot tidak valid.',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        } else {
                            alert(res.message);
                        }
                        return;
                    }

                    const botUsername = res.bot.username || '';
                    const botName = res.bot.first_name || 'Bot SIPA';
                    const botLink = botUsername ? 'https://t.me/' + botUsername : '#';

                    if (res.chats && res.chats.length > 0) {
                        let listHtml = '<div class="text-start small mb-3">' +
                            '<div class="mb-2 text-muted">Bot: <strong class="text-dark">' + botName + '</strong> (<code>@' + botUsername + '</code>)</div>' +
                            '<div class="text-secondary mb-2">Pilih Chat ID yang terdeteksi untuk dimasukkan ke form:</div>' +
                            '<div class="list-group">';

                        res.chats.forEach(function(c) {
                            const icon = c.type === 'private' ? 'bi-person-fill text-primary' : 'bi-people-fill text-info';
                            const badge = c.type === 'private' ? 'Pribadi' : 'Grup';
                            listHtml += '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-select-chat-id" data-chat-id="' + c.id + '">' +
                                '<div>' +
                                    '<div class="fw-bold text-dark"><i class="bi ' + icon + ' me-1.5"></i>' + c.title + '</div>' +
                                    '<div class="font-monospace text-muted small">ID: <strong>' + c.id + '</strong> (' + badge + ')</div>' +
                                '</div>' +
                                '<span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2">Gunakan ID Ini <i class="bi bi-arrow-right-short"></i></span>' +
                            '</button>';
                        });

                        listHtml += '</div></div>';

                        Swal.fire({
                            title: 'Chat ID Terdeteksi! 🎯',
                            html: listHtml,
                            icon: 'success',
                            showConfirmButton: false,
                            showCloseButton: true,
                            didOpen: function() {
                                const buttons = document.querySelectorAll('.btn-select-chat-id');
                                buttons.forEach(function(b) {
                                    b.addEventListener('click', function() {
                                        const selectedId = this.getAttribute('data-chat-id');
                                        const inputChat = document.getElementById('inputChatId');
                                        if (inputChat) {
                                            inputChat.value = selectedId;
                                        }
                                        Swal.fire({
                                            title: 'Chat ID Terpasang!',
                                            text: 'Chat ID ' + selectedId + ' berhasil dimasukkan ke form. Silakan klik tombol "Simpan Pengaturan".',
                                            icon: 'success',
                                            timer: 2500,
                                            showConfirmButton: false
                                        });
                                    });
                                });
                            }
                        });
                    } else {
                        // Belum ada chat masuk
                        let emptyHtml = '<div class="text-start small mb-2">' +
                            '<p class="mb-2">Bot <strong>' + botName + '</strong> (<code>@' + botUsername + '</code>) terhubung, tetapi <strong>belum ada pesan yang masuk</strong>.</p>' +
                            '<div class="alert alert-info py-2 px-3 small border-info-subtle mb-3">' +
                                '<i class="bi bi-info-circle-fill me-1"></i>Telegram melarang bot menyapa pengguna terlebih dahulu. Anda harus memulai chat secara manual.' +
                            '</div>' +
                            '<p class="mb-3 text-center">' +
                                '<a href="' + botLink + '" target="_blank" class="btn btn-primary btn-sm fw-semibold shadow-sm">' +
                                    '<i class="bi bi-telegram me-1"></i>Buka @' + botUsername + ' &amp; Klik START' +
                                '</a>' +
                            '</p>' +
                            '<div class="text-muted small text-center">Setelah mengklik <strong>START</strong> (atau mengirim pesan) di Telegram, silakan klik tombol <strong>Deteksi Chat ID Otomatis</strong> ini lagi.</div>' +
                        '</div>';

                        Swal.fire({
                            title: 'Belum Ada Pesan Masuk',
                            html: emptyHtml,
                            icon: 'info',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                },
                error: function(xhr) {
                    btnDetectChat.disabled = false;
                    btnDetectChat.innerHTML = originalHtml;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Gagal Menghubungi Server',
                            text: 'Gagal mendeteksi chat ID (' + xhr.status + ').',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert('Terjadi kesalahan menghubungi server.');
                    }
                }
            });
        });
    }
});
</script>
