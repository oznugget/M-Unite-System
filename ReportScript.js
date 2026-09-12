/*========= FAULT DESCRIPTION CHARACTER COUNTER =========*/

const faultDescriptionInput = document.getElementById('fault-description');
const charCountDisplay = document.querySelector('.char-count-current');

faultDescriptionInput.addEventListener('input', function () {

    const currentLength = faultDescriptionInput.value.length;

    charCountDisplay.textContent = currentLength;
    
});




/*========= INITIALIZING THE MAP =========*/

const map = L.map('report-map').setView([-33.3041, 26.5328], 15); //Setting the initial coordinates to Makhanda

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {

    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',

    maxZoom: 19,

}).addTo(map);


/* ========== DROPPING A PIN ON THE MAP =========*/

let reportmarker = null;

map.on('click', function (e) {

    const clickedLocation = e.latlng;

    if(reportmarker == null) {

        reportmarker = L.marker(clickedLocation).addTo(map);
    } else {

        reportmarker.setLatLng(clickedLocation);
    }

    fillAddressFromCoordinates(clickedLocation.lat, clickedLocation.lng);
}); 



/* ========== COORDINATES TO ADDRESS =========*/

const locationAddressInput = document.getElementById('location-address');

async function fillAddressFromCoordinates(lat, lng) {

    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if(data && data.display_name) {

            locationAddressInput.value = data.display_name;

            fillStructuredLocationDetails(data);
    } else{
        locationAddressInput.value = 'Address not found, please type it manually';
    }

        } catch (error) {
        console.error('Error fetching address:', error);
        locationAddressInput.value = 'Error fetching address, please type it manually';
    }
    markTouched('location');
    updateFormValidity();
}


/* ========== USE MY LOCATION BUTTON =========*/

const useMyLocationBtn = document.querySelector('.use-my-location-btn');

useMyLocationBtn.addEventListener('click', function () {

    
    if (!navigator.geolocation) {
        locationAddressInput.value = 'Geolocation is not supported by your browser';
        return;
    }


    navigator.geolocation.getCurrentPosition(

        /* ----- SUCCESS ----- */
        function (position) {

            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;

            
            map.setView([userLat, userLng], 17);

            
            if (reportmarker == null) {
                reportmarker = L.marker([userLat, userLng]).addTo(map);
            } else {
                reportmarker.setLatLng([userLat, userLng]);
            }

            
            fillAddressFromCoordinates(userLat, userLng);

        },

        /* ----- ERROR ----- */
        function (error) {

           
            console.error('Geolocation error:', error);
            locationAddressInput.value = 'Could not get your location — please click the map or type it manually';

        }

    );

});
/*===================== EXTRACTING STRUCTURED LOCATION DETAILS ====================*/
const placeNameField = document.getElementById('place-name');
const houseNumberField = document.getElementById('house-number');
const roadNameField = document.getElementById('road-name');
const wardNumberField = document.getElementById('ward-number');
const suburbField = document.getElementById('suburb');

const POSSIBLE_WARD_FIELDS = ['neighbourhood', 'suburb', 'city_district', 'quater'];

function extraWardNumber(text){
    
    if(!text){
        return null;

    }

    const match = text.match(/Ward\s*(\d+)/i);

    return match ? match[1] : null
}

function extractSuburb(addr) {

    for (const fieldName of POSSIBLE_WARD_FIELDS) {

        const text = addr[fieldName];

        if (text && extraWardNumber(text) === null) {
            return text;
        }

    }


    return addr.city || null;

}


function fillStructuredLocationDetails(data){

    const addr = data.address || {};


    placeNameField.value = data.name || ' ';

    houseNumberField.value = addr.house_number || ' ';

    roadNameField.value = addr.road || ' ';

    suburbField.value = extractSuburb(addr) || ' ';
    
    let wardNumber = null;

    for (const fieldName of POSSIBLE_WARD_FIELDS){

        wardNumber = extraWardNumber(addr[fieldName]);

        if(wardNumber !== null){
            break
        }
    }

    wardNumberField.value = wardNumber || ' ';
}

/* ====================== FORM VALIDATION ====================== */

/* ====================== FORM VALIDATION ====================== */

const faultTypeSelect = document.getElementById('fault-type');
const faultImageInput = document.getElementById('fault-image');
const submitBtn = document.querySelector('.submit-btn');

const locationAddressError = document.getElementById('location-address-error');
const faultTypeError = document.getElementById('fault-type-error');
const faultDescriptionError = document.getElementById('fault-description-error');
const faultImageError = document.getElementById('fault-image-error');

/* Tracks which fields the user has actually "reached" so far.
   A field only gets its red error shown once it's touched —
   this stops every field flashing red the moment ANY one
   field changes. */
const touchedFields = {
  location: false,
  faultType: false,
  description: false,
  image: false,
};

const fieldOrder = ['location', 'faultType', 'description', 'image'];

function markTouched(fieldKey) {
  touchedFields[fieldKey] = true;
}

/* Called when a field gains focus — marks every field BEFORE
   it in fieldOrder as touched too. This is what makes a
   skipped field light up: if you jump straight from Location
   to Description, focusing Description marks Fault Type
   (the one you skipped) as touched. */
function markPrecedingTouched(fieldKey) {
  const targetIndex = fieldOrder.indexOf(fieldKey);
  for (let i = 0; i < targetIndex; i++) {
    touchedFields[fieldOrder[i]] = true;
  }
}

function setFieldValidity(errorEl, isValid, message, touched) {

  if (isValid || !touched) {
    errorEl.textContent = '';
  } else {
    errorEl.textContent = message;
  }

}

function validateLocationAddress(silent) {

  const isValid = locationAddressInput.value.trim() !== '';

  if (!silent) {
    setFieldValidity(
      locationAddressError,
      isValid,
      'Please provide a location — click the map, use "Use My Location", or type an address.',
      touchedFields.location
    );
  }

  return isValid;

}

function validateFaultType(silent) {

  const isValid = faultTypeSelect.value !== '';

  if (!silent) {
    setFieldValidity(
      faultTypeError,
      isValid,
      'Please select a fault type.',
      touchedFields.faultType
    );
  }

  return isValid;

}

function validateFaultDescription(silent) {

  const isValid = faultDescriptionInput.value.trim() !== '';

  if (!silent) {
    setFieldValidity(
      faultDescriptionError,
      isValid,
      'Please describe the fault.',
      touchedFields.description
    );
  }

  return isValid;

}

function validateFaultImage(silent) {

  if (faultImageInput.files.length === 0) {
    if (!silent) faultImageError.textContent = '';
    return true;
  }

  const file = faultImageInput.files[0];

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

  if (!allowedTypes.includes(file.type)) {
    if (!silent) faultImageError.textContent = 'Only .jpg, .jpeg, .png, and .webp files are allowed.';
    return false;
  }

  const maxSizeInBytes = 128 * 1024 * 1024;

  if (file.size > maxSizeInBytes) {
    if (!silent) faultImageError.textContent = 'Image must be smaller than 128MB.';
    return false;
  }

  if (!silent) faultImageError.textContent = '';
  return true;

}

function updateFormValidity(silent) {

  const locationValid = validateLocationAddress(silent);
  const faultTypeValid = validateFaultType(silent);
  const descriptionValid = validateFaultDescription(silent);
  const imageValid = validateFaultImage(silent);

  const formIsValid = locationValid && faultTypeValid && descriptionValid && imageValid;

  submitBtn.disabled = !formIsValid;

}

locationAddressInput.addEventListener('input', updateFormValidity);
faultTypeSelect.addEventListener('change', updateFormValidity);
faultDescriptionInput.addEventListener('input', updateFormValidity);
faultImageInput.addEventListener('change', updateFormValidity);

locationAddressInput.addEventListener('focus', function () { markPrecedingTouched('location'); });
faultTypeSelect.addEventListener('focus', function () { markPrecedingTouched('faultType'); });
faultDescriptionInput.addEventListener('focus', function () { markPrecedingTouched('description'); });
faultImageInput.addEventListener('focus', function () { markPrecedingTouched('image'); });

locationAddressInput.addEventListener('blur', function () { markTouched('location'); updateFormValidity(); });
faultTypeSelect.addEventListener('blur', function () { markTouched('faultType'); updateFormValidity(); });
faultDescriptionInput.addEventListener('blur', function () { markTouched('description'); updateFormValidity(); });
faultImageInput.addEventListener('blur', function () { markTouched('image'); updateFormValidity(); });


/* ====================== IMAGE UPLOAD PREVIEW ====================== */

const uploadIcon = document.querySelector('.upload-icon');
const uploadHint = document.querySelector('.upload-hint');
const uploadPreview = document.querySelector('.upload-preview');
const uploadFilename = document.querySelector('.upload-filename');
const removeImageBtn = document.querySelector('.remove-image-btn');

function resetImageUploadUI() {
  uploadPreview.hidden = true;
  uploadPreview.src = '';
  uploadFilename.hidden = true;
  removeImageBtn.hidden = true;
  uploadIcon.hidden = false;
  uploadHint.hidden = false;
}
 

 
faultImageInput.addEventListener('change', function () {
 
  
  if (faultImageInput.files.length === 0) {
    return;
  }
 
  const file = faultImageInput.files[0];
 
  
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    return;
  }
 
  
  const reader = new FileReader();
 
  
  reader.onload = function () {
 
    
    uploadPreview.src = reader.result;
 
    
    uploadIcon.hidden = true;
    uploadHint.hidden = true;
 
    uploadPreview.hidden = false;
    uploadFilename.hidden = false;
    uploadFilename.textContent = file.name;
    removeImageBtn.hidden = false;
 
  };
 
  
  reader.readAsDataURL(file);
 
});
 

 
removeImageBtn.addEventListener('click', function (event) {
 
  event.stopPropagation();
 
  faultImageInput.value = '';
  resetImageUploadUI();
 
  updateFormValidity();
 
});
 /*============================= PENDING REPORT TIMER ======================*/

  const reportForm = document.querySelector('.report-form');
const pendingContainer = document.getElementById('pending-report-container');
const submissionModal = document.getElementById('submission-modal');
const submissionModalMessage = document.getElementById('submission-modal-message');
const modalOkBtn = document.getElementById('modal-ok-btn');

modalOkBtn.addEventListener('click', function () {
  submissionModal.hidden = true;
});
 

let isCountdownActive = false;
 

let countdownIntervalId = null;
 
 
reportForm.addEventListener('submit', function (event) {
 
  
  event.preventDefault();
 
  
  const formData = new FormData(reportForm);
 
  
  const displayFaultType = faultTypeSelect.value;
  const displayDescription = faultDescriptionInput.value;
  const displayLocation = locationAddressInput.value;
 
  
  lockReportForm();
 
  
  showPendingCard(displayFaultType, displayDescription, displayLocation);
 
  
  startCountdown(formData);
 
});
 
 

function lockReportForm() {
 
  const allFields = reportForm.querySelectorAll('input, select, textarea, button');
 
  allFields.forEach(function (field) {
    field.disabled = true;
  });
 
  reportForm.classList.add('is-locked');
 
}
 

function unlockReportForm() {
 
  const allFields = reportForm.querySelectorAll('input, select, textarea, button');
 
  allFields.forEach(function (field) {
    field.disabled = false;
  });
 
  reportForm.classList.remove('is-locked');
 
  updateFormValidity(true); // silent — just recheck the button, don't flash errors on a freshly reset form
 
}
 
 

function showPendingCard(faultType, description, location) {
 
  
  const shortDescription = description.length > 100
    ? description.substring(0, 100) + '...'
    : description;
 
  pendingContainer.innerHTML = `
    <div class="report-card pending-card">
      <h3 class="report-card-title">${faultType} Report</h3>
      <p class="pending-message">
        This report will be submitted in <span class="countdown-seconds">15</span> seconds.
        You can still cancel it until then - after that, it cannot be undone.
      </p>
      <p class="report-description"><strong>Description:</strong> ${shortDescription}</p>
      <p class="report-location"><strong>Location:</strong> ${location}</p>
      <button type="button" class="cancel-pending-btn">Cancel Report</button>
    </div>
  `;
 
  
  const cancelBtn = pendingContainer.querySelector('.cancel-pending-btn');
  cancelBtn.addEventListener('click', cancelPendingReport);
 
}
 
 

function startCountdown(formData) {
 
  isCountdownActive = true;
 
  let secondsRemaining = 15;
 
  const countdownDisplay = pendingContainer.querySelector('.countdown-seconds');
 
  
  countdownIntervalId = setInterval(function () {
 
    secondsRemaining = secondsRemaining - 1;
    countdownDisplay.textContent = secondsRemaining;
 
    if (secondsRemaining <= 0) {
 
      
      clearInterval(countdownIntervalId);
      isCountdownActive = false;
 
      submitReportForReal(formData);
 
    }
 
  }, 1000);
 
}
 
 
/* Called when the user clicks Cancel during the countdown */
function cancelPendingReport() {
 
  clearInterval(countdownIntervalId);
  isCountdownActive = false;
 
 
  pendingContainer.innerHTML = '';
 
  unlockReportForm();

  fetch('log_cancelled_report.php', { method: 'POST' }).catch(function (error) {
    console.error('Could not log the cancelled report:', error);
  });
 
}
 
 

async function submitReportForReal(formData) {
 
  try {
 
    const response = await fetch('process-report.php', {
      method: 'POST',
      body: formData
    });
 
    const result = await response.json();
 
   
    pendingContainer.innerHTML = '';
 
    if (result.success) {
 
      showModal('Report submitted successfully!', 'is-success');
 
      
      reportForm.reset();
      resetImageUploadUI();
      unlockReportForm();
 
    } else {
 
      
      showModal('Something went wrong: ' + result.message, 'is-error');
      unlockReportForm();
 
    }
 
  } catch (error) {
 
    /* A genuine network failure (server unreachable, etc.) */
    console.error('Submission error:', error);
    pendingContainer.innerHTML = '';
    showModal('Could not submit your report — please check your connection and try again.', 'is-error');
    unlockReportForm();
 
  }
 
}
 
 
/* Small shared helper for showing either modal "mood" */
function showModal(message, moodClass) {
 
  submissionModalMessage.textContent = message;
  submissionModalMessage.className = 'modal-message ' + moodClass;
  submissionModal.hidden = false;
 
}
 
 

 
window.addEventListener('beforeunload', function (event) {
 
  if (isCountdownActive) {
    event.preventDefault();
    event.returnValue = ''; // required for the warning to actually appear in Chrome
  }
 
});