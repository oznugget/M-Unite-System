<?php
session_start();

$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    
    <script src="main.js" defer></script>
    <script src="homejs.js" defer></script>
    <link rel="stylesheet" href="homecss.css">
    <link rel="stylesheet" href="header_footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
</head>


<body>

    
    <div id="loading-screen">
    <img src="loading_run.gif" alt="Loading..." class="loader-media">
    </div>

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

      <div class="header-right" id="header-right">
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
            <p>Welcome <?php echo $firstname . ' ' . '<img src="images/makbot_wave.png" alt="Wave" class="wave-icon">' . '<br>'; ?>  </p>
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

        <a href = "CommReports.php"><button class="mkrpt">Make Report</button></a>
    
        <div class="dots" id="dotsContainer"></div>

      </div>

    </section>
    
       </section>


    <section id="about" class="step-card">
      <div class="about-content">
        <img src="images/timeline4.png" alt="M-Unite Logo" class="logo-image-about"  >
      </div>
    </section>

    <section id = "localinfo" class = "localinfo">
      <h2 class = "section-title"> Local Information </h2>

      <div class="localinfo-row">

        <div id = "dams" class = "infocard">
          <h2> Dam Levels </h2>
            <h3 class = "damnames">Howieson's Poort Dam </h3>
            <!---fetch number from municipal officer input and display horizontal bar percentage-->
            <h3 class = "damnames">Settlers Dam </h3>
            <!---fetch number from municipal officer input and display horizontal bar percentage-->
            <h3 class = "damnames">Jamieson and Milner Dams </h3>
            <!---fetch number from municipal officer input and display horizontal bar percentage-->
            <h3 class = "damnames">Glen Melville Dam </h3>
            <!---fetch number from municipal officer input and display horizontal bar percentage-->
        </div>

        <div class="localinfo-col">

          <div id = "townnotices" class = "infocard">
            <h2> Town Notices </h2>
            <a href = "notifications.html"></a>
            <!--- fetch top notice from municipal officer most recent community wide notices as a box
            and render the first 3 lines from it. community wide is visible to guest and all other users-->
          </div>

          <div id = "events" class = "infocard">
            <h2> Events </h2>
             <!--- fetch top event from municipal officer most recent events post as a box
            and render the first 3 lines from it. events are visible to guest and all other users-->
          </div>

        </div>

      </div>
     
    </section>





 <section id="informatics">

  <div id="currentissues" class="infomaticsection">
    <h2>Current Issues</h2>
    <p>Makhanda is currently facing a water crisis. Makhanda is currently using 18 megalitres a
      day of water each day – about 180 litres per person. The crippling drought has nearly emptied
      Settlers' Dam – which supplies about half of that – and it is unlikely to recover until/unless
      we receive significant rainfall.</p>
    <a href="notifications.html">Read more -></a>
  </div>

  <div id="comein" class="infomaticsection">
    <h2>Where You Come In</h2>
    <p>Every drop counts. Use 50l a day. Keep taps closed. Take short showers.
      Flush using grey water. A distribution schedule is being worked on that will get water tankers
      delivering drinking water to different wards across the City. There will also be collection points,
      replenished daily, where residents will be able to collect their daily allocation of water.
    </p>
    <a href="notifications.html">Read more -></a>
  </div>

</section>

<div>
  <p></p>
</div>



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
