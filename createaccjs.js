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