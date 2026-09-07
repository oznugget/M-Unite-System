<?php include 'public_notices_data.php'; ?>

<!Doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Public Notices</title>
        <link rel="stylesheet" href="public_notices.css">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;500;600;700&family=TikTok+Sans:opsz,wght@12..36,400;12..36,500;12..36,600;12..36,700&display=swap" rel="stylesheet">
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
            <section class="public-notices-intro">
                <h1>Town notices</h1>
                <p>See what's happening around town.</p>
            </section>

            <section class="notice-group">
                <?php if (!empty($public_notices_grouped['today'])): ?>
                <div class="time-bucket today-notices">
                    <h3>Today</h3>
                    <?php foreach ($public_notices_grouped['today'] as $n) { render_public_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($public_notices_grouped['yesterday'])): ?>
                <div class="time-bucket yesterday-notices">
                    <h3>Yesterday</h3>
                    <?php foreach ($public_notices_grouped['yesterday'] as $n) { render_public_notice_card($n); } ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($public_notices_grouped['earlier'])): ?>
                <div class="time-bucket earlier-notices">
                    <h3>Earlier</h3>
                    <?php foreach ($public_notices_grouped['earlier'] as $n) { render_public_notice_card($n); } ?>
                </div>
                <?php endif; ?>
            </section>

            <p class="no-notices" <?php echo empty($public_notices) ? '' : 'hidden'; ?>>No notices right now</p>
        </main>
        <footer>
            <p>&copy; 2026 M-Unite</p>
        </footer>
    </body>
</html>