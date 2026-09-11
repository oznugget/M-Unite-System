/* MODAL POPUP LOGIC */
function openNoticeModal(cardElement) {
    if (!cardElement) return;

    const modalOverlay = document.getElementById("notice-modal-overlay");
    const title = cardElement.dataset.fullTitle || "";
    const content = cardElement.dataset.fullContent || "";
    const time = cardElement.dataset.time || "";
    const icon = cardElement.dataset.icon || "notifications";
    const category = cardElement.dataset.category || "";
    const author = cardElement.dataset.author || "";

    document.getElementById("modal-title").textContent = title;
    document.getElementById("modal-content").textContent = content;
    document.getElementById("modal-time").textContent = time;
    document.getElementById("modal-icon").textContent = icon;
    document.getElementById("modal-category").textContent = category.charAt(0).toUpperCase() + category.slice(1);

    const authorElem = document.getElementById("modal-author");
    const authorWrapper = document.getElementById("modal-author-wrapper");
    if (author) {
        authorElem.textContent = author;
        authorWrapper.style.display = "inline";
    } else {
        authorWrapper.style.display = "none";
    }

    modalOverlay.classList.add("active");
    modalOverlay.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden"; // Prevent background scrolling
}

function closeNoticeModal() {
    const modalOverlay = document.getElementById("notice-modal-overlay");
    if (modalOverlay) {
        modalOverlay.classList.remove("active");
        modalOverlay.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
    }
}

// Close modal when clicking on the dark background overlay
document.addEventListener("DOMContentLoaded", function () {
    const modalOverlay = document.getElementById("notice-modal-overlay");
    if (modalOverlay) {
        modalOverlay.addEventListener("click", function (event) {
            if (event.target === modalOverlay) {
                closeNoticeModal();
            }
        });
    }
});