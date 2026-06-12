<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IT Helpdesk' }} — KTU Helpdesk</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/custom/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom/component.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom/ticket.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom/dashboard.css') }}">

    @stack('styles')
</head>
<body>

<div class="app-wrapper">
    @include('components.layout.sidebar')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="main-content" id="mainContent">
        @include('components.layout.header')
        <div class="page-content">
            @if(session('success'))
                <x-ui.alert type="success" :message="session('success')" />
            @endif
            @if(session('error'))
                <x-ui.alert type="error" :message="session('error')" />
            @endif
            @if($errors->any())
                <x-ui.alert type="error" message="Terdapat kesalahan pada form. Silakan periksa kembali." />
            @endif
            {{ $slot }}
        </div>
        @include('components.layout.footer')
    </div>
</div>

{{-- Toast Container --}}
<div class="toast-container" id="toastContainer"></div>

{{-- Quick Action Modal --}}
<div class="quick-modal-overlay" id="quickModalOverlay">
    <div class="quick-modal">
        <div class="quick-modal-header">
            <span class="quick-modal-title" id="quickModalTitle">Konfirmasi</span>
            <button class="quick-modal-close" onclick="closeQuickModal()">✕</button>
        </div>
        <div class="quick-modal-body">
            <p id="quickModalDesc" style="font-size:13px; color:var(--gray-600); margin-bottom:16px;"></p>
            <div id="quickModalNoteField" class="form-group">
                <label class="form-label" id="quickModalNoteLabel">Catatan (Opsional)</label>
                <textarea id="quickModalNote" class="form-control" rows="2"
                          placeholder="Tambahkan catatan..."></textarea>
            </div>
            <div id="resolutionField" style="display:none;" class="form-group">
                <label class="form-label required">Catatan Penyelesaian</label>
                <textarea id="quickModalResolution" class="form-control" rows="3"
                          placeholder="Jelaskan langkah penyelesaian yang dilakukan..."></textarea>
            </div>
        </div>
        <div class="quick-modal-footer">
            <button class="btn btn-secondary" onclick="closeQuickModal()">Batal</button>
            <button class="btn btn-primary" onclick="submitQuickAction()">Konfirmasi</button>
        </div>
    </div>
</div>

{{-- Log Detail Modal --}}
<div class="quick-modal-overlay" id="logDetailOverlay">
    <div class="quick-modal" style="max-width:420px;">
        <div class="quick-modal-header">
            <span class="quick-modal-title" id="logModalTitle">Detail Perubahan</span>
            <button class="quick-modal-close" onclick="closeLogModal()">✕</button>
        </div>
        <div class="quick-modal-body">
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div class="panel-row">
                    <span class="panel-row-label">Waktu</span>
                    <span class="panel-row-value" id="logModalTime"></span>
                </div>
                <div class="panel-row">
                    <span class="panel-row-label">Oleh</span>
                    <span class="panel-row-value" id="logModalBy"></span>
                </div>
                <div class="panel-row" id="logModalChangeRow">
                    <span class="panel-row-label">Perubahan</span>
                    <span class="panel-row-value" id="logModalChange"></span>
                </div>
                <div id="logModalNoteSection" style="display:none;">
                    <div style="font-size:11px; font-weight:700; color:var(--gray-400);
                                text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;"
                         id="logModalNoteLabel">Catatan</div>
                    <div style="background:var(--gray-50); border-radius:8px; padding:12px;
                                font-size:13px; color:var(--gray-700); line-height:1.6;"
                         id="logModalNote"></div>
                </div>
            </div>
        </div>
        <div class="quick-modal-footer">
            <button class="btn btn-secondary" onclick="closeLogModal()">Tutup</button>
        </div>
    </div>
</div>

{{-- Confirm Modal --}}
<div class="confirm-modal-overlay" id="confirmModalOverlay">
    <div class="confirm-modal">
        <div class="confirm-modal-icon">
            <div class="confirm-modal-icon-circle" id="confirmModalIconCircle">
                <span id="confirmModalIconEmoji">🗑️</span>
            </div>
        </div>
        <div class="confirm-modal-body">
            <div class="confirm-modal-title" id="confirmModalTitle">Konfirmasi</div>
            <p class="confirm-modal-desc" id="confirmModalDesc">Apakah kamu yakin?</p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-secondary" onclick="closeConfirmModal()">Batal</button>
            <button class="btn btn-danger" id="confirmModalBtn" onclick="executeConfirmAction()">Hapus</button>
        </div>
    </div>
</div>

{{-- Logout Modal --}}
<div class="confirm-modal-overlay" id="logoutModalOverlay">
    <div class="confirm-modal">
        <div class="confirm-modal-icon">
            <div class="confirm-modal-icon-circle warning">
                <span>🚪</span>
            </div>
        </div>
        <div class="confirm-modal-body">
            <div class="confirm-modal-title">Keluar dari Aplikasi</div>
            <p class="confirm-modal-desc">Apakah kamu yakin ingin keluar? Sesi kamu akan diakhiri.</p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-secondary" onclick="closeLogoutModal()">Batal</button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Ya, Keluar</button>
            </form>
        </div>
    </div>
</div>

{{-- Modal Diluar Jam Kerja --}}
<div class="confirm-modal-overlay" id="outsideWorkingHoursModal">
    <div class="confirm-modal">
        <div class="confirm-modal-icon">
            <div class="confirm-modal-icon-circle warning">
                <span>🌙</span>
            </div>
        </div>
        <div class="confirm-modal-body">
            <div class="confirm-modal-title">Diluar Jam Kerja</div>
            <p class="confirm-modal-desc">
                Tiket <strong id="outsideTicketNumber"></strong> berhasil dibuat!
                Namun saat ini diluar jam kerja operasional.
                SLA akan mulai dihitung saat jam kerja dimulai.
            </p>
        </div>
        <div class="confirm-modal-footer">
            <button class="btn btn-primary" onclick="closeOutsideWorkingHoursModal()">OK, Mengerti</button>
        </div>
    </div>
</div>

{{-- Load JS --}}
<script src="{{ asset('js/ticket-action.js') }}"></script>

<script>
// ── Sidebar Toggle ────────────────────────────
const sidebar        = document.getElementById('sidebar');
const mainContent    = document.getElementById('mainContent');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const btnToggle      = document.getElementById('btnToggleSidebar');

function toggleSidebar() {
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('active');
    } else {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
}

if (window.innerWidth > 768) {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }
}

btnToggle?.addEventListener('click', toggleSidebar);
sidebarOverlay?.addEventListener('click', toggleSidebar);

// ── Notification Dropdown ─────────────────────
const btnNotification    = document.getElementById('btnNotification');
const notifDropdown      = document.getElementById('notifDropdown');
const userDropdownToggle = document.getElementById('userDropdownToggle');
const userDropdownMenu   = document.getElementById('userDropdownMenu');

btnNotification?.addEventListener('click', (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle('open');
    userDropdownMenu?.classList.remove('open');
});

userDropdownToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdownMenu.classList.toggle('open');
    notifDropdown?.classList.remove('open');
});

document.addEventListener('click', () => {
    notifDropdown?.classList.remove('open');
    userDropdownMenu?.classList.remove('open');
});

// ── User Dropdown Close ───────────────────────
function closeUserDropdown() {
    document.getElementById('userDropdownMenu')?.classList.remove('open');
}

// ── Mark Notif Read ───────────────────────────
function markNotifRead(id, url) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const role      = '{{ auth()->user()->role ?? "user" }}';
    let prefix = 'user';
    if (role === 'it_support')    prefix = 'support';
    if (role === 'it_supervisor') prefix = 'supervisor';

    fetch(`/${prefix}/notifications/${id}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        }
    }).then(() => {
        window.location.href = url;
    }).catch(() => {
        window.location.href = url;
    });
}

// ── Toast Notification ────────────────────────
function showToast(type, message, title) {
    const container = document.getElementById('toastContainer');
    const icons  = { success: '✅', error: '❌', warning: '⚠️', info: '📢' };
    const titles = {
        success: title || 'Berhasil',
        error:   title || 'Gagal',
        warning: title || 'Peringatan',
        info:    title || 'Informasi',
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <div class="toast-body">
            <div class="toast-title">${titles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="removeToast(this.parentElement)">×</button>
        <div class="toast-progress"></div>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
    setTimeout(() => removeToast(toast), 4000);
}

function removeToast(toast) {
    if (!toast || !toast.parentElement) return;
    toast.classList.add('hide');
    setTimeout(() => toast.remove(), 200);
}

// ── Confirm Modal ─────────────────────────────
let confirmAction = null;

function showConfirmModal(options) {
    const {
        title    = 'Hapus Data',
        desc     = 'Data yang dihapus tidak dapat dikembalikan.',
        btnText  = 'Hapus',
        btnClass = 'btn-danger',
        icon     = '🗑️',
        type     = 'danger',
        action,
    } = options;

    document.getElementById('confirmModalTitle').textContent     = title;
    document.getElementById('confirmModalDesc').textContent      = desc;
    document.getElementById('confirmModalIconEmoji').textContent = icon;
    document.getElementById('confirmModalIconCircle').className  = `confirm-modal-icon-circle ${type}`;

    const btn       = document.getElementById('confirmModalBtn');
    btn.textContent = btnText;
    btn.className   = `btn ${btnClass}`;

    confirmAction = action;
    document.getElementById('confirmModalOverlay').classList.add('open');
}

function closeConfirmModal() {
    document.getElementById('confirmModalOverlay').classList.remove('open');
    confirmAction = null;
}

function executeConfirmAction() {
    if (confirmAction) confirmAction();
    closeConfirmModal();
}

function confirmDelete(formId, itemName) {
    showConfirmModal({
        title   : 'Hapus Data',
        desc    : `Apakah kamu yakin ingin menghapus "${itemName}"? Data tidak dapat dikembalikan.`,
        btnText : 'Hapus Permanen',
        btnClass: 'btn-danger',
        icon    : '🗑️',
        type    : 'danger',
        action  : () => document.getElementById(formId).submit(),
    });
}

// ── Logout Modal ──────────────────────────────
function openLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.add('open');
}

function closeLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.remove('open');
}

// ── Log Detail Modal ──────────────────────────
function openLogModal(action, changedBy, time, before, after, field, note, statusAfter) {
    document.getElementById('logModalTitle').textContent = action;
    document.getElementById('logModalTime').textContent  = time;
    document.getElementById('logModalBy').textContent    = changedBy;

    const changeRow = document.getElementById('logModalChangeRow');
    const changeEl  = document.getElementById('logModalChange');
    if (before && after) {
        changeEl.textContent    = `${before.replace(/_/g, ' ')} → ${after.replace(/_/g, ' ')}`;
        changeRow.style.display = 'flex';
    } else {
        changeRow.style.display = 'none';
    }

    const noteSection = document.getElementById('logModalNoteSection');
    const noteEl      = document.getElementById('logModalNote');
    const noteLabelEl = document.getElementById('logModalNoteLabel');

    if (note) {
        if (statusAfter === 'pending' || after === 'pending') {
            noteLabelEl.textContent = 'Alasan Pending';
        } else if (statusAfter === 'resolved' || after === 'resolved') {
            noteLabelEl.textContent = 'Catatan Penyelesaian';
        } else if (field === 'priority') {
            noteLabelEl.textContent = 'Catatan Perubahan Prioritas';
        } else {
            noteLabelEl.textContent = 'Catatan';
        }
        noteEl.textContent        = note;
        noteSection.style.display = 'block';
    } else {
        noteSection.style.display = 'none';
    }

    document.getElementById('logDetailOverlay').classList.add('open');
}

function closeLogModal() {
    document.getElementById('logDetailOverlay').classList.remove('open');
}

// ── Outside Working Hours Modal ───────────────
function closeOutsideWorkingHoursModal() {
    document.getElementById('outsideWorkingHoursModal').classList.remove('open');
}

// ── Auto Hide Alert ───────────────────────────
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        el.style.transition = 'opacity 0.5s ease';
        el.style.opacity    = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 4000);

// ── Event Listeners ───────────────────────────
document.getElementById('confirmModalOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

document.getElementById('logoutModalOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) closeLogoutModal();
});

document.getElementById('logDetailOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) closeLogModal();
});

document.getElementById('outsideWorkingHoursModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeOutsideWorkingHoursModal();
});
</script>

{{-- Flash Session → Toast --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('success', '{{ addslashes(session('success')) }}');
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('error', '{{ addslashes(session('error')) }}');
        });
    </script>
@endif

@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('warning', '{{ addslashes(session('warning')) }}');
        });
    </script>
@endif

@if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('info', '{{ addslashes(session('info')) }}');
        });
    </script>
@endif

@if(session('outside_working_hours'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('outsideTicketNumber').textContent = '{{ session('outside_working_hours') }}';
            document.getElementById('outsideWorkingHoursModal').classList.add('open');
        });
    </script>
@endif

@stack('scripts')

</body>
</html>
