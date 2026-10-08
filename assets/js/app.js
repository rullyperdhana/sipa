/**
 * SIRKBMD - Main Application JavaScript
 * Pemerintah Kabupaten Purwakarta - BPKAD
 */

$(function () {

    // ===== Sidebar Toggle (Mobile) =====
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    // Create backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (sidebar.classList.contains('open')) closeSidebar();
            else openSidebar();
        });
    }
    backdrop.addEventListener('click', closeSidebar);

    // ===== Load Notifikasi =====
    function loadNotifikasi() {
        $.getJSON(window.appConfig.baseUrl + 'ajax/notif/list', function (data) {
            const badge = $('#notif-badge');
            const list = $('#notif-list');
            const count = $('#notif-count');

            if (data.unread > 0) {
                badge.text(data.unread > 9 ? '9+' : data.unread).show();
            } else {
                badge.hide();
            }
            count.text(data.unread + ' belum dibaca');

            if (!data.list || data.list.length === 0) {
                list.html('<div class="text-center text-muted py-4 small"><i class="bi bi-bell-slash d-block fs-3 mb-1"></i>Tidak ada notifikasi</div>');
                return;
            }

            let html = '';
            data.list.forEach(function (n) {
                const iconMap = { info: 'info-circle text-info', success: 'check-circle text-success', warning: 'exclamation-triangle text-warning', danger: 'x-circle text-danger' };
                const icon = iconMap[n.tipe] || 'bell text-secondary';
                html += `<a href="${n.link || '#'}" class="notif-item d-block text-decoration-none ${n.is_read == 0 ? 'unread' : ''}" data-id="${n.id}">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-${icon} mt-1"></i>
                        <div class="flex-fill overflow-hidden">
                            <div class="notif-title text-truncate">${n.judul}</div>
                            <div class="notif-msg">${n.pesan}</div>
                        </div>
                    </div>
                </a>`;
            });
            list.html(html);
        }).fail(function () {
            $('#notif-list').html('<div class="text-center text-muted py-3 small">Gagal memuat notifikasi.</div>');
        });
    }

    // Load on dropdown open
    $('[data-bs-toggle="dropdown"]').on('shown.bs.dropdown', function () {
        if ($(this).next().hasClass('notif-dropdown')) loadNotifikasi();
    });

    // Mark as read
    $(document).on('click', '.notif-item', function () {
        const id = $(this).data('id');
        if (id) {
            $.post(window.appConfig.baseUrl + 'ajax/notif/read/' + id, {
                [window.appConfig.csrfName]: window.appConfig.csrfHash
            });
            $(this).removeClass('unread');
        }
    });

    // Poll every 60s
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
