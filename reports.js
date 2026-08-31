document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id); // Helper to shorten DOM queries
    
    const updateUI = () => {
        const selected = document.querySelectorAll('.report-checkbox:checked');
        $('selection-count').textContent = `${selected.length} selected`;
        $('create-ticket-btn').disabled = !selected.length;
        
        document.querySelectorAll('.report-checkbox').forEach(cb => {
            cb.closest('.report-row').classList.toggle('selected', cb.checked);
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
    });

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

    updateUI();
});