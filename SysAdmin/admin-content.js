// M-Unite Admin - Content Management page

let content = [
  { id: 1, title: "Water Outage Scheduled", target: "Ward 5", status: "Pending" },
  { id: 2, title: "Community Clean-Up Day", target: "General", status: "Approved" },
  { id: 3, title: "Road Closure Notice", target: "Ward 2", status: "Pending" },
  { id: 4, title: "Unverified Advert", target: "General", status: "Rejected" }
];

const listEl = document.getElementById("content-list");
const emptyMsg = document.getElementById("empty-msg");
const searchInput = document.getElementById("search-input");
const statusFilter = document.getElementById("status-filter");

function updateStats() {
  document.getElementById("stat-total").textContent = content.length;
  document.getElementById("stat-pending").textContent = content.filter(c => c.status === "Pending").length;
  document.getElementById("stat-approved").textContent = content.filter(c => c.status === "Approved").length;
}

function render() {
  const search = searchInput.value.toLowerCase();
  const filter = statusFilter.value;
  const filtered = content.filter(c => c.title.toLowerCase().includes(search) && (filter === "all" || c.status === filter));

  listEl.innerHTML = "";
  emptyMsg.style.display = filtered.length ? "none" : "block";

  filtered.forEach(c => {
    const li = document.createElement("li");
    li.innerHTML = `
      <div class="item-title">${c.title} <span class="badge badge-${c.status}">${c.status}</span></div>
      <div class="item-meta">${c.target}</div>
      ${c.status !== "Approved" ? `<button onclick="setStatus(${c.id}, 'Approved')">Approve</button>` : ""}
      ${c.status !== "Rejected" ? `<button class="secondary" onclick="setStatus(${c.id}, 'Rejected')">Reject</button>` : ""}
      <button class="danger" onclick="deleteContent(${c.id})">Delete</button>
    `;
    listEl.appendChild(li);
  });

  updateStats();
}

function setStatus(id, value) { content.find(c => c.id === id).status = value; render(); }

function deleteContent(id) {
  if (confirm("Delete this item permanently?")) {
    content = content.filter(c => c.id !== id);
    render();
  }
}

searchInput.addEventListener("input", render);
statusFilter.addEventListener("change", render);

render();
