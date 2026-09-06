document.addEventListener('DOMContentLoaded', () => {
    const $ = id => document.getElementById(id);

    const panel = $('add-reports-panel');
    const addBtn = $('add-reports-btn');
    const confirmBtn = $('confirm-add-btn');
    const candidateList = $('candidate-report-list');

    if (!addBtn || !panel) return; // nothing to wire up on this page

    addBtn.addEventListener('click', () => {
        panel.classList.toggle('hidden');
    });

    const updateConfirmState = () => {
        const selected = candidateList
            ? candidateList.querySelectorAll('.candidate-checkbox:checked')
            : [];
        confirmBtn.disabled = selected.length === 0;
    };

    if (candidateList) {
        candidateList.addEventListener('change', updateConfirmState);
    }

    confirmBtn.addEventListener('click', async (e) => {
        const selected = Array.from(candidateList.querySelectorAll('.candidate-checkbox:checked'))
            .map(cb => cb.value);

        if (!selected.length) return;

        e.target.disabled = true;

        const params = new URLSearchParams();
        params.append('ticket_id', TICKET_ID);
        selected.forEach(id => params.append('report_ids[]', id));

        try {
            const res = await fetch('add_to_ticket.php', {
                method: 'POST',
                body: params
            });

            const responseText = await res.text();

            if (responseText.startsWith('SUCCESS:')) {
                // Reload so the linked list, candidate list, and report counts
                // all reflect the server's current state rather than trying
                // to patch the DOM by hand.
                window.location.reload();
            } else {
                alert(responseText);
                e.target.disabled = false;
            }
        } catch (err) {
            alert('Network error adding reports to this ticket.');
            e.target.disabled = false;
        }
    });

    updateConfirmState();
});
