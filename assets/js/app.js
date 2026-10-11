/**
 * SIRKBMD - Main Application JavaScript
 * Pemerintah Kabupaten Tapin - BKAD
 */

$(function () {

    // ===== Responsive Sidebar Toggle (Desktop Collapse & Mobile Drawer) =====
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    // Create backdrop for mobile drawer if not already in DOM
    let backdrop = document.querySelector('.sidebar-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    function isMobile() {
        return window.innerWidth < 992;
    }

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        backdrop.classList.add('show');
        document.body.classList.add('sidebar-open');
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    }

    function toggleDesktopSidebar() {
        document.body.classList.toggle('sidebar-collapsed');
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
        try {
            localStorage.setItem('sipa_sidebar_collapsed', isCollapsed ? '1' : '0');
        } catch (e) {}

        // Trigger resize event after transition to adjust any responsive tables/charts
        setTimeout(function () {
            window.dispatchEvent(new Event('resize'));
            if (window.jQuery && $.fn.dataTable) {
                try {
                    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
                } catch (err) {}
            }
        }, 260);
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (isMobile()) {
                if (sidebar && sidebar.classList.contains('open')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                toggleDesktopSidebar();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            closeMobileSidebar();
        });
    }

    backdrop.addEventListener('click', closeMobileSidebar);

    // Close mobile drawer when pressing Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isMobile() && sidebar && sidebar.classList.contains('open')) {
            closeMobileSidebar();
        }
    });

    // Close mobile drawer when clicking any sidebar navigation link
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobile()) {
                    closeMobileSidebar();
                }
            });
        });
    }

    // Handle screen resize
    window.addEventListener('resize', function () {
        if (!isMobile() && sidebar && sidebar.classList.contains('open')) {
            closeMobileSidebar();
        }
    });

    // ===== Helper CSRF Token =====
    function getCsrfToken() {
        if (window.appConfig && window.appConfig.csrfHash) {
            return window.appConfig.csrfHash;
        }
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function updateCsrfToken(data) {
        if (data && data.csrfHash && window.appConfig) {
            window.appConfig.csrfHash = data.csrfHash;
            if (data.csrfName) window.appConfig.csrfName = data.csrfName;
            $('meta[name="csrf-token"]').attr('content', data.csrfHash);
        }
    }

    // ===== Load Notifikasi =====
    function loadNotifikasi() {
        if (!window.appConfig || !window.appConfig.baseUrl) return;

        // Jika user sedang idle lebih dari 2 menit, tunda polling background agar hemat resource & tidak memicu false activity
        if (window.SipaIdleManager && window.SipaIdleManager.isIdleFor(120)) {
            return;
        }

        $.ajax({
            url: window.appConfig.baseUrl + 'ajax/notif/list',
            method: 'GET',
            dataType: 'json',
            headers: { 'X-Background-Poll': '1' },
            success: function (data) {
                const badge = $('#notif-badge');
                const list = $('#notif-list');
                const count = $('#notif-count');
                const btnReadAll = $('#btn-read-all-notif');

                updateCsrfToken(data);

                const unreadCount = parseInt(data.unread, 10) || 0;
                if (unreadCount > 0) {
                    badge.text(unreadCount > 9 ? '9+' : unreadCount).show();
                    count.text(unreadCount + ' belum dibaca');
                    btnReadAll.show();
                } else {
                    badge.hide();
                    count.text('0 belum dibaca');
                    btnReadAll.hide();
                }

                if (!data.list || data.list.length === 0) {
                    list.html('<div class="text-center text-muted py-4 small"><i class="bi bi-bell-slash d-block fs-3 mb-1 text-secondary opacity-50"></i>Tidak ada notifikasi</div>');
                    return;
                }

                let html = '';
                data.list.forEach(function (n) {
                    const iconMap = {
                        info: 'info-circle-fill text-primary',
                        success: 'check-circle-fill text-success',
                        warning: 'exclamation-triangle-fill text-warning',
                        danger: 'x-circle-fill text-danger'
                    };
                    const icon = iconMap[n.tipe] || 'bell-fill text-secondary';
                    const timeText = n.waktu_lalu ? `<div class="notif-time text-muted" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>${n.waktu_lalu}</div>` : '';
                    html += `<a href="${n.link || '#'}" class="notif-item d-block text-decoration-none ${n.is_read == 0 ? 'unread' : ''}" data-id="${n.id}">
                        <div class="d-flex gap-2.5 align-items-start">
                            <i class="bi bi-${icon} mt-0.5 fs-6 flex-shrink-0"></i>
                            <div class="flex-fill overflow-hidden">
                                <div class="notif-title text-truncate text-dark">${n.judul}</div>
                                <div class="notif-msg text-secondary mb-1">${n.pesan}</div>
                                ${timeText}
                            </div>
                        </div>
                    </a>`;
                });
                list.html(html);
            }
        });
    }

    // Load on dropdown open
    $('[data-bs-toggle="dropdown"]').on('shown.bs.dropdown', function () {
        if ($(this).next().hasClass('notif-dropdown') || $(this).attr('id') === 'dropdownNotifBtn') {
            loadNotifikasi();
        }
    });

    // Mark as read saat item diklik
    $(document).on('click', '.notif-item', function (e) {
        const item = $(this);
        const id = item.data('id');
        const href = item.attr('href');
        const isUnread = item.hasClass('unread');

        if (id && isUnread) {
            item.removeClass('unread');
            const badge = $('#notif-badge');
            let current = parseInt(badge.text(), 10) || 0;
            if (current > 1) {
                badge.text(current - 1);
                $('#notif-count').text((current - 1) + ' belum dibaca');
            } else {
                badge.hide();
                $('#notif-count').text('0 belum dibaca');
                $('#btn-read-all-notif').hide();
            }

            const postData = {};
            if (window.appConfig) {
                postData[window.appConfig.csrfName] = getCsrfToken();
            }

            $.post(window.appConfig.baseUrl + 'ajax/notif/read/' + id, postData, function(res) {
                updateCsrfToken(res);
            });
        }

        if (href && href !== '#' && href !== 'javascript:;') {
            // Navigasi normal browser
        } else {
            e.preventDefault();
        }
    });

    // Tandai semua notifikasi telah dibaca
    $(document).on('click', '#btn-read-all-notif', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const btn = $(this);
        btn.prop('disabled', true).text('Menandai...');

        const postData = {};
        if (window.appConfig) {
            postData[window.appConfig.csrfName] = getCsrfToken();
        }

        $.post(window.appConfig.baseUrl + 'ajax/notif/read/all', postData, function(res) {
            updateCsrfToken(res);
            $('#notif-badge').hide();
            $('#notif-count').text('0 belum dibaca');
            $('.notif-item').removeClass('unread');
            btn.hide().prop('disabled', false).text('Tandai semua dibaca');
        }).fail(function() {
            btn.prop('disabled', false).text('Tandai semua dibaca');
        });
    });

    // Poll setiap 60 detik
    loadNotifikasi();
    setInterval(loadNotifikasi, 60000);

    // ===== Auto-dismiss alerts =====
    setTimeout(function () {
        $('.alert.alert-dismissible').fadeOut(500);
    }, 5000);

    // ===== Format rupiah input =====
    $(document).on('blur', 'input[data-rupiah]', function () {
        let val = parseInt($(this).val().replace(/\D/g, '')) || 0;
        $(this).val(val.toLocaleString('id-ID'));
    });

    // ===== Global AJAX 401 Handler (Sesi Habis / Idle) =====
    $(document).ajaxError(function (event, jqXHR) {
        if (jqXHR.status === 401) {
            const loginUrl = (window.appConfig && window.appConfig.baseUrl ? window.appConfig.baseUrl : '/') + 'login?reason=idle';
            try {
                const res = JSON.parse(jqXHR.responseText);
                if (res && res.logged_in === false) {
                    window.location.href = loginUrl;
                }
            } catch (e) {
                window.location.href = loginUrl;
            }
        }
    });

    // ===== Manajemen Auto-Logout Saat Idle (User Inactivity) =====
    const SipaIdleManager = (function () {
        const config = (window.appConfig && window.appConfig.idleTimeout) ? window.appConfig.idleTimeout : null;
        if (!config || !config.enabled) {
            return {
                isIdleFor: function () { return false; },
                resetTimer: function () {}
            };
        }

        const timeoutMins = parseInt(config.timeoutMinutes, 10) || 15;
        const warningSecs = parseInt(config.warningSeconds, 10) >= 0 ? parseInt(config.warningSeconds, 10) : 60;
        const timeoutMs = timeoutMins * 60 * 1000;
        const warningMs = warningSecs * 1000;
        const warningThresholdMs = Math.max(0, timeoutMs - warningMs);

        const STORAGE_KEY_LAST_ACTIVE = 'sipa_last_activity_time';
        const STORAGE_KEY_LOGGED_OUT  = 'sipa_user_logged_out_event';

        let lastActiveTime = Date.now();
        let isWarningModalOpen = false;
        let countdownTimer = null;
        let lastThrottleRecord = 0;

        // Inisialisasi awal di localStorage
        try {
            const savedTime = parseInt(localStorage.getItem(STORAGE_KEY_LAST_ACTIVE), 10);
            if (savedTime && (Date.now() - savedTime) < timeoutMs) {
                lastActiveTime = savedTime;
            } else {
                localStorage.setItem(STORAGE_KEY_LAST_ACTIVE, Date.now().toString());
            }
        } catch (e) {}

        function getEffectiveLastActive() {
            let remoteTime = 0;
            try {
                remoteTime = parseInt(localStorage.getItem(STORAGE_KEY_LAST_ACTIVE), 10) || 0;
            } catch (e) {}
            return Math.max(lastActiveTime, remoteTime);
        }

        function recordUserActivity(explicitEvent) {
            const now = Date.now();
            // Jika modal peringatan sedang terbuka, aktivitas kursor di background tidak langsung menutup modal tanpa konfirmasi tombol Tetap Masuk
            if (isWarningModalOpen) return;

            // Throttle agar tidak menulis ke localStorage berulang-ulang tiap millisecond
            if (now - lastThrottleRecord < 1500 && !explicitEvent) {
                return;
            }

            const wasIdle = isIdleFor(120);
            lastThrottleRecord = now;
            lastActiveTime = now;
            try {
                localStorage.setItem(STORAGE_KEY_LAST_ACTIVE, now.toString());
            } catch (e) {}

            // Jika user aktif kembali setelah idle > 2 menit, perbarui notifikasi segera
            if (wasIdle && typeof loadNotifikasi === 'function') {
                loadNotifikasi();
            }
        }

        // Event listener penangkap aktivitas pengguna
        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'wheel'].forEach(function (evtName) {
            window.addEventListener(evtName, function () {
                recordUserActivity(false);
            }, { passive: true });
        });

        // Sinkronisasi antar tab browser melalui Storage Event
        window.addEventListener('storage', function (e) {
            if (e.key === STORAGE_KEY_LAST_ACTIVE) {
                const val = parseInt(e.newValue, 10) || 0;
                if (val > lastActiveTime) {
                    lastActiveTime = val;
                    // Jika tab lain aktif dan modal peringatan di tab ini sedang terbuka, tutup modal
                    if (isWarningModalOpen) {
                        try {
                            if (window.Swal && Swal.isVisible()) {
                                Swal.close();
                            }
                        } catch (err) {}
                        isWarningModalOpen = false;
                    }
                }
            } else if (e.key === STORAGE_KEY_LOGGED_OUT) {
                // Tab lain telah logout
                triggerLogout();
            }
        });

        function isIdleFor(seconds) {
            const elapsed = Date.now() - getEffectiveLastActive();
            return elapsed >= (seconds * 1000);
        }

        function triggerLogout() {
            try {
                localStorage.setItem(STORAGE_KEY_LOGGED_OUT, Date.now().toString());
            } catch (e) {}
            window.location.href = config.logoutUrl || (window.appConfig.baseUrl + 'logout?reason=idle');
        }

        function pingSession() {
            if (!config.pingUrl) return;
            const postData = {};
            if (window.appConfig && window.appConfig.csrfName) {
                postData[window.appConfig.csrfName] = getCsrfToken();
            }
            $.post(config.pingUrl, postData, function (res) {
                updateCsrfToken(res);
            });
        }

        function showWarningModal() {
            if (isWarningModalOpen) return;
            isWarningModalOpen = true;

            const remainingSec = Math.max(1, Math.ceil((timeoutMs - (Date.now() - getEffectiveLastActive())) / 1000));

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Sesi Akan Berakhir',
                    html: `
                        <div class="text-center py-2">
                            <div class="mb-3">
                                <i class="bi bi-clock-history text-warning" style="font-size: 3.5rem;"></i>
                            </div>
                            <p class="mb-2 text-dark fw-semibold">Tidak terdeteksi aktivitas pada akun Anda.</p>
                            <p class="mb-2 text-muted small">Demi keamanan dan perlindungan data daerah, sistem akan otomatis logout dalam:</p>
                            <div class="display-5 fw-bold text-danger my-3" id="sipa-idle-countdown-sec">${remainingSec} detik</div>
                            <p class="text-secondary small mb-0">Klik tombol di bawah untuk tetap melanjutkan pekerjaan Anda.</p>
                        </div>
                    `,
                    icon: null,
                    showCancelButton: true,
                    confirmButtonColor: '#1e6091',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Tetap Masuk',
                    cancelButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Logout Sekarang',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    focusConfirm: true,
                    didOpen: function () {
                        clearInterval(countdownTimer);
                        countdownTimer = setInterval(function () {
                            const currentElapsed = Date.now() - getEffectiveLastActive();
                            const leftSec = Math.max(0, Math.ceil((timeoutMs - currentElapsed) / 1000));
                            const el = document.getElementById('sipa-idle-countdown-sec');
                            if (el) el.textContent = leftSec + ' detik';

                            if (leftSec <= 0) {
                                clearInterval(countdownTimer);
                                triggerLogout();
                            }
                        }, 1000);
                    },
                    willClose: function () {
                        clearInterval(countdownTimer);
                        isWarningModalOpen = false;
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        // User klik "Tetap Masuk"
                        lastActiveTime = Date.now();
                        try {
                            localStorage.setItem(STORAGE_KEY_LAST_ACTIVE, lastActiveTime.toString());
                        } catch (e) {}
                        pingSession();
                        isWarningModalOpen = false;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Sesi berhasil diperpanjang',
                                showConfirmButton: false,
                                timer: 2500
                            });
                        }
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // User klik "Logout Sekarang"
                        triggerLogout();
                    }
                });
            } else {
                // Fallback sederhana jika SweetAlert tidak tersedia
                if (confirm('Sesi Anda akan segera berakhir karena tidak ada aktivitas. Klik OK untuk tetap masuk atau Batal untuk logout.')) {
                    lastActiveTime = Date.now();
                    pingSession();
                } else {
                    triggerLogout();
                }
            }
        }

        // Loop pemeriksaan timer idle setiap 1 detik
        setInterval(function () {
            const now = Date.now();
            const elapsed = now - getEffectiveLastActive();

            if (elapsed >= timeoutMs) {
                // Batas waktu habis
                triggerLogout();
            } else if (elapsed >= warningThresholdMs && warningSecs > 0 && !isWarningModalOpen) {
                // Tampilkan popup peringatan sebelum logout
                showWarningModal();
            }
        }, 1000);

        return {
            isIdleFor: isIdleFor,
            resetTimer: function () {
                recordUserActivity(true);
            }
        };
    })();

    window.SipaIdleManager = SipaIdleManager;

    // ===== Confirm delete via data-confirm =====
    $(document).on('submit', 'form[data-confirm]', function (e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });

});
