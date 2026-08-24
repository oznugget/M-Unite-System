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


