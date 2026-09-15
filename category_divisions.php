<?php
// Maps a municipal_officers.division value to the category_id(s) it covers.
//
// FLAGGED — this mapping is my best guess reconciling your division enum
// against the category_id values implied by categories.php / the
// .type-cat-N CSS classes (1 Electricity, 2 Water, 3 Roads, 4 Animals,
// 5 Sanitation, 6 Vandalism, 7 Waste Management, 8 Environmental Incidents).
// Two things don't line up and need your call:
//   - 'Water & Sanitation' — I've mapped it to BOTH category 2 (Water) and
//     category 5 (Sanitation), since your enum merges what your categories
//     keep separate. If that's wrong, split the division or split the map.
//   - 'Transport' has no matching category_id in the CSS list, and category
//     8 (Environmental Incidents) has no matching division at all — so
//     right now no officer can ever see an Environmental Incidents ticket.
//     Confirm whether that's intentional or a gap to fill.

function division_category_ids(string $division): array {
    $map = [
        'Electricity'        => [1],
        'Water & Sanitation'  => [2, 5],
        'Roads'               => [3],
        'Animals'             => [4],
        'Transport'           => [],
        'Vandalism'           => [6],
        'Waste Management'    => [7],
    ];
    return $map[$division] ?? [];
}
