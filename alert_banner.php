<?php
// Renders the sticky-top alert banner. Include this file, then call
// render_alert_banner($alerts) wherever the banner should appear —
// typically right below the page header, above the main content.


function render_alert_banner($alerts) {
    if (empty($alerts)) {
        return;
    }
    ?>
    <div class="alert-banner" role="alert">
        <?php foreach ($alerts as $alert): ?>
            <div class="alert-banner-item" data-alert-id="<?php echo $alert['notice_id']; ?>">
                <a class="alert-banner-link" href="#notice-<?php echo $alert['notice_id']; ?>">
                    <span class="material-symbols-outlined alert-banner-icon">warning</span>
                    <div class="alert-banner-text">
                        <p class="alert-banner-title"><?php echo htmlspecialchars($alert['title']); ?></p>
                        <p class="alert-banner-excerpt"><?php echo htmlspecialchars($alert['content']); ?></p>
                    </div>
                    <span class="alert-banner-time"><?php echo format_alert_time($alert['created_at']); ?></span>
                </a>
                <button class="alert-banner-dismiss" type="button" aria-label="Dismiss alert">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}