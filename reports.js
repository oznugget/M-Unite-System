document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id); // Helper to shorten DOM queries

    // Remember the server-rendered (timestamp DESC) order so "No grouping" can restore it
    document.querySelectorAll('.report-row').forEach((row, i) => { row.dataset.order = i; });

    const updateUI = () => {
        const selected = document.querySelectorAll('.report-checkbox:checked');
        $('selection-count').textContent = `${selected.length} selected`;
        $('create-ticket-btn').disabled = !selected.length;
        $('add-existing-btn').disabled = !selected.length;

        document.querySelectorAll('.report-checkbox').forEach(cb => {
            cb.closest('.report-row').classList.toggle('selected', cb.checked);
        });
    };

    // Groups visible report rows under a header by street or category.
    // Re-run whenever the group-by choice or the type filter changes.
    const applyGrouping = () => {
        const list = $('report-list');
        list.querySelectorAll('.group-header').forEach(h => h.remove());

        const groupBy = $('group-by-filter').value; // '', 'street', 'category'
        const rows = Array.from(list.querySelectorAll('.report-row'));

        if (!groupBy) {
            rows.sort((a, b) => Number(a.dataset.order) - Number(b.dataset.order));
            rows.forEach(r => list.appendChild(r));
            return;
        }

        const keyFor = row => (groupBy === 'street' ? row.dataset.street : row.dataset.type) || 'Unknown';

        const groups = new Map();
        rows.forEach(row => {
            const key = keyFor(row);
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(row);
        });

        Array.from(groups.keys()).sort((a, b) => a.localeCompare(b)).forEach(key => {
            const groupRows = groups.get(key);
            const anyVisible = groupRows.some(r => r.style.display !== 'none');

            const header = document.createElement('div');
            header.className = 'group-header';
            header.textContent = `${key} (${groupRows.length})`;
            if (!anyVisible) header.style.display = 'none';

            list.appendChild(header);
            groupRows.forEach(r => list.appendChild(r));
        });
    };

    $('select-all').addEventListener('change', e => {
        document.querySelectorAll('.report-checkbox').forEach(cb => {
            if (cb.closest('.report-row').style.display !== 'none') cb.checked = e.target.checked;
        });
        updateUI();
    });

    $('report-list').addEventListener('change', updateUI);

    $('type-filter').addEventListener('change', e => {
        document.querySelectorAll('.report-row').forEach(row => {
            row.style.display = (!e.target.value || row.dataset.type === e.target.value) ? '' : 'none';
        });
        $('select-all').checked = false;
        updateUI();
        applyGrouping();
    });

    $('group-by-filter').addEventListener('change', applyGrouping);

    $('create-ticket-btn').addEventListener('click', () => {
        const count = document.querySelectorAll('.report-checkbox:checked').length;
        $('modal-report-count').textContent = `${count} report(s) will be aggregated.`;
        $('ticket-title').value = $('ticket-desc').value = '';
        $('ticket-modal').classList.remove('hidden');
    });

    $('modal-cancel').addEventListener('click', () => $('ticket-modal').classList.add('hidden'));

    $('modal-submit').addEventListener('click', async (e) => {
        const title = $('ticket-title').value.trim();
        const desc = $('ticket-desc').value.trim();
        const selected = Array.from(document.querySelectorAll('.report-checkbox:checked')).map(cb => cb.value);

        if (!title || !desc) return alert('Please provide a title and description.');

        e.target.disabled = true;

        // Replaced JSON payload with standard Form Data (URL encoded)
        const params = new URLSearchParams();
        params.append('title', title);
        params.append('description', desc);
        selected.forEach(id => params.append('report_ids[]', id));

        try {
            const res = await fetch('create_ticket.php', {
                method: 'POST',
                body: params // Automatically sets Content-Type to application/x-www-form-urlencoded
            });
            
            // Replaced JSON response handling with plain text
            const responseText = await res.text(); 
            
            if (responseText.startsWith('SUCCESS:')) {
                const ticketId = responseText.split(':')[1];
                selected.forEach(id => document.querySelector(`.report-row[data-id="${id}"]`).remove());
                $('ticket-modal').classList.add('hidden');
                updateUI();
                window.location.href = `ticket.php?id=${ticketId}`;
            } else {
                alert(responseText); 
            }
        } catch (err) {
            alert('Network error creating ticket.');
        } finally {
            e.target.disabled = false;
        }
    });

    $('add-existing-btn').addEventListener('click', () => {
        const count = document.querySelectorAll('.report-checkbox:checked').length;
        $('existing-modal-report-count').textContent = `${count} report(s) will be added to the selected ticket.`;
        $('existing-ticket-modal').classList.remove('hidden');
    });

    $('existing-modal-cancel').addEventListener('click', () => $('existing-ticket-modal').classList.add('hidden'));

    $('existing-modal-submit').addEventListener('click', async (e) => {
        const ticketId = $('existing-ticket-select').value;
        const selected = Array.from(document.querySelectorAll('.report-checkbox:checked')).map(cb => cb.value);

        if (!ticketId) return alert('Please choose a ticket.');
        if (!selected.length) return alert('Select at least one report first.');

        e.target.disabled = true;

        const params = new URLSearchParams();
        params.append('ticket_id', ticketId);
        selected.forEach(id => params.append('report_ids[]', id));

        try {
            const res = await fetch('add_to_ticket.php', {
                method: 'POST',
                body: params
            });

            const responseText = await res.text();

            if (responseText.startsWith('SUCCESS:')) {
                window.location.href = `ticket.php?id=${ticketId}`;
            } else {
                alert(responseText);
            }
        } catch (err) {
            alert('Network error adding reports to ticket.');
        } finally {
            e.target.disabled = false;
        }
    });

    updateUI();
    applyGrouping();

    if (window.initReportInteractions) initReportInteractions();
});