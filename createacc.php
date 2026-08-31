<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | M-Unite</title>

       <link rel="stylesheet" href="createacccss.css">
       <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
       <script src="createaccjs.js" defer></script>
       <link rel="preconnect" href="https://fonts.googleapis.com">
       <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=TikTok+Sans:opsz,wght@12..36,300..900&display=swap" rel="stylesheet">
       <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>  <!--intl-tel-input Core Library JS for contact flag -->
    
</head>
<body>


  <div class="registration">
    <h2>Create Account</h2>
    <p class="form-subtitle">Fields turn <span class="txt-green">green</span> when complete and <span class="txt-red">red</span> if invalid.</p>

    <form class="reg-form" action="registration.php" method="POST">

      <div class="form-group">
        <label for="firstname">First Name </label>
        <input type="text" id="firstname" name="firstname" maxlength="20" required />
      </div>

      <div class="form-group">
        <label for="surname">Surname </label>
        <input type="text" id="surname" name="surname" maxlength="30" required/>
      </div>

      
      <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" maxlength="255" required />

      <?php if (isset($_GET['error']) && $_GET['error'] === 'email_domain'): ?>
        <span style="color: #d9534f; font-size: 0.82rem; margin-top: 4px; font-weight: 600; display: block;">
          Municipal roles require a different email address.
        </span>
      <?php endif; ?>
    </div>

      
      <div class="form-group">
        <label for="contact">Contact   </label>
        <input type="tel" id="contact" name="contact" pattern="[1-9][0-9]{1}\s?[0-9]{3}\s?[0-9]{4}" required />
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

      <div class="form-group autocomplete-wrapper" id="address-container">
        <label for="addr">Physical Address (Makhanda)</label>
        <input type="text" id="addr" name="addr" placeholder="Type street or area in Makhanda..." autocomplete="off" required />
        <ul id="suggestions" class="suggestions-list"></ul>
      </div>

      <!-- Hidden address components extracted automatically -->
      <input type="hidden" id="lat" name="lat">
      <input type="hidden" id="lon" name="lon">
      <input type="hidden" id="street_number" name="street_number">
      <input type="hidden" id="street_name" name="street_name">
      <input type="hidden" id="suburb" name="suburb">



    <!-- optional division field for Municipal Officers -->
      <div class="form-group" id="division-container" style="display: none;">
      <label for="division">Division <span style="font-weight: normal; font-size: 12px; color: #666;"></span></label>
      <select id="division" name="division">
        <option value="">Select Division</option>
        <option value="Electricity">Electricity</option>
        <option value="Water & Sanitation">Water & Sanitation</option>
        <option value="Roads">Roads</option>
        <option value="Animals">Animals</option>
        <option value="Transport">Transport</option>
        <option value="Vandalism">Vandalism</option>
        <option value="Waste Management">Waste Management</option>
      </select>
    </div>


      <div class="form-group">
        <label for="pword">Password  </label>
        <input type="password" id="pword" name="pword" maxlength="20" required />
      </div>

      <div class="form-buttons">
        <button type="reset" id="clr">Clear Form</button>
        <button type="submit" id="sub">Create Account</button>
      </div>

    </form>
  </div>



</body>
</html>