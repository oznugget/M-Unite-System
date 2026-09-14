document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('managePanelOverlay');
    const closeBtn = document.getElementById('managePanelClose');

    // Open panel when any "Manage" link is clicked
    document.querySelectorAll('.manage-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const username = this.getAttribute('data-username');

            // TODO next step: fetch this user's real data using `username`
            // and populate the #mp-* fields before opening the panel.
            console.log('Manage clicked for:', username);

            overlay.classList.add('open');
        });
    });

    // Close panel
    closeBtn.addEventListener('click', function () {
        overlay.classList.remove('open');
    });

    // Close when clicking the dark overlay background (not the panel itself)
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            overlay.classList.remove('open');
        }
    });
});