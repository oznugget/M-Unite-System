// report-common.js
//
// Shared behavior for report rows across reports.php and ticket.php:
//   1. Clicking a report's thumbnail opens that photo full-size (lightbox).
//   2. Clicking anywhere else on a report row opens a detail panel with
//      the full (untruncated) description, photo, address, status, etc.
//
// The modal/lightbox markup is injected once into <body>, so no changes
// were needed to the existing modal HTML in reports.php / ticket.php.
//
// Usage: call initReportInteractions() once per page, after the report
// rows have been rendered. It uses event delegation on <body>, so it
// works for rows added later too (e.g. after AJAX reloads).
//
// Expects each .report-row to optionally carry these data-* attributes,
// set from PHP:
//   data-id, data-type, data-status, data-address, data-time, data-image
// The full description is read from the .report-desc element's `title`
// attribute (already used for the native tooltip), so no extra markup
// is required for that.

(function () {
    let initialized = false;

    function ensureModals() {
        if (document.getElementById('report-detail-overlay')) return;

        const detailOverlay = document.createElement('div');
        detailOverlay.id = 'report-detail-overlay';
        detailOverlay.className = 'modal-overlay hidden';
        detailOverlay.innerHTML = `
            <div class="modal report-detail-modal">
                <button type="button" class="modal-close" id="report-detail-close" aria-label="Close">&times;</button>
                <div class="report-detail-top">
                    <span class="badge" id="rd-status"></span>
                    <span class="report-detail-type" id="rd-type"></span>
                    <span class="report-detail-id" id="rd-id"></span>
                </div>
                <img id="rd-image" class="report-detail-image hidden" alt="Report photo">
                <p class="report-detail-desc" id="rd-desc"></p>
                <div class="report-detail-meta">
                    <span id="rd-address"></span>
                    <span id="rd-time"></span>
                </div>
            </div>`;
        document.body.appendChild(detailOverlay);

        const lightbox = document.createElement('div');
        lightbox.id = 'lightbox-overlay';
        lightbox.className = 'modal-overlay hidden lightbox-overlay';
        lightbox.innerHTML = `
            <button type="button" class="lightbox-close" id="lightbox-close" aria-label="Close">&times;</button>
            <img id="lightbox-image" class="lightbox-image" alt="Report photo, full size">`;
        document.body.appendChild(lightbox);

        detailOverlay.addEventListener('click', (e) => {
            if (e.target === detailOverlay) closeReportDetail();
        });
        document.getElementById('report-detail-close').addEventListener('click', closeReportDetail);

        lightbox.addEventListener('click', () => closeLightbox());
        document.getElementById('lightbox-close').addEventListener('click', (e) => {
            e.stopPropagation();
            closeLightbox();
        });

        // Clicking the (medium) photo inside the detail panel also opens
        // the full-size lightbox.
        document.getElementById('rd-image').addEventListener('click', (e) => {
            e.stopPropagation();
            openLightbox(e.target.src);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closeLightbox();
            closeReportDetail();
        });
    }

    function openLightbox(src) {
        if (!src) return;
        document.getElementById('lightbox-image').src = src;
        document.getElementById('lightbox-overlay').classList.remove('hidden');
    }

    function closeLightbox() {
        document.getElementById('lightbox-overlay').classList.add('hidden');
    }

    function statusToBadgeClass(status) {
        return 'badge badge-' + String(status || '').toLowerCase().replace(/\s+/g, '-');
    }

    function openReportDetail(row) {
        const { id = '', type = '', status = '', address = '', time = '', image = '' } = row.dataset;
        const descEl = row.querySelector('.report-desc');
        const fullDesc = descEl ? (descEl.getAttribute('title') || descEl.textContent) : '';

        const statusBadge = document.getElementById('rd-status');
        if (status) {
            statusBadge.textContent = status;
            statusBadge.className = statusToBadgeClass(status);
            statusBadge.classList.remove('hidden');
        } else {
            statusBadge.classList.add('hidden');
        }

        document.getElementById('rd-type').textContent = type;
        document.getElementById('rd-id').textContent = id ? ('#' + id) : '';
        document.getElementById('rd-desc').textContent = fullDesc;
        document.getElementById('rd-address').textContent = address;
        document.getElementById('rd-time').textContent = time;

        const imgEl = document.getElementById('rd-image');
        if (image) {
            imgEl.src = image;
            imgEl.classList.remove('hidden');
        } else {
            imgEl.removeAttribute('src');
            imgEl.classList.add('hidden');
        }

        document.getElementById('report-detail-overlay').classList.remove('hidden');
    }

    function closeReportDetail() {
        document.getElementById('report-detail-overlay').classList.add('hidden');
    }

    function initReportInteractions() {
        if (initialized) return; // safe to call from multiple scripts on the same page
        initialized = true;

        ensureModals();

        document.body.addEventListener('click', (e) => {
            const thumb = e.target.closest('.report-thumb');
            if (thumb) {
                e.stopPropagation();
                openLightbox(thumb.src);
                return;
            }

            // Don't hijack clicks on interactive controls inside a row
            // (checkboxes, buttons, links, selects).
            if (e.target.closest('input, button, a, select, textarea, label')) return;

            const row = e.target.closest('.report-row');
            if (row) openReportDetail(row);
        });
    }

    window.initReportInteractions = initReportInteractions;
    window.openLightbox = openLightbox;

    document.addEventListener('DOMContentLoaded', initReportInteractions);
})();
