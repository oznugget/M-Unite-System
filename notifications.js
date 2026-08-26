/* This code is for dismissing notices, displayig unread notices via the unread toggle btn,
mark all unread notices as read at once and individual notice must be marked as read once clicked*/
/* Search will be impplemented in cojunction with php */

/*NOTIFICATION DISMISSAL*/

const dismissButtons = document.querySelectorAll(".dismiss-btn");
// Add click event to each button
dismissButtons.forEach(button => {

    button.addEventListener("click", function () {

        // Find the notification card
        const notification = button.closest(".notif-card");

        // Get the notification ID, might need for php
       // const notificationId = notification.dataset.notificationId;
        const section = notification.closest("section");//section is either Earlier, Yesterday, Today
        notification.remove();

        // Check if the section still has notifications (Earlier, Yesterday, Today)
        const remainingNotifications = section.querySelectorAll(".notif-card");

        // If there are no notifications left , remove sections
        if (remainingNotifications.length === 0) {
            section.remove();
        }
        const allNotifications = document.querySelectorAll(".notif-card");

        // If there are no notifications anywhere
        if (allNotifications.length === 0) {
            // Show "No new messages"
            const noNotifications = document.querySelector(".no-notifications");
            noNotifications.hidden = false;
        }
    });
});

/* DISPLAY UNREAD ONLY */
const unreadToggle = document.querySelector(".unread-toggle input");

// When the toggle is changed
unreadToggle.addEventListener("change", function () {
    const sections = document.querySelectorAll(".today-notices, .yesterday-notices, .earlier-notices");
    const noUnreadMessage = document.querySelector(".no-unread-notifications");

    // Toggle is ON
    if (unreadToggle.checked) {
        let unreadCount = 0;

        // Go through every section
        sections.forEach(section => {

            // Get notifications inside this section
            const notifications = section.querySelectorAll(".notif-card");
            let visibleNotifications = 0;

            // Check each notification
            notifications.forEach(notification => {
                if (notification.classList.contains("unread")) {
                    // Show unread notification
                    notification.style.display = "flex";
                    visibleNotifications++;
                    unreadCount++;
                } else {
                    // Hide read notification
                    notification.style.display = "none";
                }
            });

            // If this section has no unread notifications
            if (visibleNotifications === 0) {
                section.style.display = "none";
            } else {
                section.style.display = "";
            }
        });

        // If there are no unread notifications anywhere
        if (unreadCount === 0) {
            noUnreadMessage.hidden = false;
        } else {
            noUnreadMessage.hidden = true;
        }

    } else { // Toggle is OFF
        
        // Show the "No unread notifications" message
        noUnreadMessage.hidden = true;

        // Show all sections
        sections.forEach(section => {

            section.style.display = "";

            const notifications = section.querySelectorAll(".notif-card");
            // Show all notifications
            notifications.forEach(notification => {
                notification.style.display = "flex";
            });
        });
    }
});

/* FILTER TABS (All / Tickets / Ward / General) */
const filterButtons = document.querySelectorAll(".notif-type-tab nav button");
const noNotifications = document.querySelector(".no-notifications");

filterButtons.forEach(button => {
    button.addEventListener("click", function () {

        // Update active tab
        filterButtons.forEach(btn => {
            btn.setAttribute("aria-pressed", "false");
        });

        button.setAttribute("aria-pressed", "true");

        const selectedType = button.dataset.filter;

        const allCards = document.querySelectorAll(".notif-card");
        const sections = document.querySelectorAll(
            ".today-notices, .yesterday-notices, .earlier-notices"
        );

        let visibleCount = 0;

        // Filter notification cards
        allCards.forEach(card => {

            const matches =
                selectedType === "all" ||
                card.dataset.notifType === selectedType;

            if (matches) {
                card.style.display = "flex";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        // Show/hide date sections
        sections.forEach(section => {

            const cards = section.querySelectorAll(".notif-card");

            const hasVisibleCard = Array.from(cards).some(card => {
                return card.style.display !== "none";
            });

            section.style.display = hasVisibleCard ? "" : "none";
        });

        // Show "No new messages" if filter has no results
        if (visibleCount === 0) {
            noNotifications.hidden = false;
        } else {
            noNotifications.hidden = true;
        }

    });
});

/* CATEGORY FILTER (dropdown) */
const categoryFilter = document.querySelector(".category-filter");
categoryFilter.addEventListener("change", function () {

    const selectedCategory = categoryFilter.value; // "" means "All categories"
    const allCards = document.querySelectorAll(".notif-card");
    const sections = document.querySelectorAll(".today-notices, .yesterday-notices, .earlier-notices");
    let visibleCount = 0;

    allCards.forEach(card => {
        const matches = selectedCategory === "" || card.dataset.category === selectedCategory;
        card.style.display = matches ? "flex" : "none";
        if (matches) visibleCount++;
    });

    // Hide sections that end up with nothing visible
    sections.forEach(section => {
        const visibleCards = section.querySelectorAll('.notif-card:not([style*="display: none"])');
        section.style.display = visibleCards.length === 0 ? "none" : "";

    });
    const noNotifications = document.querySelector(".no-notifications");
    noNotifications.hidden = visibleCount !== 0;
});