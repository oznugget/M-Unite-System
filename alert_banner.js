document.addEventListener("DOMContentLoaded", function () {
    const banner = document.querySelector(".alert-banner");
    if (!banner) return;

    const STORAGE_KEY = "dismissedAlerts";

    function getDismissedIds() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function saveDismissedId(id) {
        const dismissed = getDismissedIds();
        if (!dismissed.includes(id)) {
            dismissed.push(id);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(dismissed));
        }
    }

    function removeBannerIfEmpty() {
        const remaining = banner.querySelectorAll(".alert-banner-item");
        if (remaining.length === 0) {
            banner.remove();
        }
    }

    // Hide any alerts the visitor already dismissed in a previous visit
    const dismissedIds = getDismissedIds();
    banner.querySelectorAll(".alert-banner-item").forEach(item => {
        const alertId = item.dataset.alertId;
        if (dismissedIds.includes(alertId)) {
            item.remove();
        }
    });
    removeBannerIfEmpty();

    // Wire up dismiss buttons for alerts still showing
    banner.querySelectorAll(".alert-banner-dismiss").forEach(button => {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            const item = button.closest(".alert-banner-item");
            const alertId = item.dataset.alertId;
            saveDismissedId(alertId);
            item.remove();
            removeBannerIfEmpty();
        });
    });
});