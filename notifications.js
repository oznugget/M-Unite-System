/* COMBINED FILTERING & DOM MANAGEMENT */

const unreadToggle = document.querySelector(".unread-toggle input");
const filterButtons = document.querySelectorAll(".notif-type-tab nav button");
const categoryFilter = document.querySelector(".category-filter");
const searchInput = document.querySelector(".notification-search input");

let currentType = "all";
let currentCategory = "";
let currentSearch = "";

const singleTabEmptyMessage = document.querySelector(".single-tab-empty-message");
const groupTitles = document.querySelectorAll(".notice-group .group-title");

function applyFilters() {
    const allCards = document.querySelectorAll(".notif-card");
    const isUnreadOnly = unreadToggle.checked;

    allCards.forEach(card => {
        const matchesType = (currentType === "all") || (card.dataset.notifType === currentType);
        const matchesCategory = (currentCategory === "") || (card.dataset.category.toLowerCase() === currentCategory.toLowerCase());

        const titleText = card.querySelector(".notif-title")?.textContent.toLowerCase() || "";
        const msgText = card.querySelector(".notif-msg")?.textContent.toLowerCase() || "";
        const matchesSearch = (currentSearch === "") || titleText.includes(currentSearch) || msgText.includes(currentSearch);

        const matchesUnread = !isUnreadOnly || card.classList.contains("unread");

        const shouldShow = matchesType && matchesCategory && matchesSearch && matchesUnread;

        card.style.display = shouldShow ? "flex" : "none";
    });

    // Clean up empty time buckets (Today / Yesterday / Earlier)
    const timeBuckets = document.querySelectorAll(".time-bucket, .time-bucket-cards");
    timeBuckets.forEach(bucket => {
        const cards = bucket.querySelectorAll(".notif-card");
        const hasVisibleCard = Array.from(cards).some(card => card.style.display !== "none");
        bucket.style.display = hasVisibleCard ? "" : "none";
    });

    const personalGroup = document.querySelector(".notice-group[data-group='personal']");
    const townwideGroup = document.querySelector(".notice-group[data-group='townwide']");
    const isAllTab = currentType === "all";

    if (isAllTab) {
        // ALL tab: show section headings, each section manages its own empty message
        groupTitles.forEach(title => title.hidden = false);
        singleTabEmptyMessage.hidden = true;

        [personalGroup, townwideGroup].forEach(group => {
            const cards = group.querySelectorAll(".notif-card");
            const hasVisibleCard = Array.from(cards).some(card => card.style.display !== "none");
            const emptyMessage = group.querySelector(".group-empty-message");
            if (emptyMessage) emptyMessage.hidden = hasVisibleCard;
        });
    } else {
        // Specific tab (Reports/Ward/General): flat list, no section headings, single empty message
        groupTitles.forEach(title => title.hidden = true);

        [personalGroup, townwideGroup].forEach(group => {
            const emptyMessage = group.querySelector(".group-empty-message");
            if (emptyMessage) emptyMessage.hidden = true;
        });

        const hasAnyVisibleCard = Array.from(allCards).some(card => card.style.display !== "none");
        singleTabEmptyMessage.hidden = hasAnyVisibleCard;
    }

    // Priority alerts group: hide entirely if nothing in it matches
    const alertGroup = document.querySelector(".notice-group.alert-group");
    if (alertGroup) {
        const cards = alertGroup.querySelectorAll(".notif-card");
        const hasVisibleCard = Array.from(cards).some(card => card.style.display !== "none");
        alertGroup.style.display = hasVisibleCard ? "" : "none";
    }
}

    // Clean up empty time buckets (Today / Yesterday / Earlier)
    const timeBuckets = document.querySelectorAll(".time-bucket, .time-bucket-cards");
    timeBuckets.forEach(bucket => {
        const cards = bucket.querySelectorAll(".notif-card");
        const hasVisibleCard = Array.from(cards).some(card => card.style.display !== "none");
        bucket.style.display = hasVisibleCard ? "" : "none";
    });

    // Per-section empty messaging: Personal Updates / Town-Wide Notices
    // Each section shows its own "No new messages" independently of the other.
    const noticeGroups = document.querySelectorAll(
        ".notice-group[data-group='personal'], .notice-group[data-group='townwide']"
    );
    noticeGroups.forEach(group => {
        const cards = group.querySelectorAll(".notif-card");
        const hasVisibleCard = Array.from(cards).some(card => card.style.display !== "none");
        const emptyMessage = group.querySelector(".group-empty-message");
        if (emptyMessage) {
            emptyMessage.hidden = hasVisibleCard;
        }
    });

    // Priority alerts group has no "empty" text of its own — just hide it
    // entirely if nothing in it matches the current filters.
    const alertGroup = document.querySelector(".notice-group.alert-group");
    if (alertGroup) {
        const cards = alertGroup.querySelectorAll(".notif-card");
        const hasVisibleCard = Array.from(cards).some(card => card.style.display !== "none");
        alertGroup.style.display = hasVisibleCard ? "" : "none";
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