/* COMBINED FILTERING & DOM MANAGEMENT */

const unreadToggle = document.querySelector(".unread-toggle input");
const filterButtons = document.querySelectorAll(".notif-type-tab nav button");
const categoryFilter = document.querySelector(".category-filter");
const searchInput = document.querySelector(".notification-search input");
const noNotifications = document.querySelector(".no-notifications");
const noUnreadMessage = document.querySelector(".no-unread-notifications");

let currentType = "all";
let currentCategory = "";
let currentSearch = "";

function applyFilters() {
    const allCards = document.querySelectorAll(".notif-card");
    const isUnreadOnly = unreadToggle.checked;
    let visibleCount = 0;

    allCards.forEach(card => {
        const matchesType = (currentType === "all") || (card.dataset.notifType === currentType);
        const matchesCategory = (currentCategory === "") || (card.dataset.category.toLowerCase() === currentCategory.toLowerCase());
        
        const titleText = card.querySelector(".notif-title")?.textContent.toLowerCase() || "";
        const msgText = card.querySelector(".notif-msg")?.textContent.toLowerCase() || "";
        const matchesSearch = (currentSearch === "") || titleText.includes(currentSearch) || msgText.includes(currentSearch);
        
        const matchesUnread = !isUnreadOnly || card.classList.contains("unread");

        const shouldShow = matchesType && matchesCategory && matchesSearch && matchesUnread;

        card.style.display = shouldShow ? "flex" : "none";
        if (shouldShow) visibleCount++;
    });

    // Clean up empty time buckets
    const timeBuckets = document.querySelectorAll(".time-bucket, .alert-group");
    timeBuckets.forEach(bucket => {
        const cards = bucket.querySelectorAll(".notif-card");
        const hasVisibleCard = Array.from(cards).some(card => card.style.display !== "none");
        bucket.style.display = hasVisibleCard ? "" : "none";
    });

    // Clean up empty section groups (Personal vs Town-Wide)
    const noticeGroups = document.querySelectorAll(".notice-group");
    noticeGroups.forEach(group => {
        const visibleBuckets = group.querySelectorAll(".time-bucket:not([style*='display: none']), .time-bucket-cards");
        const hasCards = Array.from(group.querySelectorAll(".notif-card")).some(card => card.style.display !== "none");
        group.style.display = hasCards ? "" : "none";
    });

    // Display messaging state
    if (isUnreadOnly && visibleCount === 0) {
        noUnreadMessage.hidden = false;
        noNotifications.hidden = true;
    } else if (visibleCount === 0) {
        noNotifications.hidden = false;
        noUnreadMessage.hidden = true;
    } else {
        noNotifications.hidden = true;
        noUnreadMessage.hidden = true;
    }
}

// Event Listeners
unreadToggle.addEventListener("change", applyFilters);

filterButtons.forEach(button => {
    button.addEventListener("click", function () {
        filterButtons.forEach(btn => btn.setAttribute("aria-pressed", "false"));
        button.setAttribute("aria-pressed", "true");

        currentType = button.dataset.filter;
        applyFilters();
    });
});

categoryFilter.addEventListener("change", function () {
    currentCategory = categoryFilter.value;
    applyFilters();
});

searchInput.addEventListener("input", function () {
    currentSearch = searchInput.value.trim().toLowerCase();
    applyFilters();
});