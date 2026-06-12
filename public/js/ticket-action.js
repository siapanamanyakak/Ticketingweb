// ═══════════════════════════════════════════
//  TICKET QUICK ACTIONS
// ═══════════════════════════════════════════

// ── State ─────────────────────────────────
let currentTicketId   = null;
let currentAction     = null;
let currentValue      = null;
let currentLabel      = null;
let isNoteRequired    = false;

// ── Dropdown Toggle ───────────────────────
function toggleDropdown(id) {
    const dropdown   = document.getElementById('dropdown-' + id);
    const allDropdowns = document.querySelectorAll('.quick-dropdown');
    allDropdowns.forEach(d => { if (d !== dropdown) d.classList.remove('open'); });
    dropdown.classList.toggle('open');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.quick-action-wrapper')) {
        document.querySelectorAll('.quick-dropdown').forEach(d => d.classList.remove('open'));
    }
});

// ── Open Status Modal ─────────────────────
function openStatusModal(ticketId, newStatus, newLabel, noteRequired) {
    currentTicketId = ticketId;
    currentAction   = 'status';
    currentValue    = newStatus;
    currentLabel    = newLabel;
    isNoteRequired  = noteRequired;

    document.querySelectorAll('.quick-dropdown').forEach(d => d.classList.remove('open'));

    // Set judul modal
    document.getElementById('quickModalTitle').textContent = 'Update Status Tiket';

    // Set deskripsi
    document.getElementById('quickModalDesc').innerHTML =
        `Ubah status menjadi <strong>${newLabel}</strong>?`;

    // Set label catatan
    const noteLabel = document.getElementById('quickModalNoteLabel');
    if (newStatus === 'pending') {
        noteLabel.textContent  = 'Alasan Pending';
        noteLabel.className    = 'form-label required';
    } else {
        noteLabel.textContent  = 'Catatan (Opsional)';
        noteLabel.className    = 'form-label';
    }

    document.getElementById('quickModalNote').placeholder =
        newStatus === 'pending' ? 'Wajib isi alasan pending...' : 'Tambahkan catatan...';
    document.getElementById('quickModalNote').required = noteRequired;
    document.getElementById('quickModalNote').value    = '';

    // Tampilkan resolution field hanya untuk resolved
    const resolutionField = document.getElementById('resolutionField');
    const resolutionInput = document.getElementById('quickModalResolution');
    if (resolutionField && resolutionInput) {
        resolutionField.style.display = newStatus === 'resolved' ? 'block' : 'none';
        resolutionInput.required      = newStatus === 'resolved';
        resolutionInput.value         = '';
    }

    // Sembunyikan note field kalau resolved (sudah ada resolution field)
    const noteField = document.getElementById('quickModalNoteField');
    if (noteField) {
        noteField.style.display = newStatus === 'resolved' ? 'none' : 'block';
    }

    document.getElementById('quickModalOverlay').classList.add('open');
}

// ── Open Priority Modal ───────────────────
function openPriorityModal(ticketId, priorityId, priorityLabel) {
    currentTicketId = ticketId;
    currentAction   = 'priority';
    currentValue    = priorityId;
    currentLabel    = priorityLabel;
    isNoteRequired  = false;

    document.querySelectorAll('.quick-dropdown').forEach(d => d.classList.remove('open'));

    document.getElementById('quickModalTitle').textContent = 'Update Prioritas Tiket';
    document.getElementById('quickModalDesc').innerHTML    =
        `Ubah prioritas menjadi <strong>${priorityLabel}</strong>?`;

    const noteLabel = document.getElementById('quickModalNoteLabel');
    if (noteLabel) {
        noteLabel.textContent = 'Catatan (Opsional)';
        noteLabel.className   = 'form-label';
    }

    document.getElementById('quickModalNote').placeholder = 'Tambahkan catatan...';
    document.getElementById('quickModalNote').required    = false;
    document.getElementById('quickModalNote').value       = '';

    const resolutionField = document.getElementById('resolutionField');
    if (resolutionField) resolutionField.style.display = 'none';

    const noteField = document.getElementById('quickModalNoteField');
    if (noteField) noteField.style.display = 'block';

    document.getElementById('quickModalOverlay').classList.add('open');
}

// ── Close Modal ───────────────────────────
function closeQuickModal() {
    document.getElementById('quickModalOverlay').classList.remove('open');
    currentTicketId = null;
    currentAction   = null;
    currentValue    = null;

    // Reset border merah
    const noteInput = document.getElementById('quickModalNote');
    const resInput  = document.getElementById('quickModalResolution');
    if (noteInput) noteInput.style.borderColor = '';
    if (resInput)  resInput.style.borderColor  = '';
}

// ── Submit Modal ──────────────────────────
function submitQuickAction() {
    const note       = document.getElementById('quickModalNote')?.value?.trim();
    const resolution = document.getElementById('quickModalResolution')?.value?.trim();

    // Validasi pending — catatan wajib
    if (currentAction === 'status' && currentValue === 'pending' && !note) {
        const noteInput = document.getElementById('quickModalNote');
        noteInput.style.borderColor = '#dc2626';
        noteInput.focus();
        noteInput.placeholder = '⚠ Alasan pending wajib diisi!';
        return;
    }

    // Validasi resolved — resolution notes wajib
    if (currentAction === 'status' && currentValue === 'resolved' && !resolution) {
        const resInput = document.getElementById('quickModalResolution');
        resInput.style.borderColor = '#dc2626';
        resInput.focus();
        resInput.placeholder = '⚠ Catatan penyelesaian wajib diisi!';
        return;
    }

    // Buat form dan submit
    const form  = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    if (currentAction === 'status') {
        form.action = `/support/tickets/${currentTicketId}/status`;
    } else {
        form.action = `/support/tickets/${currentTicketId}/priority`;
    }

    // CSRF
    const csrf   = document.createElement('input');
    csrf.type    = 'hidden';
    csrf.name    = '_token';
    csrf.value   = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrf);

    // Method
    const method = document.createElement('input');
    method.type  = 'hidden';
    method.name  = '_method';
    method.value = 'PATCH';
    form.appendChild(method);

    // Value
    const valueInput = document.createElement('input');
    valueInput.type  = 'hidden';
    valueInput.name  = currentAction === 'status' ? 'status' : 'priority_id';
    valueInput.value = currentValue;
    form.appendChild(valueInput);

    // Note
    if (note) {
        const noteInput = document.createElement('input');
        noteInput.type  = 'hidden';
        noteInput.name  = 'note';
        noteInput.value = note;
        form.appendChild(noteInput);
    }

    // Resolution notes
    if (resolution && currentValue === 'resolved') {
        const resInput = document.createElement('input');
        resInput.type  = 'hidden';
        resInput.name  = 'resolution_notes';
        resInput.value = resolution;
        form.appendChild(resInput);
    }

    document.body.appendChild(form);
    form.submit();
}

// ── Close modal saat klik overlay ─────────
document.getElementById('quickModalOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) closeQuickModal();
});
