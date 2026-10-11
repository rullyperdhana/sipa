/**
 * SIRKBMD - Main Application JavaScript
 * Pemerintah Kabupaten Purwakarta - BPKAD
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
        $.getJSON(window.appConfig.baseUrl + 'ajax/notif/list', function (data) {
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
        }).fail(function () {
            // Abaikan jika terjadi kegagalan jaringan sementara
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

    // ===== Confirm delete via data-confirm =====
    $(document).on('submit', 'form[data-confirm]', function (e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });

});
