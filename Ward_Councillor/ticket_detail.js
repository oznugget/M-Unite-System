document.addEventListener('DOMContentLoaded', function () {
    setupAddReportsPanel();
    setupStatusUpdate();
    setupCommentForm();

    if (window.initReportInteractions) initReportInteractions();
});

// Shorthand for document.getElementById
function $(id) {
    return document.getElementById(id);
}

// =================================================================
// Add existing reports to this ticket
// =================================================================
function setupAddReportsPanel() {
    const panel = $('add-reports-panel');
    const toggleBtn = $('add-reports-btn');
    const confirmBtn = $('confirm-add-btn');
    const candidateList = $('candidate-report-list');

    if (!panel || !toggleBtn) return; // nothing to wire up on this page

    toggleBtn.addEventListener('click', function () {
        panel.classList.toggle('hidden');
    });

    if (candidateList) {
        candidateList.addEventListener('change', function () {
            const selected = getSelectedReportIds(candidateList);
            confirmBtn.disabled = selected.length === 0;
        });
    }

    confirmBtn.addEventListener('click', function () {
        addSelectedReports(candidateList, confirmBtn);
    });
}

function getSelectedReportIds(candidateList) {
    const checked = candidateList.querySelectorAll('.candidate-checkbox:checked');
    return Array.from(checked).map(function (checkbox) {
        return checkbox.value;
    });
}

async function addSelectedReports(candidateList, confirmBtn) {
    const selectedIds = getSelectedReportIds(candidateList);
    if (selectedIds.length === 0) return;

    confirmBtn.disabled = true;

    const params = new URLSearchParams();
    params.append('ticket_id', TICKET_ID);
    selectedIds.forEach(function (id) {
        params.append('report_ids[]', id);
    });

    try {
        const response = await fetch('add_to_ticket.php', { method: 'POST', body: params });
        const result = await response.text();

        if (result.startsWith('SUCCESS:')) {
            // Reload so the linked list, candidate list, and report counts
            // all reflect the server's current state rather than trying
            // to patch the DOM by hand.
            window.location.reload();
        } else {
            alert(result);
            confirmBtn.disabled = false;
        }
    } catch (err) {
        alert('Network error adding reports to this ticket.');
        confirmBtn.disabled = false;
    }
}

// =================================================================
// Update the ticket's status (cascades to every linked report)
// =================================================================
function setupStatusUpdate() {
    const select = $('status-select');
    const updateBtn = $('update-status-btn');

    if (!select || !updateBtn) return;

    // Only enable the button once the dropdown actually differs from
    // the ticket's current status.
    select.addEventListener('change', function () {
        updateBtn.disabled = select.value === CURRENT_STATUS;
    });

    updateBtn.addEventListener('click', function () {
        updateTicketStatus(select, updateBtn);
    });
}

async function updateTicketStatus(select, updateBtn) {
    const newStatus = select.value;
    updateBtn.disabled = true;

    const params = new URLSearchParams();
    params.append('ticket_id', TICKET_ID);
    params.append('status', newStatus);

    try {
        const response = await fetch('update_ticket_status.php', { method: 'POST', body: params });
        const result = await response.text();

        if (result.startsWith('SUCCESS:')) {
            // Reload so the badge here and every linked report's badge
            // both reflect the cascade update.
            window.location.reload();
        } else {
            alert(result);
            updateBtn.disabled = false;
        }
    } catch (err) {
        alert('Network error updating ticket status.');
        updateBtn.disabled = false;
    }
}

// =================================================================
// Post a new comment
// =================================================================
function setupCommentForm() {
    const form = $('add-comment-form');
    const input = $('comment-text');
    const list = $('comment-list');
    const submitBtn = $('comment-submit-btn');

    if (!form) return;

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        postComment(input, list, submitBtn);
    });
}

async function postComment(input, list, submitBtn) {
    const text = input.value.trim();
    if (!text) return;

    submitBtn.disabled = true;

    const params = new URLSearchParams();
    params.append('ticket_id', TICKET_ID);
    params.append('comment_text', text);

    try {
        const response = await fetch('add_comment.php', { method: 'POST', body: params });
        const result = await response.text();

        if (result.startsWith('SUCCESS:')) {
            const emptyState = $('comment-empty-state');
            if (emptyState) emptyState.remove();

            list.prepend(buildCommentRow(CURRENT_USERNAME, text));
            input.value = '';
        } else {
            alert(result);
        }
    } catch (err) {
        alert('Network error adding comment.');
    } finally {
        submitBtn.disabled = false;
    }
}

// Builds a comment row using DOM APIs (not innerHTML), so the username and
// comment text are never parsed as markup, no matter what they contain.
function buildCommentRow(username, text) {
    const row = document.createElement('div');
    row.className = 'comment-row';

    const meta = document.createElement('div');
    meta.className = 'comment-meta';

    const usernameEl = document.createElement('span');
    usernameEl.className = 'comment-username';
    usernameEl.textContent = username;

    const timeEl = document.createElement('span');
    timeEl.className = 'comment-time';
    timeEl.textContent = 'Just now';

    meta.append(usernameEl, timeEl);

    const textEl = document.createElement('p');
    textEl.className = 'comment-text';
    textEl.textContent = text;

    row.append(meta, textEl);
    return row;
}
