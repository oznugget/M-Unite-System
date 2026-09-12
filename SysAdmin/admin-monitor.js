// M-Unite Admin - System Monitoring page

const logs = [
  { user: "amahle.mtshali", action: "Login", time: "2026-08-20 09:14" },
  { user: "thandeka.nyathi", action: "Login", time: "2026-08-20 08:02" },
  { user: "sipho.dube", action: "Logout", time: "2026-08-19 17:45" },
  { user: "admin.zola", action: "Delete", time: "2026-08-19 15:30" },
  { user: "lindiwe.khumalo", action: "Login", time: "2026-08-19 08:50" }
];

let sortKey = "time";
let sortAsc = false;

const bodyEl = document.getElementById("log-body");
const emptyMsg = document.getElementById("empty-msg");
const searchInput = document.getElementById("search-input");
const actionFilter = document.getElementById("action-filter");

function updateStats() {
  document.getElementById("stat-total").textContent = logs.length;
  document.getElementById("stat-logins").textContent = logs.filter(l => l.action === "Login").length;
  document.getElementById("stat-deletes").textContent = logs.filter(l => l.action === "Delete").length;
}

function sortBy(key) {
  if (sortKey === key) { sortAsc = !sortAsc; } else { sortKey = key; sortAsc = true; }
  render();
}

function render() {
  const search = searchInput.value.toLowerCase();
  const filter = actionFilter.value;

  let filtered = logs.filter(l => l.user.toLowerCase().includes(search) && (filter === "all" || l.action === filter));
  filtered.sort((a, b) => {
    const result = a[sortKey] < b[sortKey] ? -1 : a[sortKey] > b[sortKey] ? 1 : 0;
    return sortAsc ? result : -result;
  });

  bodyEl.innerHTML = "";
  emptyMsg.style.display = filtered.length ? "none" : "block";

  filtered.forEach(l => {
    const row = document.createElement("tr");
    row.innerHTML = `<td>${l.user}</td><td><span class="badge badge-${l.action === "Delete" ? "Rejected" : "Approved"}">${l.action}</span></td><td>${l.time}</td>`;
    bodyEl.appendChild(row);
  });

  updateStats();
}

searchInput.addEventListener("input", render);
actionFilter.addEventListener("change", render);

render();
