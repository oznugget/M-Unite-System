<?php
// Central lookup for report/ticket category_id -> display name.
// Keep this in sync with the `categories` table.
$CATEGORY_NAMES = [
    1 => 'Electricity',
    2 => 'Water',
    3 => 'Roads',
    4 => 'Animals',
    5 => 'Sanitation',
    6 => 'Vandalism',
    7 => 'Waste Management',
    8 => 'Environmental Incidents',
];

/**
 * Human-readable category name for a category_id.
 * Pass null (e.g. a legacy mixed ticket) to get 'Mixed'.
 */
function category_name($id): string {
    global $CATEGORY_NAMES;
    if ($id === null || $id === '') {
        return 'Mixed';
    }
    return $CATEGORY_NAMES[(int)$id] ?? 'Unknown';
}

/**
 * Stable CSS class for color-coding a category badge/pill.
 * Based on the id (not the name) so relabeling categories later
 * doesn't require touching the CSS.
 */
function category_class($id): string {
    if ($id === null || $id === '') {
        return 'type-cat-mixed';
    }
    return 'type-cat-' . (int)$id;
}
