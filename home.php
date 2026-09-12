<?php
session_start();
include 'dbConnection.php';
include 'alert_banner.php'; // Contains get_active_alerts() and format_alert_time()
include 'alert_banner_data.php';

$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';

$username   = $isLoggedIn ? $_SESSION['username'] : null;

// Fetch active alerts based on scope (public vs user's ward if logged in)
$alerts = get_active_alerts($conn, $username);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <script src="homejs.js" defer></script>
    <script src="alert_banner.js" defer></script>
    <link rel="stylesheet" href="homecss.css">
    <link rel="stylesheet" href="header_footer.css">
    <link rel="stylesheet" href="alert_banner.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
</head>


<body>

    <header class="site-header">

      <div class="logo-box">
      <a href="home.php" class="logo-link">
      <img src="images/logo_1.png" alt="M-Unite Logo" class="logo-image">
      </a>
    </div>

    <div class="hamburger" id="hamburger-menu">
        <i class="fa-solid fa-bars"></i>
      </div>

    <nav class="navbar" id="nav-menu">
      <a href="home.php" class="nav-item-active">Home</a>
      <a href="CommReports.php" class="nav-item">Reports</a>
      <a href="public_notices.php" class="nav-item">Notices</a>
      <a href="map.php" class="nav-item">Map</a>
      <a href="about_us.html" class="nav-item">About Us</a>
    </nav>
    
      <div class="header-right">
      <?php if ($isLoggedIn): ?>
        <a href="account.php" class="sign-in-btn">
          <?php echo $firstname ?> <i class="fa-regular fa-circle-user"></i>
        </a>
      <?php else: ?>
        <a href="signin.php" class="sign-in-btn">
          Sign In <i class="fa-regular fa-circle-user"></i>
        </a>
      <?php endif; ?>
    </div>
    
    </header>

    <script>
      const hamburger = document.getElementById('hamburger-menu');
      const navMenu = document.getElementById('nav-menu');
      const hamburgerIcon = hamburger.querySelector('i');

      function toggleMenu() {
        const isOpen = navMenu.classList.toggle('active');
        hamburger.classList.toggle('active', isOpen);
        hamburgerIcon.classList.toggle('fa-bars', !isOpen);
        hamburgerIcon.classList.toggle('fa-xmark', isOpen);
      }

      function closeMenu() {
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
        hamburgerIcon.classList.add('fa-bars');
        hamburgerIcon.classList.remove('fa-xmark');
      }

      hamburger.addEventListener('click', toggleMenu);

      // Close the menu after a nav link is tapped
      navMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMenu);
      });

      // Close the menu automatically if the viewport grows past the mobile breakpoint
      window.addEventListener('resize', () => {
        if (window.innerWidth > 768) closeMenu();
      });

      // Close the menu if the user taps/clicks outside of it
      document.addEventListener('click', (event) => {
        const clickedInsideMenu = navMenu.contains(event.target) || hamburger.contains(event.target);
        if (!clickedInsideMenu && navMenu.classList.contains('active')) {
          closeMenu();
        }
      });
    </script>



  
    <section id="carousel">

      <div id="carousel-frame">

        <div id="slides">
          <img class="slide" src="images/monument.jpg" alt="slide1">
          <img class="slide" src="images/municipality.jpg" alt="slide2">
          <img class="slide" src="images/mt.jpg" alt="slide3">
          <img class="slide" src="images/city.jpg" alt="slide4">
          <img class="slide" src="images/art.png" alt="slide5">
          <img class="slide" src="images/volunteers.jpg" alt="slide6">
          <img class="slide" src="images/town.jpg" alt="slide7">
        </div>

        <button class="arrows" id="prev">&#10094;</button>
        <button class="arrows" id="next">&#10095;</button>

        <div class="carousel-text">
        <?php if ($isLoggedIn): ?>
            <p>Welcome <?php echo $firstname .'<br>';?></p>
        <?php endif; ?>
        
          <h1>Sibanye</h1>
          <p>We Are One</p>
        </div>

         
   
        <div class="weather-widget-wrap">
          <a class="weatherwidget-io" href="https://forecast7.com/en/n33d3126d53/grahamstown/" data-label_1="MAKHANDA" 
          data-label_2="WEATHER" data-font="Roboto" data-mode="Current" data-theme="pure" data-basecolor="transparent">MAKHANDA WEATHER</a>
        </div>
        <script>
        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');
        </script>

        <a href="CommReports.php" class="mkrpt">Make Report</a>
    
        <div class="dots" id="dotsContainer"></div>

      </div>

    </section>
    


    <section id="about" class="step-card">
      <div class="about-content">
        <img src="images/timeline3.png" alt="Journey Through M-Unite" class="logo-image-about"  >
      </div>
    </section>



   <section id="localinfo" class="localinfo">
      <h2 class="section-title">Local Information</h2>

      <div class="localinfo-row"> 
        <!-- DAM LEVELS -->
        <div id="dams" class="infocard">
          <h2>Dam Levels</h2>
          <br>
          <?php 
          $damLevels = [
              "Howieson's Poort Dam" => 0,
              "Settlers Dam" => 0,
              "Glen Melville Dam" => 0
          ]; 
          $result = $conn->query("SELECT dam_name, level_percent FROM dams_levels");
          if ($result) {
              while ($row = $result->fetch_assoc()) {
                  if (stripos($row['dam_name'], "Howieson") !== false) $damLevels["Howieson's Poort Dam"] = $row['level_percent'];
                  if (stripos($row['dam_name'], "Settlers") !== false) $damLevels["Settlers Dam"] = $row['level_percent'];
                  if (stripos($row['dam_name'], "Glen") !== false) $damLevels["Glen Melville Dam"] = $row['level_percent'];
              }
          } else {
              echo "<p style='color:red; font-size:12px;'>DB Error: " . $conn->error . "</p>";
          }
          ?>
          <?php foreach ($damLevels as $name => $level): ?>
              <h3 class="damnames"><?= htmlspecialchars($name) ?></h3>
              <div class="dam-bar-container">
                  <div class="dam-bar-fill" style="width: <?= htmlspecialchars($level) ?>%;">
                      <?php if ($level >= 15): ?>
                          <span class="dam-text-inside"><?= htmlspecialchars($level) ?>%</span>
                      <?php endif; ?>
                  </div>
                  <?php if ($level < 15): ?>
                      <span class="dam-text-outside"><?= htmlspecialchars($level) ?>%</span>
                  <?php endif; ?>
              </div>
          <?php endforeach; ?>
        </div>

        <!-- EVENTS INFO -->
        <div id="eventsInfo" class="infocard hover-orange">
          <a href="public_notices.php" style="text-decoration:none; color:inherit;">
            <h2>Events</h2>
            <br>
            <br>
            <?php
                $eventinfo = $conn->query("SELECT title, event_date FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3");
                if ($eventinfo && $eventinfo->num_rows > 0) {
                    while ($row = $eventinfo->fetch_assoc()) {
                        echo "<p><strong>" . htmlspecialchars($row['title']) . "</strong> - " . date("F j, Y", strtotime($row['event_date'])) . "</p>";
                    }
                } else {
                    echo "<p>No upcoming events at this time.</p>";
                }
            ?>
          </a>
        </div>

        <!-- TOWN NOTICES (Spans full width below) -->
        <div id="townNotices" class="infocard full-width hover-orange">
          <a href="public_notices.php" style="text-decoration:none; color:inherit;">
            <h2>Town Notices</h2>
            <?php
            $result = $conn->query("SELECT content FROM notices WHERE notif_type = 'general' ORDER BY created_at DESC LIMIT 1");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<p>" . htmlspecialchars($row['content']) . "</p>";
            } else {
                echo "<p>No new notices at this time.</p>";
            }
            ?>
          </a>
        </div>
      </div>
    </section>

    <!-- INFORMATICS SECTION -->
    <section id="informatics">
      <div id="currentissues" class="infomaticsection">
        <h2>Longterm Issues in Makhanda</h2>
        <?php $issue = $conn->query("SELECT content, is_featured from current_issues ORDER BY created_at DESC LIMIT 1");
                if ($issue && $issue->num_rows >0){
                  $row = $issue->fetch_assoc();
                  if ($row['is_featured'] == 1)
                  echo "<p>" . htmlspecialchars($row['content']) . "</p>";
                } else{
                   echo "<p>There are no long-term issues to be reported</p> .";
                }
        ?>
        <a href="public_notices.php#ci">Read more -></a>
      </div>

      <div id="comein" class="infomaticsection">
        <h2>Where You Come In</h2>
        <!-- Volunteer Form -->
      
        <form method = "POST" id="volunteerForm" data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>" style="margin-top: 1rem; display: flex; flex-direction: column; gap: 10px; max-width: 400px;">
            <p style="margin-bottom: 0.5rem; font-weight: bold; color: #0E2841;">We would appreciate any assistance from you with 
               different initiatives. Please select options to volunteer for should you wish to be added to a mailing list:</p>
            
            <!-- Checklist Options -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="cursor: pointer;"><input type="checkbox" name="volunteerOptions[]" value="clean_up"> Clean up</label>
                <label style="cursor: pointer;"><input type="checkbox" name="volunteerOptions[]" value="neighbourhood_watch"> Neighbourhood watch</label>
                <label style="cursor: pointer;"><input type="checkbox" name="volunteerOptions[]" value="soup_kitchens"> Soup kitchens</label>
                <label style="cursor: pointer;"><input type="checkbox" name="volunteerOptions[]" value="disaster_management"> Disaster management</label>
                <label style="cursor: pointer;"><input type="checkbox" name="volunteerOptions[]" value="youth_mentor"> Youth mentor</label>
            </div>

            <?php if ($isLoggedIn): ?>
                  <button type="button" id="confirmVolunteerBtn" class="mkrpt" style="position:static; font-size:1rem; padding: 0.8rem; margin-top: 10px;">
                      Confirm sign up
                  </button>
              <?php else: ?>
                  <p class="signin-prompt">
                      Please <a href="signin.php" class="signin-here">sign in here</a> to volunteer.
                  </p>
              <?php endif; ?>
        </form>
        <p id="volunteerMessage" style="display:none; margin-top: 15px; font-weight: bold;"></p>
      </div>
    </section>

    


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
          <li><a href="index.html">Home</a></li>
          <li><a href="CommReports.php">Reports</a></li>
          <li><a href="notices.html">Notices</a></li>
          <li><a href="map.html">Map</a></li>
          <li><a href="about.html">About Us</a></li>
        </ul>
      </div>

      <!-- Connect Column -->
      <div class="footer-col">
        <h4>Connect</h4>
        <ul>
          <li><a href="#">Report Website Bugs</a></li>
          <li><a href="#">Volunteer</a></li>
          <li><a href="mailto:info@munite.co.za">info@munite.co.za</a></li>
          <li><a href="tel:+27000000000">+27 000000000</a></li>
        </ul>
      </div>

      <!-- Resources Column -->
      <div class="footer-col">
        <h4>Resources</h4>
        <ul>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Documentation</a></li>
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
</body>
</html>
