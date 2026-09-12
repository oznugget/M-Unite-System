// M-Unite Admin - Account page

const validators = {
  name: v => v.trim().length > 0,
  phone: v => /^\d{10}$/.test(v)
};

function validateField(id) {
  const input = document.getElementById(id);
  const valid = validators[id](input.value);
  input.closest("label").classList.toggle("invalid", !valid);
  return valid;
}

document.getElementById("settings-form").addEventListener("submit", (e) => {
  e.preventDefault();
  const ids = ["name", "phone"];
  const allValid = ids.map(validateField).every(Boolean);
  if (!allValid) return;

  document.getElementById("p-name").textContent = document.getElementById("name").value;
  document.getElementById("p-phone").textContent = document.getElementById("phone").value;

  const msg = document.getElementById("save-msg");
  msg.style.display = "inline";
  setTimeout(() => (msg.style.display = "none"), 2000);
});

document.getElementById("logout-btn").addEventListener("click", () => {
  document.getElementById("main-content").style.display = "none";
  document.getElementById("signed-out").style.display = "block";
});

document.getElementById("login-btn").addEventListener("click", () => {
  document.getElementById("main-content").style.display = "block";
  document.getElementById("signed-out").style.display = "none";
});

document.getElementById("name").value = document.getElementById("p-name").textContent;
document.getElementById("phone").value = document.getElementById("p-phone").textContent;
