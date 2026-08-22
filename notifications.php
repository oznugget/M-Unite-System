<?php include 'notices.php'; ?>
<!Doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Notices</title>
        <link rel="stylesheet" href="notificationstyle.css">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;500;600;700&family=TikTok+Sans:opsz,
            wght@12..36,400;12..36,500;12..36,600;12..36,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    </head>
    <body>
        <header id="notif-header">
            <p>Logo M-Unite</p>
            <nav>
                <a href="">Home</a>
                <a href="">Reports</a>
                <a href="" aria-current="page">Notices</a>
                <a href="">Map</a>
                <a href="">About Us</a>
            </nav>
        </header>
        <main>
            <!--Notifications headers-->
            <section class="filtering-tabs">
                <h1>My notifications</h1><span name="unread-msgs"> <?php echo $unread_count; ?> unread</span>
                <p>See what you've missed out on.</p>
                <button type="button" class="mark-all-read">Mark all read</button>

              <div class="notif-type-tab">

                <!-- TOP ROW -->
                <div class="filter-top-row">

                    <!-- Existing tabs -->
                    <nav aria-label="Notification filters">
                        <button type="button" aria-pressed="true" data-filter="all">All</button>
                        <button type="button" aria-pressed="false" data-filter="ticket">Ticket</button>
                        <button type="button" aria-pressed="false" data-filter="ward">Ward</button>
                        <button type="button" aria-pressed="false" data-filter="general"> General</button>
                    </nav>

                    <!-- Unread toggle -->
                    <label class="unread-toggle">
                        <input type="checkbox">
                        <span class="toggle-slider"></span>
                        <span class="toggle-text">Unread only</span>
                    </label>

                </div>

                <!-- BOTTOM ROW -->
                <div class="filter-bottom-row">

                    <!-- Search -->
                    <div class="notification-search">
                        <span class="search-icon">⌕</span>
                        <input type="search" placeholder="Search notifications..." aria-label="Search notifications">
                    </div>

                    <!-- Category -->
                    <select class="category-filter" aria-label="Filter by category">
                        <option value="">All categories</option>
                        <option value="electricity">Electricity</option>
                        <option value="water">Water & Sanitation</option>
                        <option value="roads">Roads</option>
                        <option value="waste">Waste Management</option>
                        <option value="general">General</option>
                    </select>

                </div>

            </div>
            </section>

            <!--Today messages sections-->
            <section class="today-notices" aria-labelledby="today-heading">
                <h2 id="today-heading" class="">Today</h2>
                
                <!--Notification card 1-->    
                <article class="notif-card unread" data-notification-id="1" data-notif-type="ticket" data-category="water">

                    <!-- Notification icon -->
                    <div class="notif-icon">
                        <span class="material-symbols-outlined">water_drop</span>
                    </div>

                    <!-- Notification content -->
                    <div class="notif-content">

                        <header class="notif-header">
                            <div class="notif-title-row">
                                <span class="unread-dot"></span>
                                <h3 class="notif-title">Report #0002 is now In Progress</h3>
                                <span class="status">In Progress</span>
                            </div>

                            <span class="time">12 min ago</span>
                        </header>

                        <p class="notif-msg">
                            Ward 4 councillor forwarded your burst pipe report on Beaufort Street
                            to municipal officers.
                            <a href="">Show more</a>
                        </p>

                        <footer class="notif-footer">
                            <span class="notif-category">Water &amp; Sanitation</span>
                            <span class="notif-separator">·</span>
                            <span class="notif-author">Ward 4</span>
                        </footer>

                    </div>

                    <!--The  dismiss (X) button on the far right-->
                    <button class="dismiss-btn" type="button" aria-label="Dismiss notification">
                        <span class="material-symbols-outlined">close</span>
                    </button>

                </article>
                    
                     <!--Notification card 2-->
                    <article class="notif-card unread" data-notification-id="2" data-notif-type="ward" data-category="water">

                        <div class="notif-icon">
                            <span class="material-symbols-outlined">water_drop</span>
                        </div>

                        <div class="notif-content">

                            <header class="notif-header">
                                <div class="notif-title-row">
                                    <span class="unread-dot"></span>
                                    <h3 class="notif-title">Water restrictions remain in effect</h3>
                                </div>

                                <span class="time">1 hour ago</span>
                            </header>

                            <p class="notif-msg">
                                Level 2 water restrictions remain in effect town-wide.
                                Irrigation only between 18:00 and 06:00.
                                <a href="">Show more</a>
                            </p>

                            <footer class="notif-footer">
                                <span class="notif-category">Water &amp; Sanitation</span>
                                <span class="notif-separator">·</span>
                                <span class="notif-author">Cllr N. Mbeki</span>
                            </footer>

                        </div>

                        <button class="dismiss-btn" type="button" aria-label="Dismiss notification">
                            <span class="material-symbols-outlined">close</span>
                        </button>

                    </article>
            </section>

            <!-- YESTERDAY SECTION-->
            <section class="yesterday-notices">
                <h2 id="yesterday-heading">Yesterday</h2>

                <!-- Notification card 3 -->
                <article aria-label="unread" class="notif-card unread" data-notification-id="3" data-notif-type="ticket" data-category="electricity">

                    <div class="notif-icon"> <span class="material-symbols-outlined">bolt</span></div>
                    <div class="notif-content">

                        <header class="notif-header">
                            <div class="notif-title-row">
                                <span class="unread-dot"></span>
                                <h3 class="notif-title">
                                    Your report #0001 has been resolved
                                </h3>
                            </div>

                            <span class="time">08 August 2026</span>
                        </header>

                        <p class="notif-msg">
                            Electricity light at your house is now resolved.
                            <a href="">Show more</a>
                        </p>

                        <footer class="notif-footer">
                            <span class="notif-category">Electricity</span>
                            <span class="notif-separator">·</span>
                            <span class="notif-author">Municipal Communications Officer</span>
                        </footer>

                    </div>

                    <button class="dismiss-btn" type="button" aria-label="Dismiss notification">
                        <span class="material-symbols-outlined">close</span>
                    </button>

                </article>

                <!-- Notification card 4 -->
                <article class="notif-card" data-notification-id="4" data-notif-type="general" data-category="general">

                    <div class="notif-icon">
                        <span class="material-symbols-outlined">water_drop</span>
                    </div>

                    <div class="notif-content">

                        <header class="notif-header">
                            <div class="notif-title-row">
                                <h3 class="notif-title">
                                    Dam level update: Settlers Dam at 41%
                                </h3>
                            </div>
                            <span class="time">3 hours ago</span>
                        </header>

                        <p class="notif-msg">
                            Level 2 water restrictions remain in effect town-wide.
                            Irrigation permitted only between 18:00 and 06:00.
                            <a href="">Show more</a>
                        </p>

                        <footer class="notif-footer">
                            <span class="notif-category">General</span>
                            <span class="notif-separator">·</span>
                            <span class="notif-author">Municipal Communications</span>
                        </footer>

                    </div>

                    <button class="dismiss-btn" type="button" aria-label="Dismiss notification">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </article>
            </section>


            <!-- EARLIER SECTION-->
            <section class="earlier-notices" aria-labelledby="earlier-heading">
                <h2 id="earlier-heading">Earlier</h2>

                <!-- Notification card 5 -->
                <article aria-label="unread" class="notif-card unread" data-notification-id="5" data-notif-type="general" data-category="general">

                    <div class="notif-icon general-icon">ℹ</div>

                    <div class="notif-content">

                        <header class="notif-header">
                            <div class="notif-title-row">
                                <span class="unread-dot"></span>
                                <h3 class="notif-title">
                                    Streetlight repairs
                                </h3>
                            </div>

                            <span class="time">30 July 2026</span>
                        </header>

                        <p class="notif-msg">
                            42 street lights were repaired across town during July.
                            <a href="">Show more</a>
                        </p>

                        <footer class="notif-footer">
                            <span class="notif-category">General</span>
                            <span class="notif-separator">·</span>
                            <span class="notif-author">Municipal Officer Communications</span>
                        </footer>

                    </div>

                    <button class="dismiss-btn" type="button" aria-label="Dismiss notification">
                        <span class="material-symbols-outlined">close</span>
                    </button>

                </article>


                <!-- Notification card 6 -->
                <article class="notif-card" data-notification-id="6" data-notif-type="ward" data-category="general">

                    <div class="notif-icon general-icon">ℹ</div>

                    <div class="notif-content">

                        <header class="notif-header">
                            <div class="notif-title-row">
                                <h3 class="notif-title">
                                    Ward 4 for July Summary published
                                </h3>
                            </div>

                            <span class="time">27 July 2026</span>
                        </header>

                        <p class="notif-msg">
                            37 faults logged this month, 24 resolved.
                            Pothole repairs on African Street begin Monday.
                        </p>

                        <footer class="notif-footer">
                            <span class="notif-category">General</span>
                            <span class="notif-separator">·</span>
                            <span class="notif-author">Ward 4 Councillor</span>
                        </footer>

                    </div>

                    <button class="dismiss-btn" type="button" aria-label="Dismiss notification">
                        <span class="material-symbols-outlined">close</span>
                    </button>

                </article>

            </section>
            <!--This part should appear when there are no notices-->
            <p class="no-notifications" hidden>No new messages</p>

            <!--This part should appear only when there are unread notices-->
            <p class="no-unread-notifications" hidden>No unread notifications</p>
        </main>
        <footer>
            <p>&copy; 2026 M-Unite</p>
        </footer>
        <script src="notifications.js"></script>
    </body>
</html>