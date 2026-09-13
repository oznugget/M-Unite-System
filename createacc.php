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
    <title>Create Account | M-Unite</title>

       <link rel="stylesheet" href="createacccss.css">
       <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
       <script src="createaccjs.js" defer></script>
       <link rel="stylesheet" href="header_footer.css">
       <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
       <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
       <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>  <!--intl-tel-input Core Library JS for contact flag -->
    
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
        <a href="home.php" class="nav-item">Home</a>
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
  const navMenu   = document.getElementById('nav-menu');

  if (hamburger && navMenu) {
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

    navMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 768) closeMenu();
    });

    document.addEventListener('click', (event) => {
      const clickedInsideMenu = navMenu.contains(event.target) || hamburger.contains(event.target);
      if (!clickedInsideMenu && navMenu.classList.contains('active')) {
        closeMenu();
      }
    });
  }
</script>



  <div class="registration">
    <h2>Create Account</h2>


        <form class="reg-form" id = "regForm" action="registration.php" method="POST">

      <?php if (isset($_GET['error']) && $_GET['error'] !== ''): ?>
        <div id="form-error-banner" class="form-error-banner">
          <?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>


      <div class="form-group">
        <label for="firstname">First Name </label>
        <input type="text" id="firstname" name="firstname" maxlength="20" required />
        <ul id="firstname-feedback" class="pwd-feedback"></ul>
      </div>

      <div class="form-group">
        <label for="surname">Surname </label>
        <input type="text" id="surname" name="surname" maxlength="30" required/>
        <ul id="surname-feedback" class="pwd-feedback"></ul>
      </div>

          
      <div class="form-group">
        <label for="urole">User Role  </label>
        <select id="urole" name="userrole" required>
          <option value="">Select role</option>
          <option value="1">Community Member</option>
          <option value="2">Ward Councillor</option>
          <option value="3">Municipal Officer</option>
          <option value="4">System Admin</option>
        </select>
      </div>

      
      <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" maxlength="255" required />
      <ul id="email-feedback" class="pwd-feedback"></ul>
    </div>

      
      <div class="form-group">
        <label for="contact">Contact   </label>
        <input type="tel" id="contact" name="contact" pattern="[1-9][0-9]{1}\s?[0-9]{3}\s?[0-9]{4}" required />
      </div>


      <div class="form-group autocomplete-wrapper" id="address-container">
        <label for="addr">Physical Address</label>
        <input type="text" id="addr" name="addr" placeholder="Type in street number + street name in Makhanda..." autocomplete="off" required />
        <ul id="suggestions" class="suggestions-list"></ul>
      </div>



            <!-- Hidden address components extracted automatically -->
      <input type="hidden" id="lat" name="lat">
      <input type="hidden" id="lon" name="lon">
      <input type="hidden" id="street_number" name="street_number">
      <input type="hidden" id="street_name" name="street_name">
      <input type="hidden" id="suburb" name="suburb">
      <input type="hidden" id="ward_id" name="ward_id" value="1">



    <!-- optional division field for Municipal Officers -->
      <div class="form-group" id="division-container" style="display: none;">
      <label for="division">Division <span style="font-weight: normal; font-size: 12px; color: #666;"></span></label>
      <select id="division" name="division">
        <option value="">Select Division</option>
        <option value="Electricity">Electricity</option>
        <option value="Water & Sanitation">Water</option>
        <option value="Roads">Roads</option>
        <option value="Animals">Animals</option>
        <option value="Sanitation">Sanitation</option>
        <option value="Vandalism">Vandalism</option>
        <option value="Waste Management">Waste Management</option>
        <option value="Environmental Incidents">Environmental Incidents</option>
      </select>
    </div>

      <!-- optional division field for Ward Councillors -->
      <div class="form-group" id="ward-container" style="display: none;">
      <label for="wcWard">Ward <span style="font-weight: normal; font-size: 12px; color: #666;"></span></label>
      <select id="wcWard" name="wardCouncillorward">
        <option value="">Select Ward</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
        <option value="13">13</option>
        <option value="14">14</option>
      </select>
    </div>

<div class="form-group">
  <label for="myInput">Password</label>
  <div class="password-wrapper">
    <input type="password" id="myInput" name = "pword" required oncopy="return false;" onpaste="return false;" oncut="return false;">
    <button type="button" class="toggle-btn" onclick="togglePassword('myInput', 'eyeIcon')" aria-label="Toggle password visibility">
      <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0E2841" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
        <line x1="1" y1="1" x2="23" y2="23"></line>
      </svg>
    </button>
  </div>
  <ul id="pwd-feedback" class="pwd-feedback"></ul>
</div>

<div class="form-group">
  <label for="confirmMyInput">Confirm Password</label>
  <div class="password-wrapper">
    <input type="password" id="confirmMyInput" name = "confirm_pwd" required oncopy="return false;" onpaste="return false;" oncut="return false;">
    <button type="button" class="toggle-btn" onclick="togglePassword('confirmMyInput', 'eyeIcon2')" aria-label="Toggle password visibility">
      <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0E2841" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
        <line x1="1" y1="1" x2="23" y2="23"></line>
      </svg>
    </button>
  </div>
   <ul id="confirm-pwd-feedback" class="confirm-pwd-feedback"></ul>
</div>

      <div class="form-buttons">
        <button type="reset" id="clr">Clear Form</button>
        <button type="submit" id="sub" disabled>Create Account</button>
      </div>

    </form>
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
          <li><a href="reports.html">Reports</a></li>
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


</body>
</html>