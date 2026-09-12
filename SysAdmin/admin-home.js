// M-Unite Admin - Dashboard (Home) page

const usersByRole = [
  { role: "Community Member", count: 98, color: "#F36E39" },
  { role: "Municipal Officer", count: 15, color: "#4a90c4" },
  { role: "Ward Councillor", count: 12, color: "#0E2841" },
  { role: "System Administrator", count: 3, color: "#b8c4d1" }
];

const activity = [
  { text: "amahle.mtshali logged in", time: "09:14 today" },
  { text: "New user Sipho Dube registered", time: "08:40 today" },
  { text: "Notice 'Road Closure' was approved", time: "Yesterday" },
  { text: "admin.zola deleted an inactive account", time: "Yesterday" },
  { text: "thandeka.nyathi logged in", time: "2 days ago" }
];

const pendingApprovals = [
  { name: "Sipho Dube", role: "Community Member", date: "2026-08-27" },
  { name: "Lindiwe Khumalo", role: "Ward Councillor", date: "2026-08-26" }
];

// Reports broken down two ways, so the dropdown has something to switch between
const reportsByWard = { "Ward 5": 14, "Ward 2": 9, "Ward 3": 7, "Ward 7": 4 };
const reportsByCategory = { "Water": 11, "Roads": 9, "Electricity": 8, "Sanitation": 6 };

function renderStats() {
  const totalUsers = usersByRole.reduce((sum, r) => sum + r.count, 0);
  document.getElementById("stat-users").textContent = totalUsers;
  document.getElementById("stat-pending").textContent = pendingApprovals.length;
  document.getElementById("stat-reports").textContent = "342";
  document.getElementById("stat-notices").textContent = "6";
  document.getElementById("stat-wards").textContent = "12";
  document.getElementById("stat-alerts").textContent = "3";
}

function renderDonut() {
  const total = usersByRole.reduce((sum, r) => sum + r.count, 0);
  let gradient = [];
  let runningPct = 0;

  usersByRole.forEach((r) => {
    const pct = (r.count / total) * 100;
    gradient.push(`${r.color} ${runningPct}% ${runningPct + pct}%`);
    runningPct += pct;
  });

  document.getElementById("role-donut").style.background = `conic-gradient(${gradient.join(", ")})`;
  document.getElementById("role-donut-total").innerHTML = total + "<br>total";

  const key = document.getElementById("role-key");
  key.innerHTML = "";
  usersByRole.forEach((r) => {
    const row = document.createElement("div");
    row.innerHTML = `<span class="dot" style="background:${r.color};"></span>${r.role} (${r.count})`;
    key.appendChild(row);
  });
}

function renderActivity() {
  const feed = document.getElementById("activity-feed");
  feed.innerHTML = "";
  activity.forEach((a) => {
    const li = document.createElement("li");
    li.innerHTML = `${a.text}<div class="item-meta">${a.time}</div>`;
    feed.appendChild(li);
  });
}

function renderPending() {
  const table = document.getElementById("pending-table");
  table.innerHTML = "<tr><th>Name</th><th>Role Requested</th><th>Date</th><th></th></tr>";
  document.getElementById("pending-empty").style.display = pendingApprovals.length ? "none" : "block";

  pendingApprovals.forEach((p, i) => {
    const row = document.createElement("tr");
    row.innerHTML = `<td>${p.name}</td><td>${p.role}</td><td>${p.date}</td>
      <td><button onclick="approve(${i})">Approve</button></td>`;
    table.appendChild(row);
  });
}

function approve(index) {
  pendingApprovals.splice(index, 1);
  renderStats();
  renderPending();
}

function renderBars(counts) {
  const container = document.getElementById("report-bars");
  const max = Math.max(...Object.values(counts));
  container.innerHTML = "";
  Object.keys(counts).forEach((key) => {
    const pct = Math.round((counts[key] / max) * 100);
    const row = document.createElement("div");
    row.className = "bar-row";
    row.innerHTML = `
      <div class="bar-label">${key}</div>
      <div class="bar-track"><div class="bar-fill" style="width:${pct}%"></div></div>
      <div class="bar-value">${counts[key]}</div>
    `;
    container.appendChild(row);
  });
}

document.getElementById("breakdown-select").addEventListener("change", (e) => {
  renderBars(e.target.value === "ward" ? reportsByWard : reportsByCategory);
});

renderStats();
renderDonut();
renderActivity();
renderPending();
renderBars(reportsByWard);
