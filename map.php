<?php
// map.php – main page + API endpoints using MySQLi

// ----- error reporting (remove in production) -----
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----- include your existing database connection -----
require_once 'db_connect.php';  // provides $conn (MySQLi)    
require_once 'categories.php';    // provides $CATEGORY_NAMES

// Colours per category id. Names live in categories.php so they stay
// in sync with the DB – only the palette is defined here.
$CATEGORY_COLORS = [
    1 => '#E3B505',   // Electricity
    2 => '#3399CC',   // Water
    3 => '#F36E39',   // Roads
    4 => '#8B5A2B',   // Animals
    5 => '#6B8E23',   // Sanitation
    6 => '#C0392B',   // Vandalism
    7 => '#7D3C98',   // Waste Management
    8 => '#16A085',   // Environmental Incidents
];

function getCategoryInfo($id) {
    global $CATEGORY_NAMES, $CATEGORY_COLORS;
    $id = (int)$id;
    return [
        'name'  => $CATEGORY_NAMES[$id] ?? 'Unknown',
        'color' => $CATEGORY_COLORS[$id] ?? '#999999',
    ];
}
// ----- Category mapping (static) -----


// ----- API endpoints -----
$action = $_GET['action'] ?? '';

// 1) Get categories
if ($action === 'get_categories') {
    $categories = [];
    foreach ($CATEGORY_NAMES as $id => $name) {
        $categories[] = [
            'id'    => $id,
            'name'  => $name,
            'color' => $CATEGORY_COLORS[$id] ?? '#999999',
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($categories);
    exit;
}
// 2) Get distinct streets
if ($action === 'get_streets') {
    $query = "SELECT DISTINCT street_name FROM reports WHERE street_name IS NOT NULL AND street_name != '' ORDER BY street_name";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($conn)]);
        exit;
    }
    $streets = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $streets[] = $row['street_name'];
    }
    header('Content-Type: application/json');
    echo json_encode($streets);
    exit;
}

// 3) Get filtered reports
if ($action === 'get_reports') {
    $params = [];
    $types  = '';
    $where  = [];

    // Only pull reports that actually have coordinates.
    // (Filtering in SQL is cheaper than fetching and discarding in PHP.)
    $sql = "SELECT report_id, category_id,
                   street_number, street_name, surburb, town, postal_code,
                   description, current_status,
                   longitude, latitude
            FROM reports
            WHERE longitude IS NOT NULL
              AND latitude  IS NOT NULL
              AND NOT (longitude = 0 AND latitude = 0)";

    if (!empty($_GET['category_id'])) {
        $where[]  = "category_id = ?";
        $params[] = intval($_GET['category_id']);
        $types   .= 'i';
    }
    if (!empty($_GET['street'])) {
        $where[]  = "street_name LIKE ?";
        $params[] = '%' . $_GET['street'] . '%';
        $types   .= 's';
    }
    if (!empty($_GET['status'])) {
        $where[]  = "current_status = ?";
        $params[] = $_GET['status'];
        $types   .= 's';
    }

    if (!empty($where)) {
        $sql .= " AND " . implode(" AND ", $where);
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . mysqli_error($conn)]);
        exit;
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $reports = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Defensive second check – MySQL DECIMAL comes back as a string
        $lat = $row['latitude'];
        $lng = $row['longitude'];
        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            continue;   // not geocoded – skip it
        }

        $reports[] = [
            'id'            => $row['report_id'],
            'title'         => $row['description'] ? substr($row['description'], 0, 60) : 'Issue',
            'category_id'   => $row['category_id'],
            'street_number' => $row['street_number'] ?? '',
            'street_name'   => $row['street_name'] ?? '',
            'suburb'        => $row['surburb'] ?? '',   // column name as in your table
            'town'          => $row['town'] ?? '',
            'postal_code'   => $row['postal_code'] ?? '',
            'status'        => $row['current_status'] ?? 'Pending',
            'description'   => $row['description'],
            'lat'           => (float)$lat,   // <-- new
            'lng'           => (float)$lng,   // <-- new
        ];
    }

    mysqli_stmt_close($stmt);
    header('Content-Type: application/json');
    echo json_encode($reports);
    exit;
}

// 3b) Get dam levels
if ($action === 'get_dams') {
    $sql = "SELECT dam_id, dam_name, level_percent, supply_area, updated_at
            FROM dams_levels
            ORDER BY dam_name";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($conn)]);
        exit;
    }
    $dams = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $dams[] = [
            'id'         => (int)$row['dam_id'],
            'name'       => $row['dam_name'],
            'level'      => (float)$row['level_percent'],
            'area'       => $row['supply_area'],
            'updated_at' => $row['updated_at'],
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($dams);
    exit;
}




// ----- No action: serve the HTML page (same as before) -----
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <link rel="stylesheet" href="map.css" />
    <link rel="stylesheet" href="header_footer.css" />

</head>
<body>
    <!-- HEADER & NAVIGATION -->
    <header class="site-header">
        <div class="logo-box">
            <img src="images/logo_1.png" alt="M-Unite Logo" class="logo-image" />
            <a href="home.html" class="logo-link"></a>

        </div>
        <nav class="navbar">
            <a href="home.php" class="nav-item">Home</a>
            <a href="CommReports.php" class="nav-item">Reports</a>
            <a href="public_notices.php" class="nav-item">Notices</a>
            <a href="map.php" class="nav-item-active">Map</a>
            <a href="about_us.html" class="nav-item">About Us</a>
        </nav>
        <div class="header-right">
            <a href="signin.php" class="sign-in-btn">
                Sign In <i class="fa-regular fa-circle-user"></i>
            </a>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="about-container">
        <h1 id="heading">Makhanda Community Map</h1>

        <!-- Filter Bar -->
        <section class="content-section">
            <h2 class="section-title text-center">Filter Reported Issues</h2>
            <div class="filter-bar" id="filterBar">
                <div class="filter-group">
                    <label for="filterCategory"><i class="fa-regular fa-tag"></i> Category</label>
                    <select id="filterCategory">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterStreet"><i class="fa-regular fa-location-dot"></i> Street / Area</label>
                    <select id="filterStreet">
                        <option value="">All Streets</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterStatus"><i class="fa-regular fa-circle-check"></i> Status</label>
                    <select id="filterStatus">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn-filter" id="applyFiltersBtn"><i class="fa-regular fa-sliders"></i> Apply Filters</button>
                    <button class="btn-filter btn-filter-outline" id="resetFiltersBtn"><i class="fa-regular fa-rotate-left"></i> Reset</button>
                    <span class="filter-results-count" id="resultCount">0 issues</span>
                </div>
            </div>
        </section>

        <!-- Interactive Map -->
        <section class="content-section text-center">
            <div class="map-toolbar">
                <button id="btn-pins" class="map-toggle-btn active">Pin View</button>
                <button id="btn-heat" class="map-toggle-btn">Heatmap View</button>
            </div>
            <div class="map-wrapper">
                <div id="issue-map"></div>
            </div>
            <div class="map-legend" id="mapLegend"></div>
        </section>

        <!-- Municipal Snapshot -->
        <section class="content-section">
            <h2 class="section-title text-center">Municipal Snapshot</h2>
            <div class="info-grid" id="snapshotGrid">
                <div class="info-card">
                    <h3>Reports by Category</h3>
                    <div id="categoryBreakdown"></div>
                </div>
    
            </div>
            <div class="stats-grid" style="margin-top:20px;">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div>
            <div class="stat-label">Active Reports</div>
            <div class="stat-value" id="statActiveReports">0</div>
            <div class="stat-subvalue" id="statResolvedThisWeek">0 resolved</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-water"></i></div>
        <div>
            <div class="stat-label">Dam Levels (avg)</div>
            <div class="stat-value" id="statAvgDamLevel">—</div>
            <div id="damLevelsList">
                <!-- populated by map.js -->
            </div>
        </div>
    </div>
</div>
        </section>
    </main>

    <!-- CHATBOT -->
    <aside class="chatbot-widget">
        <span class="chatbot-text">Want to chat with Makbot, our AI assistant?</span>
        <button class="chatbot-btn" aria-label="Open Makbot AI Chat">
            <i class="fa-regular fa-comment-dots"></i>
        </button>
    </aside>

       <!-- FOOTER -->
  <footer class="site-footer">

    <img src="images/footerimgresponsive1.png" alt="Makhanda skyline" class="footer-skyline-mobile">
    <img src = "images/footer_img.png" alt = "Makhanda skyline" id = "footerimg">


    <div class="footer-top">
      <!-- Left Info -->
      <div class="footer-col footer-about">
        <div class="footer-logo-box">
            <img src="images\logo_1.png" alt="M-Unite Logo" class="footer-logo">
        </div>
        <p>Connecting residents of Makhanda and the Municipality, enabling you to share and report municipal issues.</p>
      </div>

      <!-- Pages Column -->
      <div class="footer-col">
        <h4>Pages</h4>
        <ul>
          <li><a href="home.php">Home</a></li>
          <li><a href="CommReports.php">Reports</a></li>
          <li><a href="public_notices.html">Notices</a></li>
          <li><a href="map.html">Map</a></li>
          <li><a href="about.html">About Us</a></li>
        </ul>
      </div>

      <!-- Connect Column -->
      <div class="footer-col">
        <h4>Connect</h4>
        <ul>
          <li><a href="#">Report Website Bugs</a></li>
          <li><a href="home.php#volunteerForm">Volunteer</a></li>
          <li><a href="#">info@munite.co.za</a></li>
          <li><a href="tel:+27000000000">+27 000000000</a></li>
        </ul>
      </div>

      <!-- Resources Column -->
      <div class="footer-col">
        <h4>Resources</h4>
        <ul>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="documentation.php">Documentation</a></li>
          <li><a href="Terms_of_use.php">Terms Of Use</a></li>
          <li><a href="#">Copyright Notice</a></li>
        </ul>
      </div>

     <div class="footer-col footer-socials">
        <h4>Socials</h4>
        <div class="social-icons-vertical">
          <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="https://github.com" target="_blank" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>
          <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
        </div>
      </div>
    </div>

  
    <div class="footer-bottom">
      <p>&copy; M-Unite 2026</p>
    </div>
  </footer>

    <!-- Leaflet + plugins -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.js"></script>
    <!-- Main JavaScript -->
    <script src="map.js"></script>
</body>
</html>