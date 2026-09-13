// M-Unite Admin - User Management page

let users = [
  { id: 1, name: "Amahle Mtshali", role: "Community Member", status: "Approved" },
  { id: 2, name: "Sipho Dube", role: "Community Member", status: "Pending" },
  { id: 3, name: "Thandeka Nyathi", role: "Municipal Officer", status: "Approved" },
  { id: 4, name: "Lindiwe Khumalo", role: "Ward Councillor", status: "Pending" }
];
let nextId = 5;

const listEl = document.getElementById("user-list");
const emptyMsg = document.getElementById("empty-msg");
const searchInput = document.getElementById("search-input");
const statusFilter = document.getElementById("status-filter");

function updateStats() {
  document.getElementById("stat-total").textContent = users.length;
  document.getElementById("stat-pending").textContent = users.filter(u => u.status === "Pending").length;
  document.getElementById("stat-approved").textContent = users.filter(u => u.status === "Approved").length;
}

function render() {
  const search = searchInput.value.toLowerCase();
  const filter = statusFilter.value;
  const filtered = users.filter(u => u.name.toLowerCase().includes(search) && (filter === "all" || u.status === filter));

  listEl.innerHTML = "";
  emptyMsg.style.display = filtered.length ? "none" : "block";

  filtered.forEach(u => {
    const li = document.createElement("li");
    li.innerHTML = `
      <div class="item-title">${u.name} <span class="badge badge-${u.status}">${u.status}</span></div>
      <div class="item-meta">
        <select onchange="setRole(${u.id}, this.value)">
          <option ${u.role === "Community Member" ? "selected" : ""}>Community Member</option>
          <option ${u.role === "Ward Councillor" ? "selected" : ""}>Ward Councillor</option>
          <option ${u.role === "Municipal Officer" ? "selected" : ""}>Municipal Officer</option>
        </select>
      </div>
      ${u.status === "Pending" ? `<button onclick="approveUser(${u.id})">Approve</button>` : ""}
      <button class="danger" onclick="deleteUser(${u.id})">Delete</button>
    `;
    listEl.appendChild(li);
  });

  updateStats();
}

function approveUser(id) { users.find(u => u.id === id).status = "Approved"; render(); }
function setRole(id, value) { users.find(u => u.id === id).role = value; render(); }

function deleteUser(id) {
  if (confirm("Remove this user's account?")) {
    users = users.filter(u => u.id !== id);
    render();
  }
}

document.getElementById("add-form").addEventListener("submit", (e) => {
  e.preventDefault();
  const nameInput = document.getElementById("new-name");
  if (nameInput.value.trim() === "") { alert("Please enter a name."); return; }
  users.push({ id: nextId++, name: nameInput.value, role: document.getElementById("new-role").value, status: "Pending" });
  nameInput.value = "";
  render();
});

searchInput.addEventListener("input", render);
statusFilter.addEventListener("change", render);

render();
