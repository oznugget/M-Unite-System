document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id); // Helper to shorten DOM queries

    // Keep in sync with categories.php — used purely for display (grouping
    // headers, the mixed-type warning). Validation always happens
    // server-side too.
    const CATEGORY_NAMES = {
        '1': 'Electricity',
        '2': 'Water',
        '3': 'Roads',
        '4': 'Animals',
        '5': 'Sanitation',
        '6': 'Vandalism',
        '7': 'Waste Management',
        '8': 'Environmental Incidents',
    };
    const categoryName = id => CATEGORY_NAMES[id] || 'Unknown';

    // Remember the server-rendered (timestamp DESC) order so "No grouping" can restore it
    document.querySelectorAll('.report-row').forEach((row, i) => { row.dataset.order = i; });

    // Returns the distinct set of category ids among currently checked reports.
    const selectedTypes = () => {
        const types = new Set();
        document.querySelectorAll('.report-checkbox:checked').forEach(cb => {
            types.add(cb.closest('.report-row').dataset.type);
        });
        return types;
    };

    const updateUI = () => {
        const selected = document.querySelectorAll('.report-checkbox:checked');
        $('selection-count').textContent = `${selected.length} selected`;

        const types = selectedTypes();
        const mixedTypes = types.size > 1;
        $('mixed-type-warning').classList.toggle('hidden', !selected.length || !mixedTypes);

        $('create-ticket-btn').disabled = !selected.length || mixedTypes;
        $('add-existing-btn').disabled = !selected.length || mixedTypes;

        // Restrict the "add to existing ticket" dropdown to tickets whose
        // category matches the single type currently selected.
        const onlyType = types.size === 1 ? [...types][0] : null;
        document.querySelectorAll('#existing-ticket-select option[data-category]').forEach(opt => {
            const matches = onlyType === null || opt.dataset.category === onlyType;
            opt.hidden = !matches;
            opt.disabled = !matches;
        });

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

        const keyFor = row => {
            if (groupBy === 'street') return row.dataset.street || 'Unknown';
            return categoryName(row.dataset.type);
        };

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
        if (selectedTypes().size > 1) {
            alert('Please select reports of one type only to create a ticket.');
            return;
        }
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
        if (selectedTypes().size > 1) {
            alert('Please select reports of one type only to add to a ticket.');
            return;
        }
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
