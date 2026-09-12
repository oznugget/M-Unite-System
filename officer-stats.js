// M-Unite Officer - Statistics page

const reports = [
  { ward: "Ward 5", status: "In Progress" },
  { ward: "Ward 2", status: "Pending" },
  { ward: "Ward 5", status: "Pending" },
  { ward: "Ward 5", status: "Resolved" },
  { ward: "Ward 3", status: "Resolved" },
  { ward: "Ward 2", status: "Closed" },
  { ward: "Ward 5", status: "Resolved" },
  { ward: "Ward 3", status: "In Progress" }
];

function countBy(key) {
  const counts = {};
  reports.forEach(r => { counts[r[key]] = (counts[r[key]] || 0) + 1; });
  return counts;
}

function renderBars(containerId, counts) {
  const container = document.getElementById(containerId);
  const max = Math.max(...Object.values(counts));
  container.innerHTML = "";
  Object.keys(counts).forEach(key => {
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

const statusCounts = countBy("status");
const wardCounts = countBy("ward");
const total = reports.length;
const resolved = (statusCounts["Resolved"] || 0) + (statusCounts["Closed"] || 0);
const open = total - resolved;

document.getElementById("stat-total").textContent = total;
document.getElementById("stat-resolved-rate").textContent = Math.round((resolved / total) * 100) + "%";
document.getElementById("stat-open").textContent = open;

renderBars("status-bars", statusCounts);
renderBars("ward-bars", wardCounts);
