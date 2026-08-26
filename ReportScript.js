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

const POSSIBLE_WARD_FIELDS = ['neighbourhood', 'suburb', 'city_district', 'quater'];

function extraWardNumber(text){
    
    if(!text){
        return null;

    }

    const match = text.match(/Ward\s*(\d+)/i);

    return match ? match[1] : null
}


function fillStructuredLocationDetails(data){

    const addr = data.address || {};


    placeNameField.value = data.name || ' ';

    houseNumberField.value = addr.house_number || ' ';

    roadNameField.value = addr.road || ' ';
    
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
 
/* ----- Individual field checks ----- */
/* Each of these checks ONE field, updates that field's own
   red-label/error-message state via the helper above, and
   returns true/false — so the "is the whole form okay"
   function further down can just ask each one a yes/no
   question without repeating any of this logic itself. */
 
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
 
  /* Per the SDS, the image upload is NOT a required field — so
     no file selected at all still counts as valid. We only
     need to check anything once a file HAS been chosen. */
  if (faultImageInput.files.length === 0) {
    faultImageError.textContent = '';
    return true;
  }
 
  const file = faultImageInput.files[0];
 
  /* Checking against a list of allowed types, per the SDS:
     "only .jpg, .jpeg, and .png types will be allowed" */
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
 
  if (!allowedTypes.includes(file.type)) {
    faultImageError.textContent = 'Only .jpg, .jpeg, and .png files are allowed.';
    return false;
  }
 
  /* file.size is in bytes. 128MB, converted to bytes:
     128 * 1024 * 1024 = 134,217,728 bytes.
     Per the SDS: "a maximum file size of 128MB will be allowed" */
  const maxSizeInBytes = 128 * 1024 * 1024;
 
  if (file.size > maxSizeInBytes) {
    faultImageError.textContent = 'Image must be smaller than 128MB.';
    return false;
  }
 
  /* Passed both checks */
  faultImageError.textContent = '';
  return true;
 
}
 
/* ----- The overall form check ----- */
/* Runs all 4 individual checks (each one updating its own
   field's visual state as a side effect), and only enables the
   Submit button if every single one came back valid. Using
   the & operator here rather than && intentionally — && would
   "short-circuit" and skip running the later checks the moment
   an earlier one fails, meaning fields further down wouldn't
   get their red/valid state updated. We want every field
   checked and visually updated every time, not just the first
   one that happens to fail. */
 
function updateFormValidity() {
 
  const locationValid = validateLocationAddress();
  const faultTypeValid = validateFaultType();
  const descriptionValid = validateFaultDescription();
  const imageValid = validateFaultImage();
 
  const formIsValid = locationValid && faultTypeValid && descriptionValid && imageValid;
 
  submitBtn.disabled = !formIsValid;
 
}
 
/* ----- Wiring it all up ----- */
/* Re-check the whole form's validity whenever any relevant
   field changes. 'input' fires on every keystroke (location
   address, description); 'change' fires when a dropdown
   selection or file selection changes. */
 
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
 
/* Runs every time the user picks a file (or picks a different
   one, replacing an earlier choice) */
 
faultImageInput.addEventListener('change', function () {
 
  /* If the user opened the file picker and then clicked
     Cancel, .files will be empty — nothing was actually
     chosen, so there's nothing to preview */
  if (faultImageInput.files.length === 0) {
    return;
  }
 
  const file = faultImageInput.files[0];
 
  /* We only want to show a preview for genuinely valid image
     files — reuse the SAME check from Piece 6's validateFaultImage
     rather than writing a second, separate type-check here. If
     it's invalid (wrong type/too big), validateFaultImage()
     already wrote an error message for the user — we just skip
     showing a broken/misleading preview on top of that. */
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!allowedTypes.includes(file.type)) {
    return;
  }
 
  /* FileReader is a built-in browser tool for reading the
     actual contents of a file the user selected on their own
     device — necessary here because we want to display the
     image itself, not just its filename. */
  const reader = new FileReader();
 
  /* reader.onload runs once the file has FINISHED being read
     into memory — reading a file takes a small amount of time,
     so this can't run synchronously right away */
  reader.onload = function () {
 
    /* reader.result now contains the image data encoded as a
       "data URL" — a special text format that can be used
       directly as an <img>'s src, without ever uploading the
       file anywhere. This is 100% local to the browser. */
    uploadPreview.src = reader.result;
 
    /* Swap the dropzone from its empty state to its
       "file selected" state */
    uploadIcon.hidden = true;
    uploadHint.hidden = true;
 
    uploadPreview.hidden = false;
    uploadFilename.hidden = false;
    uploadFilename.textContent = file.name;
    removeImageBtn.hidden = false;
 
  };
 
  /* This is what actually kicks off the (asynchronous) reading
     process — reader.onload above will run once it completes */
  reader.readAsDataURL(file);
 
});
 
/* Handles the × button — clears the selection and returns the
   dropzone back to its original empty state */
 
removeImageBtn.addEventListener('click', function (event) {
 
  /* Without this, clicking the × (which sits ON TOP of the
     invisible file input, per our z-index CSS fix) could still
     let the click "fall through" to the file input underneath
     and reopen the file picker right after clearing it. This
     stops the click from doing anything beyond this function. */
  event.stopPropagation();
 
  /* Resetting the file input's value is how you clear a
     previously selected file in JavaScript — there's no
     simpler "remove" method for file inputs */
  faultImageInput.value = '';
 
  /* Swap back to the empty dropzone state */
  uploadPreview.hidden = true;
  uploadPreview.src = '';
  uploadFilename.hidden = true;
  removeImageBtn.hidden = true;
 
  uploadIcon.hidden = false;
  uploadHint.hidden = false;
 
  /* The form's overall validity needs re-checking too — removing
     an image doesn't make the form invalid (image was never
     required), but it's good practice to re-run this after any
     change to this field, matching how every other field does it */
  updateFormValidity();
 
});

const filterBtn = document.querySelector('.filter-btn');
const filterPanel = document.getElementById('filter-panel');
const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
const reportCards = document.querySelectorAll('.report-card');
 
/* ----- Opening/closing the panel ----- */
 
filterBtn.addEventListener('click', function () {
 
  /* Same open/close pattern as the AI chat popup: toggle the
     "hidden" attribute, and keep aria-expanded in sync so
     assistive tech knows whether the panel is currently shown */
 
  const isCurrentlyHidden = filterPanel.hidden;
 
  filterPanel.hidden = !isCurrentlyHidden;
  filterBtn.setAttribute('aria-expanded', String(isCurrentlyHidden));
 
});
 
/* Nice-to-have UX touch: clicking anywhere OUTSIDE the filter
   button/panel should close it, matching how dropdowns behave
   almost everywhere else on the web. Without this, the panel
   would stay open until the user specifically clicks the
   Filter By button again — a bit unnatural. */
 
document.addEventListener('click', function (event) {
 
  /* .contains() checks whether the clicked element is INSIDE
     a given element. If the click happened outside BOTH the
     button and the panel, and the panel is currently open,
     close it. */
  const clickedInsideButton = filterBtn.contains(event.target);
  const clickedInsidePanel = filterPanel.contains(event.target);
 
  if (!clickedInsideButton && !clickedInsidePanel && !filterPanel.hidden) {
    filterPanel.hidden = true;
    filterBtn.setAttribute('aria-expanded', 'false');
  }
 
});
 
/* ----- Actually filtering the cards ----- */
 
function applyFilter() {
 
  /* Build a list of which statuses are CURRENTLY checked.
     Array.from() converts the NodeList of checkboxes into a
     proper array so we can use .filter() and .map() on it —
     .filter() keeps only the checked ones, .map() then pulls
     out just their value ("submitted", "processing", "closed")
     from each, discarding the checkbox elements themselves. */
 
  const checkedStatuses = Array.from(filterCheckboxes)
    .filter(function (checkbox) { return checkbox.checked; })
    .map(function (checkbox) { return checkbox.value; });
 
  /* Now go through every report card and decide whether it
     should be shown or hidden, based on whether ITS status is
     in the list of currently-checked statuses */
 
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