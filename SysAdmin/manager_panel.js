document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('managePanelOverlay');

    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('open');
            }
        });
    }
});