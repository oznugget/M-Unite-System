<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['username']);
$firstname  = $isLoggedIn ? htmlspecialchars($_SESSION['firstname']) : '';

if (!$isLoggedIn) {
    header('Location: signin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang = "en">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width = device-width, initial-scale = 1.0">
        <title>Reports</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="header_footer.css">
        <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@400;700&family=TikTok+Sans:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

        
        <link rel = "stylesheet" href = "ReportStyle.css">
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>

    <body>
        <!-- HEADER AND NAVIGATION -->
        <header class="site-header">

      <div class="logo-box">
      <a href="home.php" class="logo-link">
      <img src="images/logo_1.png" alt="M-Unite Logo" class="logo-image">
      </a>
    </div>

    <nav class="navbar">
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

        <!--MAIN CONTENT -->
        <main class="reports-page">

            <section class = "reports-section">
                <div class = "map-wrapper">
                <!--------putting the map here -->
                <div id = "report-map" class = "map-container" role = "application" aria-label = "Map of Makhanda for report location"></div>

                <p class = "map-hint">Click the map to drop a pin at the fault location</p>

                </div>

                <!--------report form goes here -->
                <form class = "report-form" novalidate action = "process-report.php" method = "POST" enctype="multipart/form-data">    <!---turning off the default browser pop ups -->
                    <h2>Report</h2>

                    <div class = "form-field">
                        <label for = "location-address">Location address<span class = "required">*</span></label>

                        <div class = "location-input-row">
                            <input type = "text" id = "location-address" name = "location-address" required>
                            <button type = "button" class = "use-my-location-btn">Use My Location</button>
                            
                        </div>
                        <span class="field-error" id="location-address-error"></span>

                        
                        <input type = "hidden" id = "place-name" name = "place-name">
                        <input type = "hidden" id = "house-number" name = "house-number">
                        <input type = "hidden" id = "road-name" name = "road-name">
                        <input type = "hidden" id = "ward-number" name = "ward-number">
                        <input type="hidden" id="suburb" name="suburb">

                    </div>

                    <div class = "form-field">
                        <label for = "fault-type">Fault Type<span class="required">*</span></label>
                        <select id = "fault-type" name= "fault-type" required>
                            <option value="" selected disabled>Select Fault Type</option>
                            <option value="Electricity">Electricity</option>
                            <option value="Water">Water</option>
                            <option value="Roads">Roads</option>
                            <option value="Animals">Animals</option>
                            <option value="Sanitation">Sanitation</option>
                            <option value="Vandalism">Vandalism</option>
                            <option value="Waste Management">Waste Management</option>
                            <option value="Environmental Incidents">Environmental Incidents</option>

                        </select>
                        <span class="field-error" id="fault-type-error"></span>
                    </div>

                    <div class = "form-field">
                        <label for = "fault-description">Fault Desription<span class = "required">*</span></label>
                        <textarea id = "fault-description" name = "fault-description" maxlength="1300" required></textarea>
                        <span class = "char-count"><span class = "char-count-current">0</span>/1300 Characters</span>
                         <span class="field-error" id="fault-description-error"></span>
                    </div>

                    <div class = "form-field">
                        <label for = "fault-image">Upload Fault Image</label>
                        <div class = "upload-dropzone">
                            <input type = "file" id = "fault-image" name = "fault-image" accept = ".jpg,.jpeg,.png,.webp">
                            <span class = "upload-icon" aria-hidden="true"></span>
                            <span class = "upload-hint">Click or drag an image here</span>

                            <img class="upload-preview" src="" alt="Preview of the uploaded fault image" hidden>
                            <span class="upload-filename" hidden></span>
                            <button type="button" class="remove-image-btn" aria-label="Remove uploaded image" hidden>&times;</button>
                        
                        </div>

                        <span class="field-error" id="fault-image-error"></span>
                    </div>

                    <button type = "submit" class = "submit-btn" disabled>Submit Fault</button>
                    
                </form>

                <div id="pending-report-container"></div>
                
            </section>
            
            <section class = "past-reports-link">
                <a href = "MyReports.php" class = "submit-btn">View My Reports</a>
            </section>

            
        </main>
            <!------AI CHATBOT ICON -->

        <div id="submission-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="submission-modal-message" hidden>
            <div class="modal-box">
                <p id="submission-modal-message" class="modal-message"></p>
                <div class="modal-actions">
                    <button type="button" id="modal-ok-btn" class="modal-ok-btn">OK</button>
                    <a href="MyReports.php" class="modal-view-reports-btn">View My Reports</a>
                </div>
            </div>
        </div>


        <div class="ai-chat-widget">
            <img src = "makbot_chat.jpeg" alt = "matbok, M-Unite chatbot" class = "ai-chat-mascot">
            <p class="ai-chat-prompt">Want to chat with Makbot, our AI assistant?</p>
 
            <button type="button" class="ai-chat-icon" aria-haspopup="dialog" aria-expanded="false" aria-controls="ai-chat-popup" aria-label="Chat with Makbot, our AI assistant">
            ?
            </button>
 
            <div id="ai-chat-popup" class="ai-chat-popup" role="dialog" aria-label="Makbot AI assistant chat" hidden>
                <div class="ai-chat-popup-header">
                <h2>Makbot</h2>
            <button type="button" class="ai-chat-close-btn" aria-label="Close chat">&times;</button>
            </div>
 
            <div class="ai-chat-messages">
                <!-- chat messages will be appended here by JS -->
            </div>
 
            <form class="ai-chat-input-row">
                <label for="ai-chat-input" class="visually-hidden">Type your message</label>
                <input type="text" id="ai-chat-input" name="ai-chat-input" placeholder="Ask a question...">
                <button type="submit">Send</button>
            </form>
        </div>
  </div>

        <!-------FOOTER -->

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
        
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src = "ReportScript.js"></script>
        
    </body