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

    /* Nothing usable found — fall back to city, since "surburb"
       is a required (NOT NULL) column in the database */
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

const faultTypeSelect = document.getElementById('fault-type');
const faultImageInput = document.getElementById('fault-image');
const submitBtn = document.querySelector('.submit-btn');
 
const locationAddressLabel = document.querySelector('label[for="location-address"]');
const faultTypeLabel = document.querySelector('label[for="fault-type"]');
const faultDescriptionLabel = document.querySelector('label[for="fault-description"]');
 
const locationAddressError = document.getElementById('location-address-error');
const faultTypeError = document.getElementById('fault-type-error');
const faultDescriptionError = document.getElementById('fault-description-error');
const faultImageError = document.getElementById('fault-image-error');
 
/* Small reusable helper — rather than repeating this same
   "toggle the invalid class + set/clear the message" logic
   separately inside all 4 validation functions below, we write
   it once here and call it from each of them. Takes the label
   element, the error-message element, whether the field is
   currently valid, and what message to show if it's not. */
 
function setFieldValidity(labelEl, errorEl, isValid, message) {
 
  if (isValid) {
    labelEl.classList.remove('invalid');
    errorEl.textContent = '';
  } else {
    labelEl.classList.add('invalid');
    errorEl.textContent = message;
  }
 
}
 

 
function validateLocationAddress() {
 
  const isValid = locationAddressInput.value.trim() !== '';
 
  setFieldValidity(
    locationAddressLabel,
    locationAddressError,
    isValid,
    'Please provide a location — click the map, use "Use My Location", or type an address.'
  );
 
  return isValid;
 
}
 
function validateFaultType() {
 
  const isValid = faultTypeSelect.value !== '';
 
  setFieldValidity(
    faultTypeLabel,
    faultTypeError,
    isValid,
    'Please select a fault type.'
  );
 
  return isValid;
 
}
 
function validateFaultDescription() {
 
  const isValid = faultDescriptionInput.value.trim() !== '';
 
  setFieldValidity(
    faultDescriptionLabel,
    faultDescriptionError,
    isValid,
    'Please describe the fault.'
  );
 
  return isValid;
 
}
 
function validateFaultImage() {
 
  
  if (faultImageInput.files.length === 0) {
    faultImageError.textContent = '';
    return true;
  }
 
  const file = faultImageInput.files[0];
 
  
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
 
  if (!allowedTypes.includes(file.type)) {
    faultImageError.textContent = 'Only .jpg, .jpeg, and .png files are allowed.';
    return false;
  }
 
  
  const maxSizeInBytes = 128 * 1024 * 1024;
 
  if (file.size > maxSizeInBytes) {
    faultImageError.textContent = 'Image must be smaller than 128MB.';
    return false;
  }
 
  /* Passed both checks */
  faultImageError.textContent = '';
  return true;
 
}
 

 
function updateFormValidity() {
 
  const locationValid = validateLocationAddress();
  const faultTypeValid = validateFaultType();
  const descriptionValid = validateFaultDescription();
  const imageValid = validateFaultImage();
 
  const formIsValid = locationValid && faultTypeValid && descriptionValid && imageValid;
 
  submitBtn.disabled = !formIsValid;
 
}
 

 
locationAddressInput.addEventListener('input', updateFormValidity);
faultTypeSelect.addEventListener('change', updateFormValidity);
faultDescriptionInput.addEventListener('input', updateFormValidity);
faultImageInput.addEventListener('change', updateFormValidity);


/* ====================== IMAGE UPLOAD PREVIEW ====================== */

const uploadIcon = document.querySelector('.upload-icon');
const uploadHint = document.querySelector('.upload-hint');
const uploadPreview = document.querySelector('.upload-preview');
const uploadFilename = document.querySelector('.upload-filename');
const removeImageBtn = document.querySelector('.remove-image-btn');
 

 
faultImageInput.addEventListener('change', function () {
 
  
  if (faultImageInput.files.length === 0) {
    return;
  }
 
  const file = faultImageInput.files[0];
 
  
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
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
 
  /* Swap back to the empty dropzone state */
  uploadPreview.hidden = true;
  uploadPreview.src = '';
  uploadFilename.hidden = true;
  removeImageBtn.hidden = true;
 
  uploadIcon.hidden = false;
  uploadHint.hidden = false;
 
  
  updateFormValidity();
 
});

const filterBtn = document.querySelector('.filter-btn');
const filterPanel = document.getElementById('filter-panel');
const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
const reportCards = document.querySelectorAll('.report-card');
 
/* ----- Opening/closing the panel ----- */
 
filterBtn.addEventListener('click', function () {
 
 
 
  const isCurrentlyHidden = filterPanel.hidden;
 
  filterPanel.hidden = !isCurrentlyHidden;
  filterBtn.setAttribute('aria-expanded', String(isCurrentlyHidden));
 
});
 

document.addEventListener('click', function (event) {
 
  
  const clickedInsideButton = filterBtn.contains(event.target);
  const clickedInsidePanel = filterPanel.contains(event.target);
 
  if (!clickedInsideButton && !clickedInsidePanel && !filterPanel.hidden) {
    filterPanel.hidden = true;
    filterBtn.setAttribute('aria-expanded', 'false');
  }
 
});
 
/* ----- Actually filtering the cards ----- */
 
function applyFilter() {
 
  
 
  const checkedStatuses = Array.from(filterCheckboxes)
    .filter(function (checkbox) { return checkbox.checked; })
    .map(function (checkbox) { return checkbox.value; });
 
  
 
  reportCards.forEach(function (card) {
 
    const cardStatus = card.dataset.status; // reads the data-status attribute we set back when building the cards
 
    if (checkedStatuses.includes(cardStatus)) {
      card.hidden = false;
    } else {
      card.hidden = true;
    }
 
  });
 
}
 
/* Re-run the filter every time ANY checkbox changes state */
 
filterCheckboxes.forEach(function (checkbox) {
  checkbox.addEventListener('change', applyFilter);
});