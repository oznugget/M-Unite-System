
var reports = [
  { category: "Water", status: "In Progress", description: "Burst pipe on New Street." },
  { category: "Roads", status: "Resolved", description: "Pothole outside the Cathedral." },
  { category: "Electricity", status: "Pending", description: "Streetlight on High Street is out." }
];

function showReports() {
  var list = document.getElementById("report-list");
  list.innerHTML = "";
  for (var i = 0; i < reports.length; i++) {
    var li = document.createElement("li");
    li.innerHTML = "<strong>" + reports[i].category + "</strong> - " +
      "<span class='status'>" + reports[i].status + "</span><br>" +
      reports[i].description;
    list.appendChild(li);
  }
}

document.getElementById("settings-form").addEventListener("submit", function (e) {
  e.preventDefault();
  var name = document.getElementById("name").value;
  var email = document.getElementById("email").value;
  var phone = document.getElementById("phone").value;

  if (name === "" || email === "" || phone === "") {
    alert("Please fill in all fields.");
    return;
  }
  if (phone.length !== 10) {
    alert("Phone number must be 10 digits.");
    return;
  }

  document.getElementById("p-name").textContent = name;
  document.getElementById("p-email").textContent = email;
  document.getElementById("p-phone").textContent = phone;
  alert("Details saved!");
});

document.getElementById("logout-btn").addEventListener("click", function () {
  document.getElementById("main-content").style.display = "none";
  document.getElementById("signed-out").style.display = "block";
});

document.getElementById("login-btn").addEventListener("click", function () {
  document.getElementById("main-content").style.display = "block";
  document.getElementById("signed-out").style.display = "none";
});

showReports();
