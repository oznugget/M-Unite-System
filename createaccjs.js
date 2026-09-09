// Initialize the international telephone input plugin
const input = document.querySelector("#contact");

window.intlTelInput(input, {
  initialCountry: "za",
  onlyCountries: ["za"],
  allowDropdown: false,
  separateDialCode: true,
  autoPlaceholder: "off",
  strictMode: true,
  utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js"
});

//===============================================================================================================/
// Live Address Autocomplete bounded to Makhanda

    //===============================================================================================================/
// Ward extraction from Nominatim's address fields (same approach as the
// fault-report form) — avoids a separate, flaky server-side MapIt API call.

const POSSIBLE_WARD_FIELDS = ['neighbourhood', 'suburb', 'city_district', 'quarter'];

function extractWardNumber(addr) {
    for (const fieldName of POSSIBLE_WARD_FIELDS) {
        const text = addr[fieldName];
        if (!text) continue;
        const match = text.match(/Ward\s*0*(\d+)/i);
        if (match) return match[1];
    }
    return null;
}

    const addrInput = document.getElementById('addr');
    const suggestions = document.getElementById('suggestions');
    let debounceTimer;
 
    addrInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const q = addrInput.value.trim();

    document.getElementById('lat').value = '';
    document.getElementById('lon').value = '';
    document.getElementById('street_number').value = '';
    document.getElementById('street_name').value = '';
    document.getElementById('suburb').value = '';

    if (q.length < 6) {
        suggestions.innerHTML = '';
        return;
    }

    debounceTimer = setTimeout(async () => {
        try {
            // Split what the user typed into "number" + "rest of street name"
            // ourselves. We do NOT rely on Nominatim's house_number field —
            // Makhanda's OSM data very often has no house-level tagging, so
            // trusting the API for this silently drops the number.
            const typedMatch = q.match(/^(\d+[a-zA-Z]*[-/]?\d*)\s+(.*)$/);
            const typedNum = typedMatch ? typedMatch[1] : '';
            const typedStreetOnly = typedMatch ? typedMatch[2] : q;

            const params = new URLSearchParams({
                street: q,
                city: 'Makhanda',
                country: 'South Africa',
                format: 'json',
                addressdetails: 1,
                limit: 1
            });
            const res = await fetch(`https://nominatim.openstreetmap.org/search?${params.toString()}`);
            const data = await res.json();

            if (data.length === 0) {
                suggestions.innerHTML = '<li class="no-match">No match yet — check the street number and spelling</li>';
                return;
            }

                       const item = data[0];
            const addr = item.address || {};

            // User-typed number wins; API's house_number is only a fallback.
            // If neither source has a number, default to '0' instead of
            // blocking the user from selecting the suggestion.
            const num = typedNum || addr.house_number || '0';
            const street = addr.road || typedStreetOnly || '';
            const suburb = addr.suburb || addr.neighbourhood || addr.residential || '';

            // Ward is pulled from the same address object — no server round-trip
            // to a third-party API needed at submit time.
            const ward = extractWardNumber(addr) || '1'; // default ward 1 if undetected

            let betterDisplay = item.display_name;
            if (num !== '0' && !betterDisplay.startsWith(num)) {
                betterDisplay = `${num} ${betterDisplay}`;
            }
            const safeName = betterDisplay.replace(/'/g, "\\'");
            const safeStreet = street.replace(/'/g, "\\'");
            const safeSuburb = suburb.replace(/'/g, "\\'");

            suggestions.innerHTML = `<li onclick="applyAddress('${safeName}', '${item.lat}', '${item.lon}', '${num}', '${safeStreet}', '${safeSuburb}', '${ward}')">${betterDisplay}</li>`;
        } catch (err) {
            console.error("Autocomplete failed", err);
        }
    }, 400);
});

//===============================================================================================================/
//===============================================================================================================/
// Applies a clicked suggestion to the visible field + hidden fields
function applyAddress(display, lat, lon, num, street, suburb, ward) {
    addrInput.value = display;
    document.getElementById('lat').value = lat;
    document.getElementById('lon').value = lon;
    document.getElementById('street_number').value = num;
    document.getElementById('street_name').value = street;
    document.getElementById('suburb').value = suburb;
    document.getElementById('ward_id').value = ward || '1';
    suggestions.innerHTML = '';
    updateSubmitState();
}


//===============================================================================================================/

//for municipal officer to choose division
const roleSelect = document.getElementById('urole');
const divisionContainer = document.getElementById('division-container');
const divisionSelect = document.getElementById('division');

const addressContainer = document.getElementById('address-container');
const addressInput = document.getElementById('addr');

function handleRoleChange() {
    const selectedRole = roleSelect.value;

    // 1. Division: Show only for Municipal Officer (Value: 3)
    if (selectedRole === '3') {
        divisionContainer.style.display = 'block';
    } else {
        divisionContainer.style.display = 'none';
        divisionSelect.value = '';
    }

    // 2. Physical Address: Show only for Community Member (Value: 1)
    if (selectedRole === '1') {
        addressContainer.style.display = 'block';
        addressInput.setAttribute('required', 'required');
    } else {
        addressContainer.style.display = 'none';
        addressInput.removeAttribute('required');
        addressInput.value = '';
        
        // Reset hidden address values
        document.getElementById('lat').value = '';
        document.getElementById('lon').value = '';
        document.getElementById('street_number').value = '';
        document.getElementById('street_name').value = '';
        document.getElementById('suburb').value = '';
    }
}

// Attach listener and trigger once on load
// Attach listener and trigger once on load
roleSelect.addEventListener('change', handleRoleChange);
roleSelect.addEventListener('change', updateSubmitState);
handleRoleChange();

//===============================================================================================================/

//toggling password visibility
function togglePassword() {
  const input = document.getElementById("myInput");
  const icon = document.getElementById("eyeIcon");

  const openEye = `
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
    <circle cx="12" cy="12" r="3"></circle>
  `;

  const slashedEye = `
    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
    <line x1="1" y1="1" x2="23" y2="23"></line>
  `;

  if (input.type === "password") {
    input.type = "text";
    icon.innerHTML = openEye;
  } else {
    input.type = "password";
    icon.innerHTML = slashedEye;
  }
}

//===============================================================================================================/
//password feedback as user typed
const pwdInput = document.getElementById('myInput');
const pwdFeedback = document.getElementById('pwd-feedback');
 
const passwordRules = [
    { label: 'At least 8 characters', test: v => v.length >= 8 },
    { label: 'One uppercase letter (A-Z)', test: v => /[A-Z]/.test(v) },
    { label: 'One lowercase letter (a-z)', test: v => /[a-z]/.test(v) },
    { label: 'One number (0-9)', test: v => /[0-9]/.test(v) }
];
 
function isPasswordValid(value) {
    return passwordRules.every(rule => rule.test(value));
}
 
function renderPasswordFeedback() {
    const value = pwdInput.value;
    pwdFeedback.innerHTML = passwordRules.map(rule => {
        const passed = rule.test(value);
        return `<li class="${passed ? 'valid' : 'invalid'}">${passed ? '✓' : '✗'} ${rule.label}</li>`;
    }).join('');
}
 
pwdInput.addEventListener('input', renderPasswordFeedback);
renderPasswordFeedback(); // show the checklist (all red) before the user starts typing
 
//===============================================================================================================/
// Overall form validity — keeps the submit button disabled until every
// currently-relevant required field is actually filled/valid.

const submitBtn = document.getElementById('sub');

function updateSubmitState() {
    const firstname = document.getElementById('firstname');
    const surname = document.getElementById('surname');
    const email = document.getElementById('email');
    const contact = document.getElementById('contact');
    const role = document.getElementById('urole');

    const basicFieldsValid =
        firstname.value.trim() !== '' &&
        surname.value.trim() !== '' &&
        email.checkValidity() &&
        contact.checkValidity() &&
        role.value !== '';

    const passwordValid = isPasswordValid(pwdInput.value);

    // Address is only required for Community Members, and only counts as
    // valid once a suggestion has actually been picked (street_number set)
    let addressValid = true;
    if (role.value === '1') {
        addressValid = document.getElementById('street_number').value !== '';
    }

    submitBtn.disabled = !(basicFieldsValid && passwordValid && addressValid);
}

// Re-check on every relevant change
['firstname', 'surname', 'email', 'contact', 'urole'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateSubmitState);
    document.getElementById(id).addEventListener('change', updateSubmitState);
});
pwdInput.addEventListener('input', updateSubmitState);
addrInput.addEventListener('input', updateSubmitState);

updateSubmitState(); // run once on load

//===============================================================================================================/


regForm.addEventListener('submit', (e) => {
    if (!isPasswordValid(pwdInput.value)) {
        e.preventDefault();
        alert('Your password needs to meet all the requirements listed under the password field.');
        pwdInput.focus();
    }


     const isCommunityMember = roleSelect.value === '1';
    if (isCommunityMember && !document.getElementById('street_number').value) {
        e.preventDefault();
        alert('Please select your address from the suggestion list so we can capture your street number and ward correctly.');
        addrInput.focus();
    }
});