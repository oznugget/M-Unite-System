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

// Live Address Autocomplete bounded to Makhanda
    const addrInput = document.getElementById('addr');
    const suggestions = document.getElementById('suggestions');
    let debounceTimer;

    addrInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = addrInput.value.trim();

        if (q.length < 3) {
            suggestions.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q + ', Makhanda, South Africa')}&format=json&addressdetails=1&limit=5`);
                const data = await res.json();

                suggestions.innerHTML = data.map(item => {
                    const addr = item.address || {};
                    const num = addr.house_number || '';
                    const street = addr.road || '';
                    const suburb = addr.suburb || addr.neighbourhood || addr.residential || '';
                    const safeName = item.display_name.replace(/'/g, "\\'");

                    return `<li onclick="applyAddress('${safeName}', '${item.lat}', '${item.lon}', '${num}', '${street.replace(/'/g, "\\'")}', '${suburb.replace(/'/g, "\\'")}')">${item.display_name}</li>`;
                }).join('');
            } catch (err) {
                console.error("Autocomplete failed", err);
            }
        }, 300);
    });

    function applyAddress(display, lat, lon, num, street, suburb) {
        addrInput.value = display;
        document.getElementById('lat').value = lat;
        document.getElementById('lon').value = lon;
        document.getElementById('street_number').value = num;
        document.getElementById('street_name').value = street;
        document.getElementById('suburb').value = suburb;
        suggestions.innerHTML = '';
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete-wrapper')) suggestions.innerHTML = '';
    });

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
roleSelect.addEventListener('change', handleRoleChange);
handleRoleChange();


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