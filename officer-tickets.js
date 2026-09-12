// M-Unite Officer - Tickets page

let tickets = [
  { id: 1, ward: "Ward 5", category: "Water", description: "Burst pipe on New Street.", status: "In Progress", comments: ["Contractor dispatched."] },
  { id: 2, ward: "Ward 2", category: "Roads", description: "Pothole outside the Cathedral.", status: "Pending", comments: ["Councillor notified contractor."] },
  { id: 3, ward: "Ward 5", category: "Electricity", description: "Streetlight out on High Street.", status: "Pending", comments: [] },
  { id: 4, ward: "Ward 3", category: "Sanitation", description: "Overflowing bin at the taxi rank.", status: "Resolved", comments: ["Collected and closed out."] }
];

const listEl = document.getElementById("ticket-list");
const emptyMsg = document.getElementById("empty-msg");
const searchInput = document.getElementById("search-input");
const statusFilter = document.getElementById("status-filter");

function statusClass(s) { return "badge-" + s.replace(/\s+/g, ""); }

function updateStats() {
  document.getElementById("stat-open").textContent = tickets.filter(t => t.status === "Pending").length;
  document.getElementById("stat-progress").textContent = tickets.filter(t => t.status === "In Progress").length;
  document.getElementById("stat-resolved").textContent = tickets.filter(t => t.status === "Resolved").length;
}

function render() {
  const search = searchInput.value.toLowerCase();
  const filter = statusFilter.value;

  const filtered = tickets.filter(t => {
    const matchesSearch = t.category.toLowerCase().includes(search) || t.ward.toLowerCase().includes(search);
    const matchesFilter = filter === "all" || t.status === filter;
    return matchesSearch && matchesFilter;
  });

  listEl.innerHTML = "";
  emptyMsg.style.display = filtered.length ? "none" : "block";

  filtered.forEach(t => {
    const commentsHtml = t.comments.map(c => `<p>&#128172; ${c}</p>`).join("");
    const li = document.createElement("li");
    li.innerHTML = `
      <div class="item-title">${t.category} <span class="badge ${statusClass(t.status)}">${t.status}</span></div>
      <div class="item-meta">${t.ward}</div>
      <p>${t.description}</p>
      ${commentsHtml}
      <label>Update status:
        <select onchange="setStatus(${t.id}, this.value)">
          <option ${t.status === "Pending" ? "selected" : ""}>Pending</option>
          <option ${t.status === "In Progress" ? "selected" : ""}>In Progress</option>
          <option ${t.status === "Resolved" ? "selected" : ""}>Resolved</option>
        </select>
      </label>
      <input type="text" id="comment-${t.id}" placeholder="Add a comment">
      <button onclick="addComment(${t.id})">Add Comment</button>
    `;
    listEl.appendChild(li);
  });

  updateStats();
}

function setStatus(id, value) {
  tickets.find(t => t.id === id).status = value;
  render();
}

function addComment(id) {
  const input = document.getElementById("comment-" + id);
  if (input.value.trim() === "") { alert("Please write a comment first."); return; }
  tickets.find(t => t.id === id).comments.push(input.value);
  render();
}

searchInput.addEventListener("input", render);
statusFilter.addEventListener("change", render);

render();
